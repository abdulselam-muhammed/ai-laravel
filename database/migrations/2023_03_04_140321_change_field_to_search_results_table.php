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
        Schema::table('search_results', function (Blueprint $table) {
            $table->text('prompt')->change();
        });
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->text('prompt')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_results', function (Blueprint $table) {
            //
        });
    }
};
