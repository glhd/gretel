<?php

namespace Glhd\Gretel\Tests\Database\Factories;

use Glhd\Gretel\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UserFactory extends Factory
{
	protected $model = User::class;
	
	public function definition(): array
	{
		return [
			'name' => $this->faker->name,
			'role' => 'user',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		];
	}
}
