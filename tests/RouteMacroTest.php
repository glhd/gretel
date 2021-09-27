<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\Cache;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

class RouteMacroTest extends TestCase
{
	/** @dataProvider cachingProvider */
	public function test_macro_registers_new_breadcrumb(bool $cache): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		$this->setUpCache($cache);
		
		$this->get('/users');
		
		$this->assertActiveBreadcrumbs(['Users', '/users']);
	}
	
	/** @dataProvider cachingProvider */
	public function test_full_parent_name_is_registered_directly(bool $cache): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		Route::get('/users/create', $this->action())
			->name('users.create')
			->breadcrumb('Add a User', 'users.index');
		
		$this->setUpCache($cache);
		
		$this->get('/users/create');
		
		$this->assertActiveBreadcrumbs(['Users', '/users'], ['Add a User', '/users/create']);
	}
	
	/** @dataProvider cachingProvider */
	public function test_parent_shorthand_syntax(bool $cache): void
	{
		Route::get('/users', $this->action())
			->name('users.index')
			->breadcrumb('Users');
		
		Route::get('/users/create', $this->action())
			->name('users.create')
			->breadcrumb('Add a User', '.index');
		
		$this->setUpCache($cache);
		
		$this->get('/users/create');
		
		$this->assertActiveBreadcrumbs(['Users', '/users'], ['Add a User', '/users/create']);
	}
	
	/** @dataProvider cachingProvider */
	public function test_dynamic_title_via_closure(bool $cache): void
	{
		$user = User::factory()->create();
		
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		$this->setUpCache($cache);
		
		$this->get(route('users.show', $user));
		
		$this->assertActiveBreadcrumbs([$user->name, route('users.show', $user)]);
	}
	
	/** @dataProvider cachingProvider */
	public function test_nested_routes(bool $cache): void
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
		
		$this->setUpCache($cache);
		
		$this->get(route('users.notes.show', [$user, $note]));
		
		$this->assertActiveBreadcrumbs(
			[$user->name, route('users.show', $user)],
			[$note->note, route('users.notes.show', [$user, $note])]
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_shallow_nested_routes(bool $cache): void
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
			->breadcrumb(
				fn(Note $note) => $note->note,
				fn(Note $note) => route('users.show', $note->author)
			);
		
		$this->setUpCache($cache);
		
		$this->get(route('notes.show', $note));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$user->name, route('users.show', $user)],
			[$note->note, route('notes.show', $note)]
		);
		
		// We also want to test that the forced binding of 'users.show' to a different
		// set of route parameters doesn't break subsequent calls to that route in a
		// different context.
		
		$user2 = User::factory()->create();
		$this->get(route('users.show', $user2));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$user2->name, route('users.show', $user2)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_custom_parent(bool $cache): void
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
		
		$this->setUpCache($cache);
		
		$this->get(route('notes.show', $note));
		$this->assertActiveBreadcrumbs(
			["Parent of {$note->id}", "/parent-{$note->id}"],
			[$note->note, route('notes.show', $note)]
		);
	}
	
	public function cachingProvider(): array
	{
		return [
			'Uncached' => [false],
			'Cached' => [true],
		];
	}
	
	protected function setUpCache(bool $cache = true): self
	{
		if ($cache) {
			$this->artisan('breadcrumbs:cache');
			$this->app->make(Registry::class)->clear();
			$this->app->make(Cache::class)->load();
		} else {
			$this->artisan('breadcrumbs:clear');
		}
		
		return $this;
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
			$this->assertEquals($title, $breadcrumb->title);
			$this->assertEquals(url($url), $breadcrumb->url);
		}
		
		return $this;
	}
	
	protected function action()
	{
		return fn() => 'OK';
	}
}
