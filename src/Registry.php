<?php

namespace Glhd\Gretel;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

class Registry
{
	protected Collection $routes;
	
	public function __construct()
	{
		$this->routes = new Collection();
	}
	
	public function register(RouteBreadcrumb $breadcrumb): Registry
	{
		$this->routes->put($breadcrumb->name, $breadcrumb);
		
		return $this;
	}
	
	public function get($route): ?RouteBreadcrumb
	{
		$name = $route instanceof Route
			? $route->getName()
			: $route;
		
		return $this->routes->get($name);
	}
}
