<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 450);
            $table->string('short_description', 600)->nullable();
            $table->text('full_description')->nullable();
            $table->string('author_name');
            $table->integer('pages_count');
            $table->decimal('price', 18, 2)->nullable();
            $table->string('sku', 450)->unique()->nullable();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->timestamps();

            
        });


        //create pivot table for categories
        Schema::create('category_book', function (Blueprint $table) {
            $table->unsignedInteger('book_id');
            $table->unsignedInteger('category_id');

            $table->primary(['category_id', 'book_id']);
        });

        //create pivot table for pictures
        Schema::create('book_picture', function (Blueprint $table) {
            $table->unsignedInteger('book_id');
            $table->unsignedInteger('picture_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_picture');
        Schema::dropIfExists('category_book');
        Schema::dropIfExists('books');
    }
}
