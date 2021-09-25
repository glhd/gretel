<?php

/** @noinspection JsonEncodingApiUsageInspection */

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Breadcrumb;
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
class Breadcrumbs implements Arrayable, Jsonable
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
	
	public function toArray()
	{
		return $this->breadcrumbs->map(fn(Breadcrumb $breadcrumb) => [
			'title' => $this->resolve($breadcrumb->title),
			'url' => $this->resolve($breadcrumb->url),
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
		$breadcrumb = $this->getBreadcrumb($value);
		
		if ($parent = $this->resolve($breadcrumb->parent)) {
			$this->walk($parent);
		}
		
		$this->breadcrumbs->push($breadcrumb);
	}
	
	protected function getBreadcrumb($breadcrumb): Breadcrumb
	{
		if ($breadcrumb instanceof Breadcrumb) {
			return $breadcrumb;
		}
		
		return $this->registry->get($breadcrumb);
	}
	
	protected function resolve($value)
	{
		if ($value instanceof Resolver) {
			return $value->resolve($this->route, $this->registry);
		}
		
		return $value;
	}
}
