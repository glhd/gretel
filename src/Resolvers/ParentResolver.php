<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Exceptions\UnmatchedRouteException;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParentResolver extends Resolver
{
	public static function makeWithRelation($value, array $parameters = [], Closure $relation = null): Resolver
	{
		if ($relation) {
			if ($value instanceof RouteBreadcrumb) {
				$parent = clone $value;
			} else {
				$parent = $value;
			}
			
			$relation = static::optimizeBinding($relation);
			
			if ($parent instanceof Closure) {
				$parent = static::optimizeBinding($parent);
			}
			
			$value = static function($parameters) use ($parent, $relation) {
				$parameters = array_values($parameters);
				
				if ($parent instanceof Closure) {
					$parent = clone $parent(...$parameters);
				}
				
				return $parent->setParameters(Arr::wrap($relation(...$parameters)));
			};
		} elseif ($value instanceof Closure) {
			// If we're been passed a closure, we need to pass the parameters
			// in as individual arguments.
			$original = $value;
			$value = static function($parameters) use ($original) {
				return call_user_func_array($original, array_values($parameters));
			};
		}
		
		return parent::make($value, $parameters);
	}
	
	public function resolve(array $parameters, Registry $registry)
	{
		$result = parent::resolve($parameters, $registry);
		
		if (is_string($result)) {
			// If we get back a URL, we'll try to resolve the parent via the Router
			if (filter_var($result, FILTER_VALIDATE_URL)) {
				return $this->findParentByUrl($result, $registry);
			}
			
			// If we get back a route name, we'll load it from the registry and pass
			// on any custom parameters that were provided
			if ($registry->has($result)) {
				$parent = clone $registry->getOrFail($result);
				return $parent->setParameters($parameters);
			}
		}
		
		if (!($result instanceof RouteBreadcrumb)) {
			throw new RuntimeException('Unable to resolve parent breadcrumb.');
		}
		
		return $result;
	}
	
	protected function transformParameters(array $parameters, Registry $registry): array
	{
		return [$parameters];
	}
	
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
