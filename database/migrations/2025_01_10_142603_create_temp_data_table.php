<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_data', function (Blueprint $table) {
            $table->id();

            $table->string('marker_title')->nullable();
            $table->string('hazard_id')->nullable();
            $table->string('internal_id')->nullable();
            $table->string('color')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('proximity')->nullable();
            $table->string('description')->nullable();
            $table->string('group_id_1')->nullable();
            $table->string('group_id_2')->nullable();
            $table->string('group_id_3')->nullable();
            $table->string('extra')->nullable();
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
        Schema::dropIfExists('temp_data');
    }
}
