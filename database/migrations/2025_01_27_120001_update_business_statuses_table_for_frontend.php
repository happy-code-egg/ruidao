<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateBusinessStatusesTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_statuses', function (Blueprint $table) {
            // 检查字段是否存在再删除
            if (Schema::hasColumn('business_statuses', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('business_statuses', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('business_statuses', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('business_statuses', function (Blueprint $table) {
            // 重命名字段以匹配前端
            if (Schema::hasColumn('business_statuses', 'name')) {
                $table->renameColumn('name', 'status_name');
            }
            if (Schema::hasColumn('business_statuses', 'status')) {
                $table->renameColumn('status', 'is_valid');
            }
            if (Schema::hasColumn('business_statuses', 'sort_order')) {
                $table->renameColumn('sort_order', 'sort');
            }
        });

        // 使用原生SQL进行类型转换（PostgreSQL）
        DB::statement('ALTER TABLE business_statuses ALTER COLUMN is_valid DROP DEFAULT');
        DB::statement('ALTER TABLE business_statuses ALTER COLUMN is_valid TYPE BOOLEAN USING (is_valid = 1)');
        DB::statement('ALTER TABLE business_statuses ALTER COLUMN is_valid SET DEFAULT true');
        DB::statement('ALTER TABLE business_statuses ALTER COLUMN sort SET DEFAULT 1');

        Schema::table('business_statuses', function (Blueprint $table) {
            // 添加用户跟踪字段
            if (!Schema::hasColumn('business_statuses', 'updated_by')) {
                $table->string('updated_by')->nullable()->comment('更新人');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_statuses', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('status_name', 'name');
            $table->renameColumn('is_valid', 'status');
            $table->renameColumn('sort', 'sort_order');
            
            $table->tinyInteger('status')->default(1)->change();
            $table->integer('sort_order')->default(0)->change();
            
            $table->string('code', 50)->unique()->comment('状态编码');
            $table->string('color', 20)->default('#409EFF')->comment('显示颜色');
            $table->text('description')->nullable()->comment('描述');
            
            $table->dropColumn('updated_by');
        });
    }
}
