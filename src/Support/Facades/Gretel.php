<?php

namespace Glhd\Gretel\Support\Facades;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Registry breadcrumb(string $name, $title = null, $parent = null, $relation = null)
 * @method static Registry handleMissingBreadcrumbs(Closure $callback)
 * @method static Registry throwOnMissingBreadcrumbs(bool $throw = true)
 * @method static Registry handleMisconfiguredBreadcrumbs(Closure $callback)
 * @method static Registry throwOnMisconfiguredBreadcrumbs(bool $throw = true)
 * @method static RouteBreadcrumb|null get(Route|string $route)
 * @method static RouteBreadcrumb getOrFail(Route|string $route)
 */
class Gretel extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return Registry::class;
	}
}
