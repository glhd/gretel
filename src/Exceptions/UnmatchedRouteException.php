<?php

namespace Glhd\Gretel\Exceptions;

use InvalidArgumentException;

class UnmatchedRouteException extends InvalidArgumentException
{
	public function __construct(string $url)
	{
		parent::__construct("Unable to find a route for '{$url}'.");
	}
}
