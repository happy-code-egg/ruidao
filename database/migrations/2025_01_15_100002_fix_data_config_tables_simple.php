<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixDataConfigTablesSimple extends Migration
{
    /**
     * Run the migrations.
     * 修复数据配置表字段问题（简化版）
     * @return void
     */
    public function up()
    {

        // 修复 process_informations 表
        Schema::table('process_informations', function (Blueprint $table) {
            // 添加 sort 字段（前端期望）
            if (!Schema::hasColumn('process_informations', 'sort')) {
                $table->integer('sort')->default(1)->comment('排序')->after('id');
            }
            // 添加 consultant_contract 字段（前端需要）
            if (!Schema::hasColumn('process_informations', 'consultant_contract')) {
                $table->string('consultant_contract')->nullable()->comment('顾问合同')->after('process_type');
            }
            // 添加 created_by 字段（如果不存在）
            if (!Schema::hasColumn('process_informations', 'created_by')) {
                $table->string('created_by')->nullable()->comment('创建人')->after('updated_by');
            }
        });
        
        // 从 sort_order 字段复制数据到 sort 字段
        try {
            DB::statement('UPDATE process_informations SET sort = COALESCE(sort_order, 1) WHERE sort IS NULL OR sort = 0 OR sort = 1');
        } catch (Exception $e) {
            // 忽略错误，可能是字段不存在
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('process_informations', function (Blueprint $table) {
            if (Schema::hasColumn('process_informations', 'sort')) {
                $table->dropColumn('sort');
            }
            if (Schema::hasColumn('process_informations', 'consultant_contract')) {
                $table->dropColumn('consultant_contract');
            }
            if (Schema::hasColumn('process_informations', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
}
