<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Support\GretelServiceProvider;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	protected function setUp() : void
	{
		parent::setUp();
		
		// Add encryption key for HTTP tests
		$config = $this->app->make(Repository::class);
		$config->set('app.key', 'base64:tfsezwCu4ZRixRLA/+yL/qoouX++Q3lPAPOAbtnBCG8=');
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
}
