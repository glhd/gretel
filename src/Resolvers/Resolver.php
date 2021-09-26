<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Registry;
use Illuminate\Routing\Route;
use Illuminate\Support\Traits\ReflectsClosures;
use Opis\Closure\SerializableClosure;

class Resolver
{
	use ReflectsClosures;
	
	/**
	 * Because of the way closure serialization happens, this can't be type hinted.
	 * 
	 * @var SerializableClosure
	 */
	protected $callback;
	
	protected array $parameters;
	
	public static function make($value, array $parameters = []): self
	{
		// If value is a closure, we'll use late static binding
		if ($value instanceof Closure) {
			return new static($value, $parameters);
		}
		
		// Otherwise, we'll instantiate a plain/base resolver instance
		return new self(fn() => $value, $parameters);
	}
	
	public function __construct(Closure $callback, array $parameters)
	{
		$this->callback = new SerializableClosure($callback);
		$this->parameters = $parameters;
	}
	
	/**
	 * @return \Glhd\Gretel\Breadcrumb|string
	 */
	public function resolve(Route $route, Registry $registry)
	{
		return call_user_func_array($this->callback->getClosure(), $this->resolveParameters($route, $registry));
	}
	
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		return array_values($route->parameters());
	}
}
