<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Routing\Breadcrumbs;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\View\ViewException;

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
		
		$this->user = User::factory()->create();
		$this->note = Note::factory()->create(['user_id' => $this->user->id]);
	}
	
	public function test_default_behavior(): void
	{
		$this->blade = '<x-breadcrumbs />';
		
		$this->get(route('notes.show', [$this->user, $this->note]))
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
		
		echo $result->getOriginalContent();
	}
	
	public function test_throw_when_no_breadcrumbs_match(): void
	{
		$this->blade = '<x-breadcrumbs throw-if-missing />';
		
		$this->get(route('notes.show', [$this->user, $this->note]))
			->assertOk();
	}
	
	public function test_throw_when_breadcrumbs_match(): void
	{
		Route::get('/foo', fn() => $this->renderBlade('<x-breadcrumbs throw-if-missing />'));
		
		try {
			$this->withoutExceptionHandling()->get('/foo');
			$this->fail('No exception thrown.');
		} catch (ViewException $exception) {
			$previous = $exception->getPrevious();
			$this->assertInstanceOf(MissingBreadcrumbException::class, $previous);
		} catch (MissingBreadcrumbException $exception) {
			$this->assertInstanceOf(MissingBreadcrumbException::class, $exception);
		}
	}
	
	protected function renderBlade($contents, array $data = [])
	{
		return (new InlineBlade($contents, $data))->render();
	}
}
