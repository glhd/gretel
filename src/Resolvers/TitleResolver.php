<?php

namespace Glhd\Gretel\Resolvers;

use Glhd\Gretel\Registry;
use Illuminate\Routing\Route;

class TitleResolver extends Resolver
{
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		return array_values($route->parameters());
	}
}
