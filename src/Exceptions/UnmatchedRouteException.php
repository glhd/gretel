<?php

namespace Glhd\Gretel\Exceptions;

class UnmatchedRouteException extends \InvalidArgumentException
{
	public function __construct(string $url)
	{
		parent::__construct("Unable to find a route for '{$url}'.");
	}
}
