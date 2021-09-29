<?php

namespace Glhd\Gretel\Exceptions;

use InvalidArgumentException;

class UnresolvableParentException extends InvalidArgumentException
{
	public function __construct($value, string $route)
	{
		$message = is_string($value)
			? "Unable to find parent '{$value}' for route '{$route}'"
			: $this->invalidTypeMessage($value, $route);
		
		parent::__construct($message);
	}
	
	protected function invalidTypeMessage($value, string $route): string
	{
		$got = is_object($value)
			? get_class($value)
			: gettype($value);
		
		return "Unable to resolve parent for route '{$route}'. Expected a route name or a RouteBreadcrumb object, but got '{$got}' instead.";
	}
}
