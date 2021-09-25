<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Registry;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;
use Opis\Closure\SerializableClosure;

class Resolver
{
	use ReflectsClosures;
	
	protected Closure $callback;
	
	protected array $parameters;
	
	public static function make($value, array $parameters = []): self
	{
		$value = static::unserializeIfSerialized($value);
		
		// If value is a closure, we'll use late static binding
		if ($value instanceof Closure) {
			return new static($value, $parameters);
		}
		
		// Otherwise, we'll instantiate a plain/base resolver instance
		return new self(fn() => $value, $parameters);
	}
	
	protected static function unserializeIfSerialized($value)
	{
		if (static::isSerializedClosure($value)) {
			$closure = unserialize($value, ['allowed_classes' => SerializableClosure::class]);
			if ($closure instanceof SerializableClosure) {
				return $closure->getClosure();
			}
		}
		
		return $value;
	}
	
	protected static function isSerializedClosure($value)
	{
		$fragment = 'C:'.strlen(SerializableClosure::class).':"'.SerializableClosure::class;
		
		return is_string($value) && Str::startsWith($value, $fragment);
	}
	
	public function __construct(Closure $callback, array $parameters)
	{
		$this->callback = $callback;
		$this->parameters = $parameters;
	}
	
	/**
	 * @return \Glhd\Gretel\Breadcrumb|string
	 */
	public function resolve(Route $route, Registry $registry)
	{
		return call_user_func_array($this->callback, $this->resolveParameters($route, $registry));
	}
	
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		return [];
	}
}
