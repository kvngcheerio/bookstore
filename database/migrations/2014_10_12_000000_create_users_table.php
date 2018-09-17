<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'users', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'username' )->unique();
			$table->string( 'email' )->unique();
			$table->string( 'password' );
			$table->string( 'first_name' );
			$table->string( 'last_name' );
            $table->string( 'middle_name' )->nullable()->default('');
			$table->string( 'phone' );
			$table->tinyInteger( 'is_active' )->default( 0 );
			$table->tinyInteger( 'is_online' )->default( 0 );
			$table->tinyInteger( 'is_reader' )->default( 0 );

			//            added this column to existing columns
			$table->unsignedInteger( 'picture_id' )->default(2);
			$table->unsignedInteger( 'created_by' )->default( 0 );

			$table->timestamps();

		} );

		//a small hack to start users at 
		$startId = config('apiauth.start_user_id') - 1;
		DB::table('users')->insert([
			'id'=> $startId,
			'username' => 'test',
			'email' => 'test',
			'password' => 'test',
			'first_name' => 'test',
			'last_name' => 'test',
			'phone' => 'test',
			]);
		DB::table('users')->where('id', $startId)->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
	
		Schema::dropIfExists( 'users' );
	}
}
