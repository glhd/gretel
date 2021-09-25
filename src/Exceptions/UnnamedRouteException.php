<?php

namespace Glhd\Gretel\Exceptions;

class UnnamedRouteException extends \InvalidArgumentException
{
	public function __construct()
	{
		parent::__construct('You cannot define a breadcrumb on a route that has not been named.');
	}
}
