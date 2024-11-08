<?php

namespace Glhd\Gretel\Support;

use Glhd\Gretel\Commands\CacheBreadcrumbs;
use Glhd\Gretel\Commands\ClearBreadcrumbs;
use Glhd\Gretel\Macros;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Routing\RequestBreadcrumbs as RouteBreadcrumbs;
use Glhd\Gretel\View\Components\Breadcrumbs as BreadcrumbComponent;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Inertia\Inertia;

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
		$this->bootConfig();
		$this->bootViews();
		$this->bootBladeComponents();
		$this->bootCommands();
		$this->bootCachedBreadcrumbs();
		$this->bootThirdParty();
	}
	
	public function register()
	{
		$this->mergeConfigFrom("{$this->base_dir}/config.php", 'gretel');
		
		$this->app->singleton(Registry::class, function(Application $app) {
			return new Registry($app->make('router'));
		});
		
		$this->app->singleton(RouteBreadcrumbs::class);
		
		$this->app->singleton(Cache::class, function(Application $app) {
			return new Cache(
				$app->make(Filesystem::class),
				$app->bootstrapPath('cache/gretel-breadcrumbs.php')
			);
		});
		
		// We want to make sure that our breadcrumbs are reset each time a new
		// route instance is bound to the container.
		if (method_exists($this->app, 'rebinding')) {
			$this->app->rebinding(
				Route::class,
				fn(Container $app) => $app->forgetInstance(RouteBreadcrumbs::class)
			);
		}
		
		// This has to happen in booting (before boot) so that the macro
		// is available in time for the RouteServiceProvider.
		$this->app->booting(function(Container $app) {
			Macros::register($app->make(Registry::class));
		});
	}
	
	protected function bootViews(): self
	{
		$views_directory = "{$this->base_dir}/resources/views";
		
		$this->loadViewsFrom($views_directory, 'gretel');
		
		if (method_exists($this->app, 'resourcePath')) {
			$this->publishes([
				$views_directory => $this->app->resourcePath('views/vendor/gretel'),
			], ['gretel', 'gretel-views']);
		}
		
		return $this;
	}
	
	protected function bootBladeComponents(): self
	{
		$this->callAfterResolving(BladeCompiler::class, function() {
			Blade::component(BreadcrumbComponent::class, 'breadcrumbs');
		});
		
		return $this;
	}
	
	protected function bootConfig(): self
	{
		if (method_exists($this->app, 'configPath')) {
			$this->publishes([
				"{$this->base_dir}/config.php" => $this->app->configPath('gretel.php'),
			], ['gretel', 'gretel-config']);
		}
		
		return $this;
	}
	
	protected function bootCommands(): self
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				CacheBreadcrumbs::class,
				ClearBreadcrumbs::class,
			]);
		}
		
		return $this;
	}
	
	protected function bootCachedBreadcrumbs(): self
	{
		if ($this->app->routesAreCached()) {
			$this->app->make(Cache::class)->load();
		}
		
		return $this;
	}
	
	protected function bootThirdParty(): self
	{
		$config = $this->app->make(Repository::class);
		
		$packages = $config->get('gretel.packages', []);
		
		if (Arr::get($packages, 'inertiajs/inertia-laravel') && class_exists(Inertia::class)) {
			Inertia::share('breadcrumbs', static function() {
				if ($route = request()->route()) {
					return $route->breadcrumbs()->jsonSerialize();
				}
				return [];
			});
		}
		
		return $this;
	}
}
