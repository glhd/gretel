<?php

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Breadcrumb;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;

class RouteBreadcrumb extends Breadcrumb
{
	public string $name;
	
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
}
