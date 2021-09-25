<?php

namespace Glhd\Gretel\Tests;

use Closure;
use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Registry;
use Glhd\Gretel\RouteBreadcrumb as Crumb;
use Glhd\Gretel\Tests\Mocks\Note;
use Glhd\Gretel\Tests\Mocks\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Traits\ReflectsClosures;

class RouteMacroTest extends TestCase
{
	use ReflectsClosures;
	
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
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		$this->get('/users/3');
		
		$this->assertActiveBreadcrumbs(['User:3:id', '/users/3']);
	}
	
	public function test_custom_parent(): void
	{
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/admins/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('admins.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/notes/{note}', fn(Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('notes.show')
			->breadcrumb(
				fn(Note $note) => $note->name,
				fn(Breadcrumb $breadcrumb, Note $note) => $breadcrumb("Parent of {$note->id}", url("/parent-{$note->id}"))
			);
		
		$this->get('/notes/1');
		$this->assertActiveBreadcrumbs(['Parent of 1', '/parent-1'], ['Note:1:id', '/notes/1']);
		
		$this->get('/notes/2');
		$this->assertActiveBreadcrumbs(['Parent of 2', '/parent-2'], ['Note:2:id', '/notes/2']);
	}
	
	public function test_dynamic_route_parent_via_closure(): void
	{
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/admins/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('admins.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/notes/{note}', fn(Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('notes.show')
			->breadcrumb(
				fn(Note $note) => $note->name,
				function(Breadcrumb $breadcrumb, Note $note) {
					// Pretend that notes can have different parents
					return 0 === $note->id % 2
						? $breadcrumb->route('admins.show', $note->id)
						: $breadcrumb->route('users.show', $note->id);
				}
			);
		
		$this->get('/notes/1');
		$this->assertActiveBreadcrumbs(['User:1:id', '/users/1'], ['Note:1:id', '/notes/1']);
		
		$this->get('/notes/2');
		$this->assertActiveBreadcrumbs(['Admin:2:id', '/admins/2'], ['Note:2:id', '/notes/2']);
	}
	
	public function test_nested_route_parameters(): void
	{
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.notes.show')
			->breadcrumb(fn(User $user, Note $note) => $note->name, 'users.show');
		
		$this->get('/users/3/notes/6');
		
		$this->assertActiveBreadcrumbs(['User:3:id', '/users/3'], ['Note:6:id', '/users/3/notes/6']);
	}
	
	public function test_dynamic_nested_route_parameters(): void
	{
		Route::get('/users/{user}', fn(User $user) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.show')
			->breadcrumb(fn(User $user) => $user->name);
		
		Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => 'OK')
			->middleware(SubstituteBindings::class)
			->name('users.notes.show')
			->breadcrumb(fn(User $user, Note $note) => $note->name, fn() => 'users.show');
		
		$this->get('/users/3/notes/6');
		$this->assertActiveBreadcrumbs(['User:3:id', '/users/3'], ['Note:6:id', '/users/3/notes/6']);
	}
	
	// Cases:
	// title, closure (both cases)
	// closure, null (both cases)
	// closure, string (both cases)
	// closure, closure (both cases)
	
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
	
	protected function assertBreadcrumbIsRegistered($name, Closure $assertions = null): self
	{
		$breadcrumb = $this->registry()->get($name);
		
		$this->assertInstanceOf(Crumb::class, $breadcrumb);
		
		if ($assertions) {
			$this->assertTrue($assertions($breadcrumb, $breadcrumb->getParent()));
		}
		
		return $this;
	}
	
	protected function registry(): Registry
	{
		return $this->app->make(Registry::class);
	}
	
	protected function action()
	{
		return fn() => 'OK';
	}
}
