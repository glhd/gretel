<?php

namespace Glhd\Gretel\Commands;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\Cache;
use Illuminate\Console\Command;

class CacheBreadcrumbs extends Command
{
	protected $signature = 'breadcrumbs:cache';
	
	protected $description = 'Cache breadcrumbs';
	
	public function handle(Cache $cache, Registry $registry)
	{
		$routes_are_cached = $this->laravel->routesAreCached();
		
		try {
			if ($routes_are_cached) {
				$this->call('route:clear');
			}
			
			$this->call('breadcrumbs:clear');
			
			if ($cache->write($registry)) {
				$this->info('Breadcrumbs cached successfully!');
				return 0;
			}
			
			$this->error('Unable to cache breadcrumbs.');
			return 1;
		} finally {
			if ($routes_are_cached) {
				$this->call('route:cache');
			}
		}
	}
	
	protected function withUncachedRoutes(Closure $callback)
	{
		if (! $this->laravel->routesAreCached()) {
			return $callback();
		}
		
		try {
			$this->call('route:clear');
			return $callback();
		} finally {
			$this->call('route:cache');
		}
	}
}
