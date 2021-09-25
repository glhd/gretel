<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Registry;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class ParentResolver extends Resolver
{
	public function resolve(Route $route, Registry $registry)
	{
		$result = parent::resolve($route, $registry);
		
		if (is_string($result) && filter_var($result, FILTER_VALIDATE_URL)) {
			return $this->findParentByUrl($result, $registry);
		}
		
		if (!($result instanceof Breadcrumb)) {
			throw new RuntimeException('Unable to resolve parent breadcrumb.');
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
	
	protected function findParentByUrl(string $url, Registry $registry)
	{
		$router = app('router');
		$request = Request::createFromBase(SymfonyRequest::create($url));
		
		if (!$route = $router->getRoutes()->match($request)) {
			throw new RuntimeException('Unable to find route for parent.'); // FIXME
		}
		
		if ($route->hasParameters()) {
			$router->substituteBindings($route);
			$router->substituteImplicitBindings($route);
		}
		
		// FIXME: It may be safer to bind this data to the Breadcrumbs object, and then
		// forcibly reset that object each time a new request it bound. That way we're never
		// messing with the breadcrumb objects themselves—just telling the current request
		// how to handle the parent.
		
		$breadcrumb = $registry->getOrFail($route);
		$breadcrumb->setRoute($route);
		
		return $breadcrumb;
	}
}
