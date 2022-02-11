<?php

namespace Glhd\Gretel\Resolvers;

use Arr;
use Closure;
use Glhd\Gretel\Exceptions\UnmatchedRouteException;
use Glhd\Gretel\Exceptions\UnresolvableParentException;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParentResolver extends Resolver
{
	public static function make($value, string $name, ?Closure $relation = null): Resolver
	{
		$value = static::wrapClosure($value);
		$relation = static::wrapNullableClosure($relation);
		
		$callback = static function() use ($value, $name, $relation) {
			return [$value, $name, $relation];
		};
		
		return new static($callback);
	}
	
	public function resolve(array $parameters, Registry $registry)
	{
		[$callback, $name, $relation] = parent::resolve($parameters, $registry);
		$result = $callback($parameters);
		
		if (null === $result) {
			return null;
		}
		
		if ($relation) {
			$parameters = Arr::wrap($relation($parameters));
		}
		
		// Handle parent shorthand
		if (is_string($result) && 0 === strpos($result, '.')) {
			$result = Str::beforeLast($name, '.').$result;
		}
		
		// If we get back a route name, we'll load it from the registry and pass
		// on any custom parameters that were provided
		if (is_string($result) && $registry->has($result)) {
			$result = $registry->getOrFail($result);
		}
		
		// If we get back a URL, we'll try to resolve the parent via the Router
		// This may not last in the API — use at your own risk
		if (is_string($result) && filter_var($result, FILTER_VALIDATE_URL)) {
			return $this->findParentByUrl($result, $registry);
		}
		
		if (! ($result instanceof RouteBreadcrumb)) {
			throw new UnresolvableParentException($result, $name);
		}
		
		if (! empty($parameters)) {
			$result = clone $result;
			$result->setParameters($parameters);
		}
		
		return $result;
	}
	
	/**
	 * Please note that this behavior is intentionally undocumented and may be
	 * removed at any time. Use at your own risk.
	 */
	protected function findParentByUrl(string $url, Registry $registry): RouteBreadcrumb
	{
		$router = app('router');
		$request = Request::createFromBase(SymfonyRequest::create($url));
		
		try {
			$route = $router->getRoutes()->match($request);
		} catch (NotFoundHttpException $exception) {
			throw new UnmatchedRouteException($url);
		}
		
		if ($route->hasParameters()) {
			$router->substituteBindings($route);
			$router->substituteImplicitBindings($route);
		}
		
		$breadcrumb = $registry->getOrFail($route);
		$breadcrumb->setParameters($route->parameters());
		
		return $breadcrumb;
	}
}
