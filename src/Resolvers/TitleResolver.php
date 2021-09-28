<?php

namespace Glhd\Gretel\Resolvers;

class TitleResolver extends Resolver
{
	public static function make($value): Resolver
	{
		return static::wrap($value);
	}
}
