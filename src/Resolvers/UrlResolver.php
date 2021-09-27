<?php

namespace Glhd\Gretel\Resolvers;

use Glhd\Gretel\Registry;
use Illuminate\Support\Arr;

class UrlResolver extends Resolver
{
	public static function makeForRoute(string $name, array $parameters): self
	{
		$callback = function(array $route_parameters) use ($name, $parameters) {
			return route($name, Arr::only($route_parameters, $parameters));
		};
		
		return static::make($callback, $parameters);
	}
	
	protected function transformParameters(array $parameters, Registry $registry): array
	{
		return [$parameters];
	}
}
