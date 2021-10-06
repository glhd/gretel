<?php

namespace Glhd\Gretel\Tests\Mocks;

class Inertia
{
	public static array $shared = [];
	
	public static function share($name, $value)
	{
		static::$shared[$name] = $value;
	}
}

class_alias(Inertia::class, 'Inertia\\Inertia');
