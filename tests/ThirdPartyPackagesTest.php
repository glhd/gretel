<?php

namespace Glhd\Gretel\Tests;

use Closure;
use Glhd\Gretel\Tests\Mocks\Inertia;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// We need to manually import this so that \Inertia\Inertia exists when the package boots
require_once __DIR__.'/Mocks/Inertia.php';

class ThirdPartyPackagesTest extends TestCase
{
	public function test_it_registers_breadcrumbs_with_inertia(): void
	{
		Route::middleware(SubstituteBindings::class)->group(function() {
			Route::get('/', fn() => 'OK')->name('home')->breadcrumb('Home');
			Route::get('/users', fn() => 'OK')->name('users.index')->breadcrumb('Users', 'home');
			Route::get('/users/create', fn() => 'OK')->name('users.create')->breadcrumb('Create User', '.index');
		});
		
		$this->get(route('users.create'))->assertOk();
		
		$this->assertArrayHasKey('breadcrumbs', Inertia::$shared);
		$this->assertInstanceOf(Closure::class, Inertia::$shared['breadcrumbs']);
		
		$expected = [
			[
				'title' => 'Home',
				'url' => route('home'),
				'is_current_page' => false,
			],
			[
				'title' => 'Users',
				'url' => route('users.index'),
				'is_current_page' => false,
			],
			[
				'title' => 'Create User',
				'url' => route('users.create'),
				'is_current_page' => true,
			],
		];
		
		$this->assertSame($expected, $this->app->call(Inertia::$shared['breadcrumbs']));
	}
}
