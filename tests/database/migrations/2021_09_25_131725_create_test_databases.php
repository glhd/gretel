<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public function up()
	{
		Schema::create('users', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->string('role');
			$table->timestamps();
		});
		
		Schema::create('notes', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('user_id');
			$table->text('note');
			$table->timestamps();
		});
		
		Schema::create('comments', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignId('user_id');
			$table->morphs('commentable');
			$table->text('comment');
			$table->timestamps();
		});
	}
	
	public function down()
	{
		Schema::dropIfExists('users');
		Schema::dropIfExists('notes');
		Schema::dropIfExists('comments');
	}
};
