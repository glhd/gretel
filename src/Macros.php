<?php

namespace Glhd\Gretel;

use Glhd\Gretel\Exceptions\UnnamedRouteException;
use Glhd\Gretel\Routing\Breadcrumbs;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class Macros
{
	public static function register(Registry $registry): void
	{
		Route::macro('breadcrumb', function($title = null, $parent = null) use ($registry) {
			return 0 === func_num_args()
				? Macros::breadcrumbs($registry, $this)
				: Macros::breadcrumb($registry, $this, $title, $parent);
		});
		
		Route::macro('breadcrumbs', function() use ($registry) {
			return Macros::breadcrumbs($registry, $this);
		});
	}
	
	public static function breadcrumb(Registry $registry, Route $route, $title, $parent = null): Route
	{
		if (!$route->getName()) {
			throw new UnnamedRouteException();
		}
		
		$name = $route->getName();
		$parameters = $route->parameterNames();
		$parent = static::resolveParent($registry, $name, $parent);
		
		$registry->register(new RouteBreadcrumb($name, $parameters, $title, $parent));
		
		return $route;
	}
	
	public static function breadcrumbs(Registry $registry, Route $route): Breadcrumbs
	{
		return new Breadcrumbs($registry, $route);
	}
	
	protected static function resolveParent(Registry $registry, string $name, $parent)
	{
		if (!is_string($parent)) {
			return $parent;
		}
		
		if (0 === strpos($parent, '.')) {
			$parent = Str::beforeLast($name, '.').$parent;
		}
		
		return $registry->get($parent);
	}
}
