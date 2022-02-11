<?php

namespace Glhd\Gretel\Resolvers;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Support\BindsClosures;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Opis\Closure\SerializableClosure as OpisSerializableClosure;

class Resolver
{
	use BindsClosures;
	
	public array $parameters;
	
	protected ?Closure $callback = null;
	
	protected ?string $serialized = null;
	
	protected static function wrap($value): self
	{
		return $value instanceof self
			? $value
			: new static(static::wrapClosure($value));
	}
	
	protected static function wrapClosure($value): Closure
	{
		if ($value instanceof Closure) {
			$value = static::optimizeBinding($value);
			return static function($parameters) use ($value) {
				return $value(...array_values($parameters));
			};
		}
		
		return static function() use ($value) {
			return $value;
		};
	}
	
	protected static function wrapNullableClosure($value): ?Closure
	{
		return null === $value
			? $value
			: static::wrapClosure($value);
	}
	
	/**
	 * @var \Closure|string $callback
	 */
	public function __construct($callback)
	{
		if ($callback instanceof Closure) {
			$this->callback = static::optimizeBinding($callback);
		} elseif ($this->isSerializedClosure($callback)) {
			$this->serialized = $callback;
		} else {
			throw new InvalidArgumentException('Resolver callbacks must be a Closure or a serialized closure.');
		}
	}
	
	public function resolve(array $parameters, Registry $registry)
	{
		return call_user_func($this->getClosure(), $parameters, $registry);
	}
	
	public function getClosure(): Closure
	{
		if (null !== $this->serialized) {
			$callback = unserialize($this->serialized, ['allow_classes' => true]);
			if ($this->isSerializableClosure($callback)) {
				$this->callback = $callback->getClosure();
				$this->serialized = null;
			}
		}
		
		return $this->callback;
	}
	
	public function getSerializedClosure(): string
	{
		if (null === $this->serialized) {
			$callback = $this->callback;
			
			$this->callback = null;
			$this->serialized = serialize(new SerializableClosure($callback));
		}
		
		return $this->serialized;
	}
	
	protected function isSerializableClosure($callback): bool
	{
		if ($callback instanceof SerializableClosure) {
			return true;
		}
		
		if (class_exists(OpisSerializableClosure::class) && $callback instanceof OpisSerializableClosure) {
			return true;
		}
		
		return false;
	}
	
	protected function isSerializedClosure($value): bool
	{
		$needles = [
			'O:'.strlen(SerializableClosure::class).':"'.SerializableClosure::class,
			'C:'.strlen(OpisSerializableClosure::class).':"'.OpisSerializableClosure::class,
		];
		
		return is_string($value) && Str::startsWith($value, $needles);
	}
}
