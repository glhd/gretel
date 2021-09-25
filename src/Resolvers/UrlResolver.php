<?php

namespace Glhd\Gretel\Resolvers;

use Exception;
use Glhd\Gretel\Exceptions\ParentParametersCannotBeInferredException;
use Glhd\Gretel\Registry;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

class UrlResolver extends Resolver
{
	protected string $name;
	
	public function __construct(string $name, array $parameters)
	{
		$this->name = $name;
		
		parent::__construct(fn(Route $route) => $this->generateUrl($route), $parameters);
	}
	
	protected function generateUrl(Route $route): string
	{
		try {
			return route($this->name, Arr::only($route->parameters(), $this->parameters));
		} catch (UrlGenerationException $exception) {
			throw $this->exception($exception, $route);
		}
	}
	
	protected function exception(UrlGenerationException $exception, Route $route): Exception
	{
		// If the active route somehow doesn't have the parameters it needs, just
		// re-throw the exception (this would only happen if the Breadcrumbs are requested
		// outside of a typical Laravel request lifecycle).
		if ($this->name === $route->getName()) {
			return $exception;
		}
		
		// Otherwise, we'll throw an exception explaining that the parent route parameters
		// cannot be inferred from the child route.
		return new ParentParametersCannotBeInferredException();
	}
	
	protected function resolveParameters(Route $route, Registry $registry): array
	{
		return [$route];
	}
}
