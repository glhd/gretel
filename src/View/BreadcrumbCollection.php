<?php

namespace Glhd\Gretel\View;

use Illuminate\Support\Collection;

class BreadcrumbCollection extends Collection
{
	public ?Breadcrumb $active = null;
	
	public function getIterator()
	{
		$this->active = null;
		
		return new BreadcrumbIterator($this, $this->items);
	}
}
