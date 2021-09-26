<?php

namespace Glhd\Gretel;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Support\Collection;

class Registry
{
	protected Collection $breadcrumbs;
	
	public function __construct()
	{
		$this->breadcrumbs = new Collection();
	}
	
	public function register(RouteBreadcrumb $breadcrumb): Registry
	{
		$this->breadcrumbs->put($breadcrumb->name, $breadcrumb);
		
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
	
	protected function resolveName($route): ?string
	{
		return $route instanceof Route
			? $route->getName()
			: (string) $route;
	}
}
