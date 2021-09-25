<?php

namespace Glhd\Gretel\Tests\Database\Factories;

use Glhd\Gretel\Tests\Models\Comment;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CommentFactory extends Factory
{
	protected $model = Comment::class;
	
	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'commentable_id' => $this->faker->randomNumber(),
			'commentable_type' => $this->faker->word,
			'comment' => $this->faker->word,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		];
	}
}
