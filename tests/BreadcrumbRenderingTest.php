<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

class BreadcrumbRenderingTest extends TestCase
{
	protected User $user;
	
	protected Note $note;
	
	protected string $blade = '';
	
	protected function setUp(): void
	{
		parent::setUp();
		
		Route::middleware(SubstituteBindings::class)->group(function() {
			$cb = fn() => $this->renderBlade($this->blade);
			
			Route::get('/', $cb)->name('home')->breadcrumb('Home');
			Route::get('/users', $cb)->name('users.index')->breadcrumb('Users', 'home');
			Route::get('/users/create', $cb)->name('users.create')->breadcrumb('Create User', '.index');
			Route::get('/users/{user}', fn(User $user) => $cb())->name('users.show')->breadcrumb(fn(User $user) => $user->name, '.index');
			Route::get('/users/{user}/notes', fn(User $user) => $cb())->name('notes.index')->breadcrumb('Notes', 'users.show');
			Route::get('/users/{user}/notes/{note}', fn(User $user, Note $note) => $cb())->name('notes.show')->breadcrumb(fn(User $user, Note $note) => $note->note, '.index');
		});
		
		$this->user = User::factory()->create(['name' => 'Chris Morrell']);
		$this->note = Note::factory()->create(['user_id' => $this->user->id, 'note' => 'Demo Note']);
	}
	
	public function test_default_behavior(): void
	{
		$this->blade = '<x-breadcrumbs framework="tailwind" />';
		
		$result = $this->get(route('notes.show', [$this->user, $this->note]))
			->assertSeeInOrder([
				route('home'),
				route('users.index'),
				route('users.show', $this->user),
				route('notes.index', $this->user),
				route('notes.show', [$this->user, $this->note]),
			])
			->assertSeeTextInOrder([
				'Home',
				'Users',
				$this->user->name,
				'Notes',
				$this->note->note,
			]);
	}
	
	public function test_json_ld(): void
	{
		$this->blade = '<x-breadcrumbs json-ld />';
		
		$result = $this->get(route('notes.show', [$this->user, $this->note]))
			->assertOk();
	}
}
