<?php

namespace Glhd\Gretel\Support;

use Closure;

trait BindsClosures
{
	protected static function optimizeBinding(Closure $closure): Closure
	{
		return config('gretel.static_closures')
			? $closure->bindTo(null)
			: $closure;
	}
}
