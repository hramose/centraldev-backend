<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @var type 
     * @return verify
     * @return password
     */
    public function up()
    {
        Schema::create('send_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('email');
            $table->text('code');
            $table->string('type');
            $table->boolean('verified')->default(false);
            $table->text('expire_at');
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
        Schema::dropIfExists('send_emails');
    }
}
