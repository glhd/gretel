<?php

namespace Glhd\Gretel\Resolvers;

use Exception;
use Glhd\Gretel\Exceptions\ParentParametersCannotBeInferredException;
use Glhd\Gretel\Registry;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

class UrlResolver extends Resolver
{
	public static function makeForRoute(string $name, array $parameters): self
	{
		$callback = function(Route $route) use ($name, $parameters) {
			try {
				return route($name, Arr::only($route->parameters(), $parameters));
			} catch (UrlGenerationException $exception) {
				throw self::exception($exception, $route, $name);
			}
		};
		
		return static::make($callback, $parameters);
	}
	
	protected static function exception(UrlGenerationException $exception, Route $route, string $name): Exception
	{
		// If the active route somehow doesn't have the parameters it needs, just
		// re-throw the exception (this would only happen if the Breadcrumbs are requested
		// outside of a typical Laravel request lifecycle).
		if ($name === $route->getName()) {
			return $exception;
		}
		
		// Otherwise, we'll throw an exception explaining that the parent route parameters
		// cannot be inferred from the child route.
		return new ParentParametersCannotBeInferredException();
	}
	
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		return [$route];
	}
}
