<?php

namespace Glhd\Gretel\Exceptions;

class ParentParametersCannotBeInferredException extends \InvalidArgumentException
{
	public function __construct()
	{
		parent::__construct('The parent route requires parameters that do not exist in the child route. You must explicitly set the parent breadcrumb in this case.');
	}
}
