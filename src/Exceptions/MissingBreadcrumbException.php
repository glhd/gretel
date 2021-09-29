<?php

namespace Glhd\Gretel\Exceptions;

use RuntimeException;

class MissingBreadcrumbException extends RuntimeException
{
	public function __construct(?string $name)
	{
		$name ??= '[unnamed route]';
		
		parent::__construct("There is no breadcrumb registered for '{$name}'.");
	}
}
