<?php

namespace Glhd\Gretel\Tests;

use Closure;
use Glhd\Gretel\Routing\ResourceBreadcrumbs;
use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Support\Facades\Route;

class ResourceRoutesTest extends TestCase
{
	use TestsCachedBreadcrumbs;
	
	protected User $user;
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->user = User::factory()->create(['name' => 'Chris Morrell']);
	}
	
	/** @dataProvider cachingProvider */
	public function test_array_syntax(bool $cache): void
	{
		$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
			$resource->breadcrumbs([
				'index' => 'Users',
				'create' => 'New User',
				'show' => fn(User $user) => $user->name,
				'edit' => 'Edit',
			]);
		});
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			['New User', '/users/create'],
		);
		
		$this->get(route('users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
		);
		
		$this->get(route('users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
			['Edit', route('users.edit', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_callback_syntax(bool $cache): void
	{
		$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
			$resource->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
				->index('Users')
				->create('New User')
				->show(fn(User $user) => $user->name)
				->edit('Edit'));
		});
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			['New User', '/users/create'],
		);
		
		$this->get(route('users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
		);
		
		$this->get(route('users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
			['Edit', route('users.edit', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_group_prefix(bool $cache): void
	{
		Route::name('foo.')->group(function() use ($cache) {
			$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
				$resource->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
					->index('Users')
					->create('New User')
					->show(fn(User $user) => $user->name)
					->edit('Edit'));
			});
		});
		
		$this->get(route('foo.users.index'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
		);
		
		$this->get(route('foo.users.create'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			['New User', '/users/create'],
		);
		
		$this->get(route('foo.users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('foo.users.show', $this->user)],
		);
		
		$this->get(route('foo.users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('foo.users.show', $this->user)],
			['Edit', route('foo.users.edit', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_custom_names(bool $cache): void
	{
		$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
			$resource
				->names([
					'index' => 'users.custom.index-arific',
					'create' => 'users.custom.create-tastic',
					'show' => 'users.custom.show-ulous',
					'edit' => 'users.custom.edit-ifying',
				])
				->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
					->index('Users')
					->create('New User')
					->show(fn(User $user) => $user->name)
					->edit('Edit'));
		});
		
		$this->get(route('users.custom.index-arific'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
		);
		
		$this->get(route('users.custom.create-tastic'));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			['New User', '/users/create'],
		);
		
		$this->get(route('users.custom.show-ulous', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.custom.show-ulous', $this->user)],
		);
		
		$this->get(route('users.custom.edit-ifying', $this->user));
		$this->assertActiveBreadcrumbs(
			['Users', '/users'],
			[$this->user->name, route('users.custom.show-ulous', $this->user)],
			['Edit', route('users.custom.edit-ifying', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_custom_parents(bool $cache): void
	{
		$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
			$resource
				->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
					->index('Users', 'home')
					->create('New User', 'home')
					->show(fn(User $user) => $user->name, 'home')
					->edit('Edit', 'home'));
		});
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['New User', '/users/create'],
		);
		
		$this->get(route('users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			[$this->user->name, route('users.show', $this->user)],
		);
		
		$this->get(route('users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Edit', route('users.edit', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_custom_index_parent(bool $cache): void
	{
		$this->registerResourceRoute($cache, function(PendingResourceRegistration $resource) {
			$resource
				->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
					->index('Users', 'home')
					->create('New User')
					->show(fn(User $user) => $user->name)
					->edit('Edit'));
		});
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			['New User', '/users/create'],
		);
		
		$this->get(route('users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
		);
		
		$this->get(route('users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Home', '/'],
			['Users', '/users'],
			[$this->user->name, route('users.show', $this->user)],
			['Edit', route('users.edit', $this->user)],
		);
	}
	
	/** @dataProvider cachingProvider */
	public function test_custom_parameter_name(bool $cache): void
	{
		Route::middleware(SubstituteBindings::class)
			->group(function() {
				Route::resource('users', ResourceRoutesTestJazzyDancerController::class)
					->parameter('users', 'jazzy_dancer')
					->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
						->index('Jazzy Dancers')
						->create('New Dancer')
						->show(fn(User $jazzy_dancer) => $jazzy_dancer->name)
						->edit('Edit'));
			});
		
		$this->setUpCache($cache);
		
		$this->get(route('users.index'));
		$this->assertActiveBreadcrumbs(
			['Jazzy Dancers', '/users'],
		);
		
		$this->get(route('users.create'));
		$this->assertActiveBreadcrumbs(
			['Jazzy Dancers', '/users'],
			['New Dancer', '/users/create'],
		);
		
		$this->get(route('users.show', $this->user));
		$this->assertActiveBreadcrumbs(
			['Jazzy Dancers', '/users'],
			[$this->user->name, route('users.show', $this->user)],
		);
		
		$this->get(route('users.edit', $this->user));
		$this->assertActiveBreadcrumbs(
			['Jazzy Dancers', '/users'],
			[$this->user->name, route('users.show', $this->user)],
			['Edit', route('users.edit', $this->user)],
		);
	}

	/**
	 * @see https://github.com/glhd/gretel/issues/7
	 * @dataProvider cachingProvider
	 */
	public function test_grouped_resource_routes(bool $cache): void
	{
		Route::middleware(SubstituteBindings::class)
			->group(function() {
				Route::resource('movies', ResourceRoutesTestController::class)
					->except(['show'])
					->breadcrumbs(function($breadcrumbs) {
						$breadcrumbs->index('Movies')
							->create('Create')
							->edit('Edit', '.index');
					});

				Route::prefix('/movies/{movie}')
					->group(function() {
						Route::resource('actors', ResourceRoutesTestController::class)
							->except(['index', 'show'])
							->breadcrumbs(function($breadcrumbs) {
								$breadcrumbs
									->create('Create', 'movies.edit', fn($movie) => $movie)
									->edit('Edit', 'movies.edit', fn($movie) => $movie);
							});
					});
			});

		$this->setUpCache($cache);

		$this->get('/movies/1/actors/create');

		$this->assertActiveBreadcrumbs(
			['Movies', '/movies'],
			['Edit', '/movies/1/edit'],
			['Create', '/movies/1/actors/create'],
		);
	}

    /**
     * @dataProvider cachingProvider
     */
    public function test_nested_shallow_resource(bool $cache): void
    {
        Route::middleware(SubstituteBindings::class)
            ->group(function() {
                Route::resource('users', ResourceRoutesTestController::class)
                    ->breadcrumbs([
                        'index' => 'Users',
                        'create' => 'New User',
                        'edit' => 'Edit',
                    ]);

                Route::resource('users.notes', NotesController::class)
                    ->shallow()
                    ->breadcrumbs(fn(ResourceBreadcrumbs $breadcrumbs) => $breadcrumbs
                        ->show(fn(Note $note) => $note->note, 'users.index', fn(Note $note) => $note->user)
                        ->edit('Edit', '.show', fn(Note $note) => $note->user)
                    );
            });

        $note = Note::factory()->create(['note' => 'some note']);

        $this->setUpCache($cache);

        $this->get('/notes/'.$note->id.'/edit');

        $this->assertActiveBreadcrumbs(
            ['Users', '/users'],
            ['some note', '/notes/'.$note->id],
            ['Edit', '/notes/'.$note->id.'/edit'],
        );
    }

    protected function registerResourceRoute(bool $cache, Closure $setup): self
	{
		Route::middleware(SubstituteBindings::class)
			->group(function() use ($setup) {
				Route::get('/', fn() => 'Home')->name('home')->breadcrumb('Home');
				$setup(Route::resource('users', ResourceRoutesTestController::class));
			});
		
		$this->setUpCache($cache);
		
		return $this;
	}
}

class ResourceRoutesTestController
{
	public function index()
	{
		return 'Users';
	}
	
	public function create()
	{
		return 'Create';
	}
	
	public function show(User $user)
	{
		return $user->name;
	}
	
	public function edit(User $user)
	{
		return $user->name;
	}
}

class ResourceRoutesTestJazzyDancerController
{
	public function index()
	{
		return 'Jazzy Dancers';
	}
	
	public function create()
	{
		return 'Create';
	}
	
	public function show(User $jazzy_dancer)
	{
		return $jazzy_dancer->name;
	}
	
	public function edit(User $jazzy_dancer)
	{
		return $jazzy_dancer->name;
	}
}

class NotesController
{
    public function edit(Note $note)
    {
        return $note;
    }
}
