<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\Cache;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

class ConsoleCommandTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		
		Route::middleware(SubstituteBindings::class)->group(function() {
			$cb = fn() => 'OK';
			
			Route::get('/', $cb)->name('home')->breadcrumb('Home');
			Route::get('/users', $cb)->name('users.index')->breadcrumb('Users', 'home');
			Route::get('/users/create', $cb)->name('users.create')->breadcrumb('Create User', '.index');
			Route::get('/users/{user}', fn(User $user) => $cb())->name('users.show')->breadcrumb(fn(User $user) => $user->name, '.index');
			Route::get('/users/{user}/notes', fn(User $user) => $cb())->name('notes.index')->breadcrumb('Notes', 'users.show');
			Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => $cb())->name('notes.show')->breadcrumb(fn(User $user, Note $note) => $note->note, '.index');
		});
	}
	
	public function test_cache_command(): void
	{
		$this->artisan('breadcrumbs:cache')
			->expectsOutput('Breadcrumbs cached successfully!')
			->assertExitCode(0);
		
		// Clear existing registry and re-load from cache
		$this->app->forgetInstance(Registry::class);
		$this->app->make(Cache::class)->load();
		
		$registry = $this->app->make(Registry::class);
		
		dd($registry->get('users.index')); // FIXME
	}
	
	public function test_cache_command_triggers_error_if_routes_are_cached(): void
	{
		$this->artisan('route:cache');
		
		$this->artisan('breadcrumbs:cache')
			->assertExitCode(1);
		
		$this->artisan('route:clear');
	}
}
