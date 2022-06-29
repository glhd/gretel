<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Support\Facades\Gretel;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

class ManualBreadcrumbRegistrationTest extends TestCase
{
	public function test_default_behavior(): void
	{
		Route::middleware(SubstituteBindings::class)->group(function() {
			$cb = fn() => 'OK';
			
			Route::get('/', $cb)->name('home');
			Route::get('/users', $cb)->name('users.index');
			Route::get('/users/create', $cb)->name('users.create');
			Route::get('/users/{user}', fn(User $user) => $cb())->name('users.show');
			Route::get('/users/{user}/notes', fn(User $user) => $cb())->name('notes.index');
			Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => $cb())->name('notes.show');
		});
		
		Gretel::breadcrumb('home', 'Home');
		Gretel::breadcrumb('users.index', 'Users', 'home');
		Gretel::breadcrumb('users.create', 'Create User', '.index');
		Gretel::breadcrumb('users.show', fn(User $user) => $user->name, '.index');
		Gretel::breadcrumb('notes.index', 'Notes', 'users.show');
		Gretel::breadcrumb('notes.show', fn(User $user, Note $note) => $note->note, '.index');
		
		$user = User::factory()->create(['name' => 'Chris Morrell']);
		$note = Note::factory()->create(['user_id' => $user->id, 'note' => 'Demo Note']);
		
		$this->get(route('home'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
		);
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			['Create User', '/users/create'],
		);
		
		$this->get(route('users.show', $user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			['Chris Morrell', '/users/'.$user->id],
		);
		
		$this->get(route('notes.index', $user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			['Chris Morrell', '/users/'.$user->id],
			['Notes', '/users/'.$user->id.'/notes'],
		);
		
		$this->get(route('notes.show', [$user, $note]));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			['Chris Morrell', '/users/'.$user->id],
			['Notes', '/users/'.$user->id.'/notes'],
			['Demo Note', '/users/'.$user->id.'/notes/'.$note->id],
		);
	}
}
