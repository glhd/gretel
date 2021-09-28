<?php

namespace Glhd\Gretel;

use Closure;
use Glhd\Gretel\Exceptions\UnnamedRouteException;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Glhd\Gretel\Routing\RequestBreadcrumbs;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;

class Macros
{
	public static function register(Registry $registry): void
	{
		Route::macro('breadcrumb', function($title = null, $parent = null, $relation = null) use ($registry) {
			return 0 === func_num_args()
				? Macros::breadcrumbs($registry, $this)
				: Macros::breadcrumb($registry, $this, $title, $parent, $relation);
		});
		
		Route::macro('breadcrumbs', function() use ($registry) {
			return Macros::breadcrumbs($registry, $this);
		});
	}
	
	public static function breadcrumb(
		Registry $registry,
		Route $route,
		$title,
		$parent = null,
		Closure $relation = null
	): Route {
		if (!$name = $route->getName()) {
			throw new UnnamedRouteException();
		}
		
		$title = TitleResolver::make($title);
		$parent = ParentResolver::make($parent, $name, $relation);
		$url = UrlResolver::make($name, $route->parameterNames());
		
		$registry->register(new RouteBreadcrumb($name, $title, $parent, $url));
		
		return $route;
	}
	
	public static function breadcrumbs(Registry $registry, Route $route): RequestBreadcrumbs
	{
		return new RequestBreadcrumbs($registry, $route);
	}
}
