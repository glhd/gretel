<?php

use Glhd\Gretel\Routing\Breadcrumbs;

// @codeCoverageIgnore

if (!function_exists('breadcrumbs')) {
	function breadcrumbs(): Breadcrumbs
	{
		return app(Breadcrumbs::class);
	}
}
