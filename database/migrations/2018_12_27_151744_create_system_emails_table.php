<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->text('code');
            $table->enum('type', ['verify-email', 'reset-pwd', 'other']);
            $table->boolean('verified')->default(false);
            $table->text('expire_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('authentication');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_emails');
    }
}
