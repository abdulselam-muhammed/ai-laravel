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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_tr')->nullable();
            $table->mediumText('summery')->nullable();
            $table->tinyInteger('is_favorite')->default(DEACTIVATE);
            $table->text('content')->nullable();
            $table->string('prompt')->nullable();
            $table->string('icon')->default('default.svg')->nullable();
            $table->tinyInteger('status')->default(ACTIVE);
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
        Schema::dropIfExists('sub_categories');
    }
};
