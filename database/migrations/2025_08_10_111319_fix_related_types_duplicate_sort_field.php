<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixRelatedTypesDuplicateSortField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('related_types', function (Blueprint $table) {
            // 删除重复的sort字段，保留sort_order
            $table->dropColumn('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('related_types', function (Blueprint $table) {
            // 恢复sort字段
            $table->integer('sort')->default(1)->comment('排序')->after('id');
        });
    }
}
