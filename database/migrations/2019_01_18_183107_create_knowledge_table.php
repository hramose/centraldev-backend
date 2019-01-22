<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKnowledgeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledges', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('developers_knowledges', function (Blueprint $table) {
            $table->integer('developers_id')->unsigned()->unique();
            $table->integer('knowledge_id')->unsigned();

            $table->foreign('developers_id')->references('user_id')->on('developers');
            $table->foreign('knowledge_id')->references('id')->on('knowledges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('knowledges');
    }
}
