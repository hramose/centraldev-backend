<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authentication', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('login_attempt')->default(0);
            $table->boolean('email_confirmed')->default(false);
            $table->boolean('account_approved')->default(false);
            $table->text('registered_ip');
            $table->text('last_ip');
            $table->text('twofa_secretkey')->nullable();
            $table->text('oauth_provider')->nullable();
            $table->text('oauth_provider_id')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('authentication');
    }
}
