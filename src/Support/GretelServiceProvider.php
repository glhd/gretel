<?php

namespace Glhd\Gretel\Support;

use Glhd\Gretel\Macros;
use Glhd\Gretel\Registry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GretelServiceProvider extends ServiceProvider
{
	protected string $base_dir;
	
	public function __construct($app)
	{
		parent::__construct($app);
		
		$this->base_dir = dirname(__DIR__, 2);
	}
	
	public function boot()
	{
		require_once __DIR__.'/helpers.php';
		
		$this->bootConfig();
		$this->bootViews();
		$this->bootBladeComponents();
	}
	
	public function register()
	{
		$this->mergeConfigFrom("{$this->base_dir}/config.php", 'gretel');
		
		$this->app->singleton(Registry::class);
		$this->app->booting(fn(Container $app) => Macros::register($app->make(Registry::class)));
	}
	
	protected function bootViews() : self
	{
		$views_directory = "{$this->base_dir}/resources/views";
		
		$this->loadViewsFrom($views_directory, 'gretel');
		
		if (method_exists($this->app, 'resourcePath')) {
			$this->publishes([
				$views_directory => $this->app->resourcePath('views/vendor/gretel'),
			], 'gretel-views');
		}
		
		return $this;
	}
	
	protected function bootBladeComponents() : self
	{
		if (version_compare($this->app->version(), '8.0.0', '>=')) {
			Blade::componentNamespace('Glhd\\Gretel\\Components', 'gretel');
		}
		
		return $this;
	}
	
	protected function bootConfig() : self
	{
		if (method_exists($this->app, 'configPath')) {
			$this->publishes([
				"{$this->base_dir}/config.php" => $this->app->configPath('gretel.php'),
			], 'gretel-config');
		}
		
		return $this;
	}
}
