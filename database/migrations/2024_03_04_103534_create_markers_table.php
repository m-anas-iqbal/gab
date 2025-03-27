<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->double('lat');
            $table->double('long');
            $table->boolean('flag_lat')->default(true);
            $table->boolean('flag_long')->default(true);
            $table->string('color');
            $table->string('status')->default(1);
            $table->string('icon')->nullable(); // New column for the icon
            $table->timestamps();

            // Define foreign key constraint
        //    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('markers');
    }
}
