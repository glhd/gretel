<?php

namespace Glhd\Gretel\Tests\Models;

use Glhd\Gretel\Tests\Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $commentable_id
 * @property string $commentable_type
 * @property string $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Glhd\Gretel\Tests\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\Glhd\Gretel\Tests\Models\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Glhd\Gretel\Tests\Models\User[] $users
 */
class Comment extends Model
{
	public static function factory(): CommentFactory
	{
		return CommentFactory::new();
	}
	
	public function author()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	
	public function notes()
	{
		return $this->morphedByMany(Note::class, 'commentable');
	}
	
	public function users()
	{
		return $this->morphedByMany(User::class, 'commentable');
	}
}
