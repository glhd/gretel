<?php

namespace Glhd\Gretel\Tests\Database\Factories;

use Glhd\Gretel\Tests\Models\Note;
use Glhd\Gretel\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NoteFactory extends Factory
{
	protected $model = Note::class;
	
	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'note' => $this->faker->word,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		];
	}
}
