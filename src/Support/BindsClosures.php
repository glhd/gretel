<?php

namespace Glhd\Gretel\Support;

use Closure;

trait BindsClosures
{
	protected static function optimizeBinding(?Closure $closure): ?Closure
	{
		return null !== $closure && config('gretel.static_closures')
			? $closure->bindTo(null)
			: $closure;
	}
}
