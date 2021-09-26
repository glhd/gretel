<?php

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Illuminate\Routing\Route;

class RouteBreadcrumb extends Breadcrumb
{
	public string $name;
	
	public ?Route $route = null;
	
	/**
	 * @param string|\Closure $title
	 * @param string|\Closure|null $parent
	 */
	public function __construct(string $name, array $parameters, $title, $parent = null)
	{
		$this->name = $name;
		$this->title = TitleResolver::make($title, $parameters);
		$this->parent = ParentResolver::make($parent, $parameters);
		$this->url = new UrlResolver($name, $parameters);
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
