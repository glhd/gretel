<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Exceptions\UnmatchedRouteException;
use Glhd\Gretel\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParentResolver extends Resolver
{
	public static function makeWithRelation($value, array $parameters = [], $relation = null): Resolver
	{
		if (null === $relation) {
			return parent::make($value, $parameters);
		}
		
		if (!is_string($relation) && !is_array($relation) && !$relation instanceof Closure) {
			throw new InvalidArgumentException('Invalid parent relation (expecting string, array, or Closure).');
		}
		
		if (!$value instanceof Breadcrumb) {
			throw new InvalidArgumentException('Unable to find parent breadcrumb.');
		}
		
		$parent = clone $value;
		
		if (is_string($relation)) {
			$relation = [$relation => $relation];
		}
		
		if (is_array($relation)) {
			$callback = static function($parameters) use ($parent, $relation) {
				$child = collect($parameters)->first(fn($parameter) => $parameter instanceof Model);
				$parameters = collect($relation)
					->mapWithKeys(function($relation, $parameter) use ($child) {
						if (is_int($parameter)) {
							$parameter = $relation;
						}
						return [$parameter => $child->{$relation}];
					})
					->merge($parameters)
					->all();
				
				return $parent->setParameters($parameters);
			};
		} else {
			if (config('gretel.static_closures')) {
				$relation->bindTo(null);
			}
			$callback = static function($parameters) use ($parent, $relation) {
				$result = call_user_func_array($relation, array_values($parameters));
				return $parent->setParameters($result);
			};
		}
		
		return parent::make($callback, $parameters);
	}
	
	public function resolve(array $parameters, Registry $registry)
	{
		$result = parent::resolve($parameters, $registry);
		
		if (is_string($result) && filter_var($result, FILTER_VALIDATE_URL)) {
			return $this->findParentByUrl($result, $registry);
		}
		
		if (!($result instanceof Breadcrumb)) {
			throw new RuntimeException('Unable to resolve parent breadcrumb.');
		}
		
		return $result;
	}
	
	protected function transformParameters(array $parameters, Registry $registry): array
	{
		// If they've type hinted a Breadcrumb argument, we'll prepend that
		// to the arguments we're going to call the closure with. This means
		// that the simple case of `breadcrumb(fn(User $user) => $user->name)`
		// will work (the majority of cases), but a `Breadcrumb` typehint can
		// be used to can more control if necessary.
		try {
			if (Breadcrumb::class === $this->firstClosureParameterType($this->callback)) {
				return [new Breadcrumb(), ...array_values($parameters)];
			}
		} catch (RuntimeException $exception) {
			// If closure has no parameters, then we don't need to inject the Breadcrumb
		}
		
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
