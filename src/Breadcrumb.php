<?php

namespace Glhd\Gretel;

use Glhd\Gretel\Resolvers\Resolver;

class Breadcrumb
{
	public Resolver $title;
	
	public ?Resolver $parent = null;
	
	public Resolver $url;
	
	public function setTitle(string $title): self
	{
		$this->title = Resolver::make($title);
		
		return $this;
	}
	
	public function setUrl(string $url): self
	{
		$this->url = Resolver::make($url);
		
		return $this;
	}
	
	public function setParent(string $title, string $url): self
	{
		$parent = new self();
		$parent->setTitle($title);
		$parent->setUrl($url);
		
		$this->parent = Resolver::make($parent);
		
		return $this;
	}
	
	public function __invoke(string $title, string $url): Breadcrumb
	{
		return $this->setTitle($title)->setUrl($url);
	}
}
