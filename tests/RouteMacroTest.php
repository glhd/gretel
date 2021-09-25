<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

class RouteMacroTest extends TestCase
{
	public function test_macro_registers_new_breadcrumb(): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		$this->get('/users');
		
		$this->assertActiveBreadcrumbs(['Users', '/users']);
	}
	
	public function test_full_parent_name_is_registered_directly(): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		Route::get('/users/create', $this->action())
			->name('users.create')
			->breadcrumb('Add a User', 'users.index');
		
		$this->get('/users/create');
		
		$this->assertActiveBreadcrumbs(['Users', '/users'], ['Add a User', '/users/create']);
	}
	
	public function test_parent_shorthand_syntax(): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		Route::get('/users/create', $this->action())
			->name('users.create')
			->breadcrumb('Add a User', '.index');
		
		$this->get('/users/create');
		
		$this->assertActiveBreadcrumbs(['Users', '/users'], ['Add a User', '/users/create']);
	}
	
	public function test_dynamic_title_via_closure(): void
	{
		$user = User::factory()->create();
		
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		$this->get(route('users.show', $user));
		
		$this->assertActiveBreadcrumbs([$user->name, route('users.show', $user)]);
	}
	
	public function test_nested_routes(): void
	{
		$user = User::factory()->create();
		$note = Note::factory()->create(['user_id' => $user->id]);
		
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.notes.show')
			->breadcrumb(fn(User $user, Note $note) => $note->note, 'users.show');
		
		$this->get(route('users.notes.show', [$user, $note]));
		
		$this->assertActiveBreadcrumbs(
			[$user->name, route('users.show', $user)],
			[$note->note, route('users.notes.show', [$user, $note])]
		);
	}
	
	public function test_shallow_nested_routes(): void
	{
		$user = User::factory()->create();
		$note = Note::factory()->create(['user_id' => $user->id]);
		
		Route::get('/users', fn(User $user) => 'OK')
			->name('users.index')
			->breadcrumb('Users');
		
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name, '.index');
		
		Route::get('/notes/{note}', fn(Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('notes.show')
			->breadcrumb(fn(Note $note) => $note->note, fn(Note $note) => route('users.show', $note->author));
		
		$this->get(route('notes.show', $note));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$user->name, route('users.show', $user)],
			[$note->note, route('notes.show', $note)]
		);
	}
	
	public function test_custom_parent(): void
	{
		$note = Note::factory()->create();
		
		Route::get('/notes/{note}', fn(Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('notes.show')
			->breadcrumb(
				fn(Note $note) => $note->note,
				function(Breadcrumb $breadcrumb, Note $note) {
					return $breadcrumb("Parent of {$note->id}", url("/parent-{$note->id}"));
				}
			);
		
		$this->get(route('notes.show', $note));
		$this->assertActiveBreadcrumbs(
			["Parent of {$note->id}", "/parent-{$note->id}"],
			[$note->note, route('notes.show', $note)]
		);
	}
	
	/**
	 * @param array{string, string} ...$expectations
	 * @return $this
	 */
	protected function assertActiveBreadcrumbs(array ...$expectations): self
	{
		$breadcrumbs = $this->app->make(\Illuminate\Routing\Route::class)
			->breadcrumbs()
			->toArray();
		
		foreach ($expectations as $index => [$title, $url]) {
			$breadcrumb = $breadcrumbs[$index];
			$this->assertEquals($title, $breadcrumb['title']);
			$this->assertEquals(url($url), $breadcrumb['url']);
		}
		
		return $this;
	}
	
	protected function action()
	{
		return fn() => 'OK';
	}
}
