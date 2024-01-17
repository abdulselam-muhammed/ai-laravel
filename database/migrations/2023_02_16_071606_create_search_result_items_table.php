<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_result_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_result_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('output');
            $table->tinyInteger('react')->nullable()->common('like for 1, dislike for 2');
            $table->tinyInteger('is_favorite')->default(DEACTIVATE);
            $table->integer('total_word')->default(0);
            $table->integer('total_characters')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_result_items');
    }
};
