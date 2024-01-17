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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('google_id')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('register_af_url')->nullable();
            $table->integer('affiliate_status')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('url')->nullable();
            $table->integer('team_role')->nullable();
            $table->integer('team_status')->nullable();
            $table->string('code')->nullable();
            $table->integer('register_status')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            $table->string('country');
            $table->string('address');
            $table->string('city');
            $table->string('postcode');
            $table->string('about');
            $table->string('profile_image');
            $table->string('avatar');

            $table->string('contact_number', 20)->nullable()->unique();
            $table->tinyInteger('status')->default(ACTIVE)->comment('Active = 1, Deactivate = 0');
            $table->bigInteger('created_by')->nullable();
            $table->tinyInteger('role')->default(USER_ROLE_USER);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
