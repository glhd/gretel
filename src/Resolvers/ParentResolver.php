<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Exceptions\UnmatchedRouteException;
use Glhd\Gretel\Registry;
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
			if ($value instanceof Breadcrumb) {
				$parent = clone $value;
			} else {
				$parent = $value;
			}
			
			if (config('gretel.static_closures')) {
				$relation = $relation->bindTo(null);
				
				if ($parent instanceof Closure) {
					$parent = $parent->bindTo(null);
				}
			}
			
			$value = static function($parameters) use ($parent, $relation) {
				if ($parent instanceof Closure) {
					$parent = clone call_user_func_array($parent, array_values($parameters));
				}
				
				$result = Arr::wrap(call_user_func_array($relation, array_values($parameters)));
				
				return $parent->setParameters($result);
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
			if (filter_var($result, FILTER_VALIDATE_URL)) {
				return $this->findParentByUrl($result, $registry);
			}
			if ($registry->has($result)) {
				$parent = clone $registry->getOrFail($result);
				return $parent->setParameters($parameters);
			}
		}
		
		if (!($result instanceof Breadcrumb)) {
			throw new RuntimeException('Unable to resolve parent breadcrumb.');
		}
		
		return $result;
	}
	
	protected function transformParameters(array $parameters, Registry $registry): array
	{
		return [$parameters];
	}
	
	protected function findParentByUrl(string $url, Registry $registry): Breadcrumb
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
