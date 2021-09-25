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
	
	protected RouteCollectionInterface $routes;
	
	public function __construct(RouteCollectionInterface $routes)
	{
		$this->routes = $routes;
		$this->breadcrumbs = new Collection();
	}
	
	public function register(RouteBreadcrumb $breadcrumb): Registry
	{
		$this->breadcrumbs->put($breadcrumb->name, $breadcrumb);
		
		return $this;
	}
	
	public function get($route): ?RouteBreadcrumb
	{
		$name = $this->resolveName($route);
		
		return $this->breadcrumbs->get($name);
	}
	
	public function getOrFail($route): RouteBreadcrumb
	{
		if ($breadcrumb = $this->get($route)) {
			return $breadcrumb;
		}
		
		throw new MissingBreadcrumbException($this->resolveName($route));
	}
	
	protected function resolveName($route): string
	{
		return $route instanceof Route
			? $route->getName()
			: $route;
	}
}
