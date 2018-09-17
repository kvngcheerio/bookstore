<?php

use Illuminate\Database\Seeder;

class EventEnumsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {

		DB::table( 'event_enums' )->delete();
		DB::table( 'event_enums' )->insert( array(
				array(
					'id'   => '1',
					'name' => 'debug'
				),
				array(
					'id'   => '2',
					'name' => 'info'
				),
				array(
					'id'   => '3',
					'name' => 'notice'
				),
				array(
					'id'   => '4',
					'name' => 'warning'
				),
				array(
					'id'   => '5',
					'name' => 'error'
				),
				array(
					'id'   => '6',
					'name' => 'critical'
				),
				array(
					'id'   => '7',
					'name' => 'alert'
				),
				array(
					'id'   => '8',
					'name' => 'emergency'
				),
			)
		);
	}
}
