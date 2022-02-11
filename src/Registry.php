<?php

namespace Glhd\Gretel;

use Closure;
use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Throwable;

/**
 * @mixin Collection
 */
class Registry
{
	use ForwardsCalls;
	
	protected const HANDLER_MISSING = 'missing';
	
	protected const HANDLER_MISCONFIGURED = 'misconfigured';
	
	protected Collection $breadcrumbs;
	
	protected Collection $exception_handlers;
	
	public function __construct()
	{
		$this->breadcrumbs = new Collection();
		$this->exception_handlers = new Collection();
		
		// Default to not throwing on missing breadcrumbs, only mis-configured ones
		$this->throwOnMissingBreadcrumbs(false);
	}
	
	public function withExceptionHandling(Closure $callback)
	{
		try {
			return $callback($this);
		} catch (MissingBreadcrumbException $exception) {
			$this->callHandler(static::HANDLER_MISSING, $exception);
		} catch (Throwable $exception) {
			$this->callHandler(static::HANDLER_MISCONFIGURED, $exception);
		}
		
		return null;
	}
	
	public function handleMissingBreadcrumbs(Closure $callback): self
	{
		$this->exception_handlers->put(static::HANDLER_MISSING, $callback);
		
		return $this;
	}
	
	public function throwOnMissingBreadcrumbs(bool $throw = true): self
	{
		if (! $throw) {
			return $this->handleMissingBreadcrumbs(static function() {
				// Ignore exception
			});
		}
		
		return $this->handleMissingBreadcrumbs(static function(Throwable $throwable) {
			throw $throwable;
		});
	}
	
	public function handleMisconfiguredBreadcrumbs(Closure $callback): self
	{
		$this->exception_handlers->put(static::HANDLER_MISCONFIGURED, $callback);
		
		return $this;
	}
	
	public function throwOnMisconfiguredBreadcrumbs(bool $throw = true): self
	{
		if (! $throw) {
			return $this->handleMisconfiguredBreadcrumbs(static function() {
				// Ignore exception
			});
		}
		
		return $this->handleMisconfiguredBreadcrumbs(static function(Throwable $throwable) {
			throw $throwable;
		});
	}
	
	public function clear(): Registry
	{
		$this->breadcrumbs = new Collection();
		
		return $this;
	}
	
	public function register(RouteBreadcrumb ...$breadcrumbs): Registry
	{
		foreach ($breadcrumbs as $breadcrumb) {
			$this->breadcrumbs->put($breadcrumb->name, $breadcrumb);
		}
		
		return $this;
	}
	
	public function get($route): ?RouteBreadcrumb
	{
		if (! $name = $this->resolveName($route)) {
			return null;
		}
		
		return $this->breadcrumbs->get($name);
	}
	
	public function getOrFail($route): RouteBreadcrumb
	{
		if ($breadcrumb = $this->get($route)) {
			return $breadcrumb;
		}
		
		throw new MissingBreadcrumbException($this->resolveName($route));
	}
	
	public function __call($name, $arguments)
	{
		return $this->forwardDecoratedCallTo($this->breadcrumbs, $name, $arguments);
	}
	
	protected function resolveName($route): ?string
	{
		return $route instanceof Route
			? $route->getName()
			: (string) $route;
	}
	
	/**
	 * Added for better backwards-compatibility. Can be removed when Laravel 9 comes out.
	 */
	protected function forwardDecoratedCallTo($object, $method, $parameters)
	{
		$result = $this->forwardCallTo($object, $method, $parameters);
		
		if ($result === $object) {
			return $this;
		}
		
		return $result;
	}
	
	protected function callHandler(string $kind, Throwable $exception): void
	{
		$handler = $this->exception_handlers->get($kind);
		
		if (! $handler instanceof Closure) {
			throw $exception;
		}
		
		$handler($exception);
	}
}
