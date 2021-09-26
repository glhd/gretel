<?php

namespace Glhd\Gretel\Commands;

use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\Cache;
use Illuminate\Console\Command;

class CacheBreadcrumbs extends Command
{
	protected $signature = 'breadcrumbs:cache';
	
	protected $description = 'Cache breadcrumbs';
	
	public function handle(Cache $cache, Registry $registry)
	{
		if ($this->laravel->routesAreCached()) {
			$this->error("You must call '{$this->signature}' before you cache routes!");
			return 1;
		}
		
		$this->call('breadcrumbs:clear');
		
		if ($cache->write($registry)) {
			$this->info('Breadcrumbs cached successfully!');
			return 0;
		}
		
		$this->error('Unable to cache breadcrumbs.');
		return 1;
	}
}
