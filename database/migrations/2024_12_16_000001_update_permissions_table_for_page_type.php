<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePermissionsTableForPageType extends Migration
{
    /**
     * Run the migrations.
     * 修改权限表：添加页面类型，移除图标和状态字段
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // 移除图标字段
            $table->dropColumn('icon');
            // 移除状态字段
            $table->dropColumn('status');
        });
        
        // 修改权限类型注释，添加页面类型说明 (PostgreSQL语法)
        DB::statement("COMMENT ON COLUMN permissions.permission_type IS '权限类型：1-菜单，2-页面，3-按钮，4-接口'");
    }

    /**
     * Reverse the migrations.
     * 恢复图标和状态字段
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // 恢复图标字段
            $table->string('icon', 100)->nullable()->comment('图标')->after('resource_url');
            // 恢复状态字段
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用')->after('sort_order');
        });
        
        // 恢复权限类型注释 (PostgreSQL语法)
        DB::statement("COMMENT ON COLUMN permissions.permission_type IS '权限类型：1-菜单，2-按钮，3-数据'");
    }
}
