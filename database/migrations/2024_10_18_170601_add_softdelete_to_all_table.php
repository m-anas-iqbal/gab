<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddSoftdeleteToAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // List all your tables and add softDeletes
        $tables = [
            'users',
            'groups',
            'group_members',
            'organizations',
            'posts',
            'comments',
            'hazards',
            'likes',
            'replies',
            'markers',
            'marker_comments',
            'marker_hazards',
            'marker_link',
            'marker_note',
            'marker_todo',
            'orders',
            'order_details',
            'products',
            'subscribed_plans',
            'subscription_plans',
        ];

        // Loop through the tables and add softDeletes to each
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes(); // Add soft deletes column
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // List the same tables to remove softDeletes on rollback
        $tables = [

            'users',
            'groups',
            'group_members',
            'organizations',
            'posts',
            'comments',
            'hazards',
            'likes',
            'replies',
            'markers',
            'marker_comments',
            'marker_hazards',
            'marker_link',
            'marker_note',
            'marker_todo',
            'orders',
            'order_details',
            'products',
            'subscribed_plans',
            'subscription_plans',
        ];

        // Loop through the tables and drop the softDeletes column
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes(); // Remove soft deletes column
            });
        }
    }
}
