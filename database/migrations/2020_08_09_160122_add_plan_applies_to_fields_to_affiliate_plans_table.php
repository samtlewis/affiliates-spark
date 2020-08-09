<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanAppliesToFieldsToAffiliatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_plans', function (Blueprint $table) {
            $table->boolean('apply_to_users')->default(1);
            $table->boolean('apply_to_teams')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_plans', function (Blueprint $table) {
            $table->dropColumn('apply_to_users');
            $table->dropColumn('apply_to_teams');
        });
    }
}
