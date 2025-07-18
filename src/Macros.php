<?php

namespace Glhd\Gretel;

use Closure;
use Glhd\Gretel\Exceptions\UnnamedRouteException;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Glhd\Gretel\Routing\RequestBreadcrumbs;
use Glhd\Gretel\Routing\ResourceBreadcrumbs;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\Route;
use InvalidArgumentException;

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
		
		PendingResourceRegistration::macro('breadcrumbs', function($breadcrumbs) use ($registry) {
			Macros::resourceBreadcrumbs($registry, $this->name, $this->options, $breadcrumbs);
			
			return $this;
		});
	}
	
	public static function breadcrumb(
		Registry $registry,
		Route $route,
		$title,
		$parent = null,
		?Closure $relation = null
	): Route {
		$registry->withExceptionHandling(function() use ($registry, $route, $title, $parent, $relation) {
			if (! $name = $route->getName()) {
				throw new UnnamedRouteException();
			}
			
			$title = TitleResolver::make($title);
			$parent = ParentResolver::make($parent, $name, $relation);
			$url = UrlResolver::make($name, $route->parameterNames());
			
			$registry->register(new RouteBreadcrumb($name, $title, $parent, $url));
		});
		
		return $route;
	}
	
	public static function breadcrumbs(Registry $registry, Route $route): RequestBreadcrumbs
	{
		return new RequestBreadcrumbs($registry, $route);
	}
	
	public static function resourceBreadcrumbs(Registry $registry, string $name, array $options, $setup): void
	{
		$breadcrumbs = new ResourceBreadcrumbs($name, $options);
		
		if (is_array($setup)) {
			$breadcrumbs->configure($setup);
		} elseif ($setup instanceof Closure) {
			$setup($breadcrumbs);
		} else {
			throw new InvalidArgumentException('Route::resource()->breadcrumbs() expects an array or closure.');
		}
		
		$breadcrumbs->register($registry);
	}
}
