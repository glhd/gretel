<?php

namespace Glhd\Gretel;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin Collection
 */
class Registry
{
	use ForwardsCalls;
	
	protected Collection $breadcrumbs;
	
	public function __construct()
	{
		$this->breadcrumbs = new Collection();
	}
	
	public function clear(): Registry
	{
		$this->breadcrumbs = new Collection();
		
		return $this;
	}
	
	public function register(RouteBreadcrumb ...$breadcrumbs): Registry
	{
		foreach ($breadcrumbs as $breadcrumb) {
			$this->breadcrumbs->put($breadcrumb->name, $breadcrumb);
		}
		
		return $this;
	}
	
	public function get($route): ?RouteBreadcrumb
	{
		if (!$name = $this->resolveName($route)) {
			return null;
		}
		
		return $this->breadcrumbs->get($name);
	}
	
	public function getOrFail($route): RouteBreadcrumb
	{
		if ($breadcrumb = $this->get($route)) {
			return $breadcrumb;
		}
		
		throw new MissingBreadcrumbException($this->resolveName($route));
	}
	
	public function __call($name, $arguments)
	{
		return $this->forwardDecoratedCallTo($this->breadcrumbs, $name, $arguments);
	}
	
	protected function resolveName($route): ?string
	{
		return $route instanceof Route
			? $route->getName()
			: (string) $route;
	}
}
