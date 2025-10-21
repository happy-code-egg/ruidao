<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixWorkflowsTableStructure extends Migration
{
    /**
     * Run the migrations.
     * 修复workflows表结构 - 添加缺失字段并重命名现有字段
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            // 重命名 is_valid 为 status
            $table->renameColumn('is_valid', 'status');
            
            // 添加nodes字段（JSON格式）
            $table->json('nodes')->nullable()->comment('工作流节点配置');
            
            // 添加软删除字段
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            // 回滚操作：重命名status回is_valid
            $table->renameColumn('status', 'is_valid');
            
            // 删除添加的字段
            $table->dropColumn('nodes');
            $table->dropSoftDeletes();
        });
    }
}
