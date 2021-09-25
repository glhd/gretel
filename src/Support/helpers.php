<?php

use Glhd\Gretel\Routing\Breadcrumbs;
use Illuminate\Routing\Route;

// @codeCoverageIgnore

if (!function_exists('breadcrumbs')) {
	function breadcrumbs(): Breadcrumbs
	{
		return app(Route::class)->breadcrumbs();
	}
}
