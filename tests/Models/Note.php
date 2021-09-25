<?php

namespace Glhd\Gretel\Tests\Models;

use Glhd\Gretel\Tests\Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Glhd\Gretel\Tests\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\Glhd\Gretel\Tests\Models\Comment[] $comments
 */
class Note extends Model
{
	public static function factory(): NoteFactory
	{
		return NoteFactory::new();
	}
	
	public function author()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function comments()
	{
		return $this->morphToMany(Comment::class, 'commentable');
	}
}
