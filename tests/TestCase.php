<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Support\GretelServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	use RefreshDatabase;
	
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
