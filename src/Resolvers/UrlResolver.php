<?php

namespace Glhd\Gretel\Resolvers;

use Illuminate\Support\Arr;

class UrlResolver extends Resolver
{
	public static function make(string $name, array $parameter_names): self
	{
		$callback = function(array $route_parameters) use ($name, $parameter_names) {
			$keys = Arr::isAssoc($route_parameters)
				? $parameter_names
				: array_keys($parameter_names);
			
			return route($name, Arr::only($route_parameters, $keys));
		};
		
		return new self($callback);
	}
}
