<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Default View
	|--------------------------------------------------------------------------
	|
	| This is the view that Gretel will use by default when rendering
	| breadcrumbs (overridden by <x-breadcrumb /> attributes.
	|
	*/
	
	'view' => 'gretel::tailwind',
	
	/*
	|--------------------------------------------------------------------------
	| Third Party Integrations
	|--------------------------------------------------------------------------
	|
	| By default, Gretel will share the current breadcrumbs with Inertia.js
	| if Inertia is installed. You can disable that here.
	|
	*/
	
	'packages' => [
		'inertiajs/inertia-laravel' => true,
	],
	
	/*
	|--------------------------------------------------------------------------
	| Use Static Closures
	|--------------------------------------------------------------------------
	|
	| By default, Gretel will convert all closures that you pass to the
	| Route::breadcrumb() macro to static closures. This results in slightly
	| better performance when caching breadcrumbs. If, for some reason, you
	| need to use the $this context inside of your closures, you will need to
	| disable this setting (very uncommon).
	|
	*/
	
	'static_closures' => true,
];
