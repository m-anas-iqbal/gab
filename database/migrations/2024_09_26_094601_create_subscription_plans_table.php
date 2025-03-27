<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_product_id')->nullable();
            $table->string('product_product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('plan_type')->nullable();
            $table->longText('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('status')->nullable();
            $table->string('livemode')->nullable();
            $table->string('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('interval')->nullable();
            $table->integer('interval_count')->default(0);
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
        Schema::dropIfExists('subscription_plans');
    }
}
