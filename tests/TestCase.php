<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Support\GretelServiceProvider;
use Glhd\Gretel\View\Breadcrumb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	use RefreshDatabase;
	
	/**
	 * @param array{string, string} ...$expectations
	 * @return $this
	 */
	protected function assertActiveBreadcrumbs(array ...$expectations): self
	{
		$breadcrumbs = $this->app->make(Route::class)
			->breadcrumbs()
			->toCollection();
		
		foreach ($expectations as $index => [$title, $url]) {
			$this->assertInstanceOf(Breadcrumb::class, $breadcrumbs[$index] ?? null);
			$this->assertEquals($title, $breadcrumbs[$index]->title);
			$this->assertEquals(url($url), $breadcrumbs[$index]->url);
		}
		
		return $this;
	}
	
	protected function getPackageProviders($app)
	{
		return [
			GretelServiceProvider::class,
		];
	}
	
	protected function getPackageAliases($app)
	{
		return [];
	}
	
	protected function getApplicationTimezone($app)
	{
		return 'America/New_York';
	}
	
	protected function defineDatabaseMigrations()
	{
		$this->loadMigrationsFrom(__DIR__.'/database/migrations');
	}
	
	protected function renderBlade($contents, array $data = [])
	{
		return (new InlineBlade($contents, $data))->render();
	}
}
