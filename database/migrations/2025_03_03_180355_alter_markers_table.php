<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterMarkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE markers MODIFY COLUMN lat VARCHAR(255)');
        DB::statement('ALTER TABLE markers MODIFY COLUMN `long` VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::statement('ALTER TABLE markers MODIFY COLUMN lat DOUBLE');
        DB::statement('ALTER TABLE markers MODIFY COLUMN `long` DOUBLE');
    }
}
