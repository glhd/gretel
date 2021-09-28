<?php

namespace Glhd\Gretel\Routing;

use Glhd\Gretel\Resolvers\Resolver;

class RouteBreadcrumb
{
	public string $name;
	
	public Resolver $title;
	
	public ?Resolver $parent = null;
	
	public Resolver $url;
	
	public ?array $parameters = null;
	
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
	
	public function setParameters(array $parameters): self
	{
		$this->parameters = $parameters;
		
		return $this;
	}
	
	public function __sleep()
	{
		return ['name', 'title', 'parent', 'url'];
	}
}
