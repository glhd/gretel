<?php

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Resolvers\Resolver;
use Illuminate\Routing\Route;

class RouteBreadcrumb extends Breadcrumb
{
	public string $name;
	
	public ?Route $route = null;
	
	public function __construct(
		string $name,
		Resolver $title,
		Resolver $parent,
		Resolver $url
	) {
		$this->name = $name;
		$this->title = $title;
		$this->parent = $parent;
		$this->url = $url;
	}
	
	public function setRoute(Route $route): self
	{
		$this->route = $route;
		
		return $this;
	}
	
	public function __sleep()
	{
		return ['name', 'title', 'parent', 'url'];
	}
}
