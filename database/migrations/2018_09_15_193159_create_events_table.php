<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('event');
            $table->integer('event_enum_id')->unsigned();
            $table->text('information')->nullable();
            $table->text('thread')->nullable();
            $table->timestamps();

            $table->foreign('event_enum_id')
                ->references('id')
                ->on('event_enums');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_enum_id']);
        });
        Schema::dropIfExists('events');
    }
}
