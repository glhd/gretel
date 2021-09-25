<?php

namespace Glhd\Gretel\Exceptions;

class MissingBreadcrumbException extends \RuntimeException
{
	public function __construct(string $name)
	{
		parent::__construct("There is no breadcrumb registered for '{$name}'.");
	}
}
