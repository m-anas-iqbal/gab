<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkerCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marker_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('marker_id');
            $table->integer('user_id');
            $table->text('comment');
            $table->timestamps();

            // Define foreign key constraint
           // $table->foreign('marker_id')->references('id')->on('markers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marker_comments');
    }
}
