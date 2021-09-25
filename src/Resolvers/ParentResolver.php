<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Registry;
use Illuminate\Routing\Route;
use RuntimeException;

class ParentResolver extends Resolver
{
	public function resolve(Route $route, Registry $registry)
	{
		$result = parent::resolve($route, $registry);
		
		if (is_string($result)) {
			return $registry->get($result);
		}
		
		return $result;
	}
	
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		// We'll pass all the route parameters into the closure
		$parameters = array_values($route->parameters());
		
		// If they've type hinted a Breadcrumb argument, we'll prepend that
		// to the arguments we're going to call the closure with. This means
		// that the simple case of `breadcrumb(fn(User $user) => $user->name)`
		// will work (the majority of cases), but a `Breadcrumb` typehint can
		// be used to can more control if necessary.
		try {
			if (Breadcrumb::class === $this->firstClosureParameterType($this->callback)) {
				array_unshift($parameters, new Breadcrumb());
			}
		} catch (RuntimeException $exception) {
			// If closure has no parameters, then we don't need to inject the Breadcrumb
		}
		
		return $parameters;
	}
}
