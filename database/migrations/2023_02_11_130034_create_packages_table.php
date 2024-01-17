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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->integer('generate_characters')->default(0);
            $table->text('access_use_cases')->nullable();
            $table->integer('write_languages')->default(0);
            $table->integer('access_tones')->default(0);
            $table->integer('generate_images')->default(0);
            $table->string('plagiarism_checker')->nullable();
            $table->string('access_community')->nullable();
            $table->string('custom_use_cases')->nullable();
            $table->string('dedicated_account')->nullable();
            $table->string('support')->nullable();
            $table->decimal('monthly_price', 8, 2)->default(0);
            $table->decimal('yearly_price', 8, 2)->default(0);
            $table->integer('device_limit')->default(1);
            $table->tinyInteger('status')->default(DEACTIVATE)->comment('active for 1 , deactivate for 0');
            $table->tinyInteger('is_default')->default(DEACTIVATE)->comment('default for 1 , not default for 0');
            $table->tinyInteger('is_trail')->default(DEACTIVATE)->comment('default for 1 , not default for 0');
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
        Schema::dropIfExists('packages');
    }
};
