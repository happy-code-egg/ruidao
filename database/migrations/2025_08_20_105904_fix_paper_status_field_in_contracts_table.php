<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixPaperStatusFieldInContractsTable extends Migration
{
    /**
     * Run the migrations.
     * 修复paper_status字段的数据类型，从boolean改为string
     * @return void
     */
    public function up()
    {
        // 先添加一个临时字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('paper_status_temp', 10)->nullable();
        });

        // 将boolean值转换为字符串并存储到临时字段
        DB::statement("UPDATE contracts SET paper_status_temp = CASE WHEN paper_status = true THEN '是' ELSE '否' END");

        // 删除原字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('paper_status');
        });

        // 重命名临时字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('paper_status_temp', 'paper_status');
        });

        // 设置默认值
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('paper_status', 10)->default('否')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 添加临时boolean字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('paper_status_temp')->default(false);
        });

        // 转换数据
        DB::statement("UPDATE contracts SET paper_status_temp = CASE WHEN paper_status = '是' THEN true ELSE false END");

        // 删除字符串字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('paper_status');
        });

        // 重命名字段
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('paper_status_temp', 'paper_status');
        });
    }
}
