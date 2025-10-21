<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTechServiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tech_service_items', function (Blueprint $table) {
            // 让 tech_service_type_id 字段可以为空，因为现在主要使用 tech_service_region_id
            $table->unsignedBigInteger('tech_service_type_id')->nullable()->change();
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
            // 恢复 tech_service_type_id 字段为非空
            $table->unsignedBigInteger('tech_service_type_id')->nullable(false)->change();
        });
    }
}
