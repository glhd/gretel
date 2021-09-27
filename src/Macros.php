<?php

namespace Glhd\Gretel;

use Closure;
use Glhd\Gretel\Exceptions\UnnamedRouteException;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Glhd\Gretel\Routing\Breadcrumbs;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

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
		if (!$route->getName()) {
			throw new UnnamedRouteException();
		}
		
		$name = $route->getName();
		$parameters = $route->parameterNames();
		
		$title = TitleResolver::make($title, $parameters);
		$url = UrlResolver::makeForRoute($name, $parameters);
		
		$parent = static::resolveParent($registry, $name, $parent);
		$parent = ParentResolver::makeWithRelation($parent, $parameters, $relation);
		
		$registry->register(new RouteBreadcrumb($name, $title, $parent, $url));
		
		return $route;
	}
	
	public static function breadcrumbs(Registry $registry, Route $route): Breadcrumbs
	{
		return new Breadcrumbs($registry, $route);
	}
	
	protected static function resolveParent(Registry $registry, string $name, $parent)
	{
		if ($parent instanceof Closure) {
			return static function(...$args) use ($name, $parent) {
				$result = $parent(...$args);
				return Macros::resolveParent(app(Registry::class), $name, $result);
			};
		}
		
		if (!is_string($parent)) {
			return $parent;
		}
		
		if (0 === strpos($parent, '.')) {
			$parent = Str::beforeLast($name, '.').$parent;
		}
		
		return $registry->getOrFail($parent);
	}
}
