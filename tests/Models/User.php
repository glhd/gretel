<?php

namespace Glhd\Gretel\Tests\Models;

use Glhd\Gretel\Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $role
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Glhd\Gretel\Tests\Models\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Glhd\Gretel\Tests\Models\Comment[] $comments
 */
class User extends Model
{
	public static function factory(): UserFactory
	{
		return UserFactory::new();
	}
	
	public function notes()
	{
		return $this->hasMany(Note::class);
	}
	
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
}
