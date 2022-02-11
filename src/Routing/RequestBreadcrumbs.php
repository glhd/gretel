<?php

/** @noinspection JsonEncodingApiUsageInspection */

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Resolvers\Resolver;
use Glhd\Gretel\View\Breadcrumb;
use Glhd\Gretel\View\BreadcrumbCollection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;

/**
 * @mixin Collection
 */
class RequestBreadcrumbs implements JsonSerializable, Arrayable, Jsonable
{
	use ForwardsCalls;
	
	protected Registry $registry;
	
	protected Route $route;
	
	protected BreadcrumbCollection $breadcrumbs;
	
	public function __construct(Registry $registry, Route $route)
	{
		$this->registry = $registry;
		$this->route = $route;
		$this->breadcrumbs = new BreadcrumbCollection();
	}
	
	public function toCollection(): BreadcrumbCollection
	{
		if ($this->breadcrumbs->isEmpty()) {
			$this->walk($this->route->getName());
		}
		
		return $this->breadcrumbs;
	}
	
	public function throwIfMissing(): void
	{
		if ($this->toCollection()->isEmpty()) {
			throw new MissingBreadcrumbException($this->route->getName());
		}
	}
	
	public function toArray(): array
	{
		return $this->toCollection()->toArray();
	}
	
	public function jsonSerialize(): array
	{
		return $this->toCollection()
			->map(fn(Breadcrumb $breadcrumb) => $breadcrumb->jsonSerialize())
			->all();
	}
	
	public function toJson($options = JSON_THROW_ON_ERROR)
	{
		return json_encode($this->jsonSerialize(), $options);
	}
	
	public function __call($name, $arguments)
	{
		return $this->forwardDecoratedCallTo($this->toCollection(), $name, $arguments);
	}
	
	protected function walk($value, $depth = 0): ?Breadcrumb
	{
		if (! $breadcrumb = $this->getRouteBreadcrumb($value)) {
			return null;
		}
		
		if ($parent = $this->resolve($breadcrumb->parent, $breadcrumb)) {
			$parent = $this->walk($parent, $depth + 1);
		}
		
		$breadcrumb = new Breadcrumb(
			$this->resolve($breadcrumb->title, $breadcrumb),
			$this->resolve($breadcrumb->url, $breadcrumb),
			0 === $depth,
			$parent
		);
		
		$this->breadcrumbs->push($breadcrumb);
		
		return $breadcrumb;
	}
	
	protected function getRouteBreadcrumb($breadcrumb): ?RouteBreadcrumb
	{
		if ($breadcrumb instanceof RouteBreadcrumb) {
			return $breadcrumb;
		}
		
		return $this->registry->get($breadcrumb);
	}
	
	protected function resolve($value, RouteBreadcrumb $breadcrumb)
	{
		if ($value instanceof Resolver) {
			$parameters = $breadcrumb->parameters ?? $this->route->parameters();
			return $value->resolve($parameters, $this->registry);
		}
		
		return $value;
	}
}
