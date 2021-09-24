<?php

namespace Galahad\LaravelPackageTemplate\Tests;

use Galahad\LaravelPackageTemplate\Support\PackageServiceProvider;
use Illuminate\Container\Container;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	protected function setUp() : void
	{
		parent::setUp();
		
		$config = $this->app['config'];
		
		// Add encryption key for HTTP tests
		$config->set('app.key', 'base64:tfsezwCu4ZRixRLA/+yL/qoouX++Q3lPAPOAbtnBCG8=');
	}
	
	protected function getPackageProviders($app)
	{
		return [
			PackageServiceProvider::class,
		];
	}
	
	protected function getPackageAliases($app)
	{
		return [];
	}
}
