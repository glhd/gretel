<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\Cache;

trait TestsCachedBreadcrumbs
{
	public static function cachingProvider(): array
	{
		return [
			'Uncached' => [false],
			'Cached' => [true],
		];
	}
	
	protected function setUpCache(bool $cache = true): self
	{
		if ($cache) {
			$this->artisan('breadcrumbs:cache');
			$this->app->make(Registry::class)->clear();
			$this->app->make(Cache::class)->load();
		} else {
			$this->artisan('breadcrumbs:clear');
		}
		
		return $this;
	}
}
