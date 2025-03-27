<?php

// database/migrations/xxxx_xx_xx_add_name_sp_to_hazards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameSpToHazardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hazards', function (Blueprint $table) {
            $table->text('name_sp')->collation('utf8mb4_unicode_ci')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hazards', function (Blueprint $table) {
            $table->dropColumn('name_sp');
        });
    }
}

