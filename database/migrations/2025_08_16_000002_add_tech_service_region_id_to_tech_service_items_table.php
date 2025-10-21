<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTechServiceRegionIdToTechServiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tech_service_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tech_service_region_id')->nullable()->after('tech_service_type_id')->comment('科技服务地区ID');
            $table->index(['tech_service_region_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tech_service_items', function (Blueprint $table) {
            $table->dropIndex(['tech_service_region_id']);
            $table->dropColumn('tech_service_region_id');
        });
    }
}
