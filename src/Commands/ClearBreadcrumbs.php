<?php

namespace Glhd\Gretel\Commands;

use Glhd\Gretel\Support\Cache;
use Illuminate\Console\Command;

class ClearBreadcrumbs extends Command
{
	protected $signature = 'breadcrumbs:clear';
	
	protected $description = 'Clear cached breadcrumbs';
	
	public function handle(Cache $cache)
	{
		if ($cache->clear()) {
			$this->info('Breadcrumbs cache cleared!');
			return 0;
		}
		
		$this->error('Unable to clear breadcrumb cache.');
		return 1;
	}
}
