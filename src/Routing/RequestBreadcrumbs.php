<?php

/** @noinspection JsonEncodingApiUsageInspection */

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Registry;
use Glhd\Gretel\Resolvers\Resolver;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin Collection
 */
class RequestBreadcrumbs implements Arrayable, Jsonable
{
	use ForwardsCalls;
	
	protected Registry $registry;
	
	protected Route $route;
	
	protected Collection $breadcrumbs;
	
	public function __construct(Registry $registry, Route $route)
	{
		$this->registry = $registry;
		$this->route = $route;
		$this->breadcrumbs = new Collection();
		
		$this->walk($route->getName());
	}
	
	public function toCollection(): Collection
	{
		return new Collection($this->toArray());
	}
	
	public function toArray()
	{
		return $this->breadcrumbs->map(fn(RouteBreadcrumb $breadcrumb) => (object) [
			'title' => $this->resolve($breadcrumb->title, $breadcrumb),
			'url' => $this->resolve($breadcrumb->url, $breadcrumb),
		]);
	}
	
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}
	
	public function __call($name, $arguments)
	{
		return $this->forwardDecoratedCallTo($this->breadcrumbs, $name, $arguments);
	}
	
	protected function walk($value): void
	{
		if (!$breadcrumb = $this->getBreadcrumb($value)) {
			return;
		}
		
		if ($parent = $this->resolve($breadcrumb->parent, $breadcrumb)) {
			$this->walk($parent);
		}
		
		$this->breadcrumbs->push($breadcrumb);
	}
	
	protected function getBreadcrumb($breadcrumb): ?RouteBreadcrumb
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
