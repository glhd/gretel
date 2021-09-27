<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\BindsClosures;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Opis\Closure\SerializableClosure;

class Resolver
{
	use BindsClosures;
	
	public array $parameters;
	
	protected ?Closure $callback = null;
	
	protected ?string $serialized = null;
	
	public static function make($value, array $parameters = []): self
	{
		// If the value is already a resolver, no need to do anything
		if ($value instanceof self) {
			return $value;
		}
		
		// If value is a closure, we'll use late static binding
		if ($value instanceof Closure) {
			return new static($value, $parameters);
		}
		
		// Otherwise, we'll instantiate a plain/base resolver instance
		return new self(fn() => $value, $parameters);
	}
	
	/**
	 * @var \Closure|string $callback
	 */
	public function __construct($callback, array $parameters)
	{
		if ($callback instanceof Closure) {
			$this->callback = static::optimizeBinding($callback);
		} elseif ($this->isSerializedClosure($callback)) {
			$this->serialized = $callback;
		} else {
			throw new InvalidArgumentException('Resolver callbacks must be a Closure or a serialized closure.');
		}
		
		$this->parameters = $parameters;
	}
	
	/**
	 * @return \Glhd\Gretel\Breadcrumb|string
	 */
	public function resolve(array $parameters, Registry $registry)
	{
		return call_user_func_array($this->getClosure(), $this->transformParameters($parameters, $registry));
	}
	
	public function getClosure(): Closure
	{
		if (null !== $this->serialized) {
			$callback = unserialize($this->serialized, ['allow_classes' => true]);
			if ($callback instanceof SerializableClosure) {
				$this->callback = $callback->getClosure();
				$this->serialized = null;
			}
		}
		
		return $this->callback;
	}
	
	public function exportForSerialization(): array
	{
		if (null === $this->serialized) {
			$callback = $this->callback;
			
			$this->callback = null;
			$this->serialized = serialize(new SerializableClosure($callback));
		}
		
		return [$this->parameters, $this->serialized];
	}
	
	protected function transformParameters(array $parameters, Registry $registry): array
	{
		return array_values($parameters);
	}
	
	protected function isSerializedClosure($value): bool
	{
		$fragment = 'C:'.strlen(SerializableClosure::class).':"'.SerializableClosure::class;
		
		return is_string($value) && Str::startsWith($value, $fragment);
	}
}
