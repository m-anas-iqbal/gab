<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStatusInTempDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_data', function (Blueprint $table) {
            $table->string('group_name_1')->nullable();
            $table->string('group_name_2')->nullable();
            $table->string('group_name_3')->nullable();
            $table->string('hazard_name')->nullable();
            $table->string('organization_id')->nullable();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_data', function (Blueprint $table) {
            $table->dropColumn('group_name_1');
            $table->dropColumn('group_name_2');
            $table->dropColumn('group_name_3');
            $table->dropColumn('hazard_name');
            $table->dropColumn('organization_id');
            $table->dropColumn('status');
        });
    }
}
