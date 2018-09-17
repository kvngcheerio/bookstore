<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_reviews', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('book_id')->unsigned();
            $table->foreign('book_id')->references('id')->on('books')
                ->onDelete('cascade')->onUpdate('cascade');
                
            $table->tinyInteger('is_approved')->default(0);
            $table->string('title', 350)->nullable();
            $table->text('review_text')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('helpful_yes_total')->nullable();
            $table->integer('helpful_no_total')->nullable();
            $table->text('reply_text')->nullable();
            
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
        Schema::dropIfExists('book_reviews');
    }
}
