<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrademarkImageToCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 为cases表添加trademark_image字段
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            // 添加商标图片字段
            if (!Schema::hasColumn('cases', 'trademark_image')) {
                $table->text('trademark_image')->nullable()->after('trademark_category')->comment('商标图片URL');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'trademark_image')) {
                $table->dropColumn('trademark_image');
            }
        });
    }
}
