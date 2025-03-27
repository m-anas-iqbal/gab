<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_type');
            $table->text('address')->nullable();
            $table->text('contact_phone')->nullable();
            $table->text('contact_email')->nullable();
            $table->text('state')->nullable();
            $table->text('city')->nullable();
            $table->text('country')->nullable();
            $table->text('zipcode')->nullable();
            $table->text('name')->nullable();
            $table->text('website')->nullable();
            $table->integer('admin_id')->nullable();
            $table->integer('status')->default(0);
            $table->date('expires_at')->nullable();
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
        Schema::dropIfExists('organizations');
    }
}
