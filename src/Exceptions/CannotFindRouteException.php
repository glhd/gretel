<?php

namespace Glhd\Gretel\Exceptions;

use InvalidArgumentException;

class CannotFindRouteException extends InvalidArgumentException
{
	public function __construct(string $name)
	{
		parent::__construct("Unable to find a route named '{$name}'.");
	}
}
