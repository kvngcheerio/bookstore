<?php

use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create(
            'settings', function ($table) {
                $table->increments('id');
                $table->string('key')->index()->unique();
                $table->string('value');
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('updated_by')->nullable()->index();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::drop('settings');
    }
}
