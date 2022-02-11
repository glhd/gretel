<?php

namespace Glhd\Gretel\View;

use Illuminate\Support\Collection;
use Traversable;

class BreadcrumbCollection extends Collection
{
	public ?Breadcrumb $active = null;
	
	public function getIterator(): Traversable
	{
		$this->active = null;
		
		return new BreadcrumbIterator($this, $this->items);
	}
}
