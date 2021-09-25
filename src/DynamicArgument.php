<?php

namespace Glhd\Gretel;

use Closure;
use Illuminate\Routing\RouteDependencyResolverTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;
use InvalidArgumentException;
use Opis\Closure\SerializableClosure;

class DynamicArgument
{
	use ReflectsClosures;
	use RouteDependencyResolverTrait;
	
	protected $value;
	
	protected RouteBreadcrumb $breadcrumb;
	
	public function __construct(RouteBreadcrumb $breadcrumb, $value)
	{
		if ($value instanceof SerializableClosure) {
			$value = $value->getClosure();
		}
		
		if (!is_string($value) && !($value instanceof Closure)) {
			$got = gettype($value);
			throw new InvalidArgumentException("Breadcrumb arguments must be a string or a Closure, but got '{$got}'.");
		}
		
		$this->breadcrumb = $breadcrumb;
		$this->value = $value;
	}
	
	public function resolve(): ?string
	{
		$result = $this->value;
		
		if ($result instanceof Closure) {
			$result = $this->callClosure();
		}
		
		if (is_string($result)) {
			return $result;
		}
		
		return null;
	}
	
	protected function callClosure(): ?string
	{
		$closure = $this->value;
		
		// We'll pass all the route parameters into the closure
		$parameters = array_values($this->breadcrumb->route->parameters());
		
		// If they've type hinted a Breadcrumb argument, we'll prepend that
		// to the arguments we're going to call the closure with. This means
		// that the simple case of `breadcrumb(fn(User $user) => $user->name)`
		// will work (the majority of cases), but a `Breadcrumb` typehint can
		// be used to can more control if necessary.
		if (Breadcrumb::class === $this->firstClosureParameterType($closure)) {
			array_unshift($parameters, new Breadcrumb());
		}
		
		$result = $closure(...$parameters);
		
		return is_string($result)
			? $result
			: null;
	}
	
	protected function isSerializedClosure($value)
	{
		return is_string($value)
			&& Str::startsWith($value, 'C:'.strlen(SerializableClosure::class).':"'.SerializableClosure::class);
	}
}
