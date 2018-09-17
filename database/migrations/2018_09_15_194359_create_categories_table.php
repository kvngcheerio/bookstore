<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name", 250);
            $table->text("description")->nullable();
            $table->string("meta_keyword", 450)->nullable();
            $table->text("meta_description")->nullable();
            $table->unsignedInteger("parent_id")->nullable();
            $table->unsignedInteger("picture_id")->nullable();
            $table->tinyInteger("is_published")->default(1);
            $table->tinyInteger("is_deleted")->default(0);
            $table->unsignedInteger("display_order")->default(1);
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
