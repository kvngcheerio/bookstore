<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageTemplateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'message_templates', function ($table) {
                $table->increments('id');
                $table->string('slug');
                $table->string('description');
                $table->string('subject');
                $table->text('template');
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('updated_by')->nullable()->index();
                $table->timestamps();
            }
        );

        Schema::create(
            'variables', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('description');
            }
        );

        Schema::create('message_template_variable', function ($table) {
            $table->integer('message_template_id')->unsigned();
            $table->integer('variable_id')->unsigned();

            $table->foreign('message_template_id')
                ->references('id')
                ->on('message_templates')
                ->onDelete('cascade');

            $table->foreign('variable_id')
                ->references('id')
                ->on('variables')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('message_template_variable');
        Schema::drop('message_templates');
        Schema::drop('variables');
    }
}
