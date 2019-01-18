<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevelopersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('dob');
            $table->string('phone');
            $table->integer('address_id')->unsigned();
            $table->string('status');
            $table->string('knowledge_level');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('authentication');
        });

        Schema::create('developers_freelance', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('status');
            $table->string('since');
            $table->string('customer_amount');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('developers');
        });

        Schema::create('developers_availability', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->boolean('is_available')->default(false);
            $table->enum('time_per_weeks', [0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5, 6.0, 6.5, 7.0]);
            $table->string('place');
            $table->string('city');
            $table->integer('perimeter');

            $table->foreign('user_id')->references('user_id')->on('developers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('developers');
        Schema::dropIfExists('developers_freelance');
        Schema::dropIfExists('developers_availability');
    }
}
