<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateOpportunityTypesTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opportunity_types', function (Blueprint $table) {
            // 删除不需要的字段
            $table->dropColumn(['code', 'description']);

            // 重命名字段以匹配前端（注意：前端使用statusName而不是typeName）
            $table->renameColumn('name', 'status_name');
            $table->renameColumn('status', 'is_valid');
            $table->renameColumn('sort_order', 'sort');

            // 添加用户跟踪字段
            $table->string('updated_by')->nullable()->comment('更新人');
        });

        // 在单独的操作中修改字段类型（PostgreSQL需要显式转换）
        DB::statement('ALTER TABLE opportunity_types ALTER COLUMN is_valid DROP DEFAULT');
        DB::statement('ALTER TABLE opportunity_types ALTER COLUMN is_valid TYPE BOOLEAN USING (is_valid = 1)');
        DB::statement('ALTER TABLE opportunity_types ALTER COLUMN is_valid SET DEFAULT true');
        DB::statement('ALTER TABLE opportunity_types ALTER COLUMN sort SET DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opportunity_types', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('status_name', 'name');
            $table->renameColumn('is_valid', 'status');
            $table->renameColumn('sort', 'sort_order');
            
            $table->tinyInteger('status')->default(1)->change();
            $table->integer('sort_order')->default(0)->change();
            
            $table->string('code', 50)->unique()->comment('类型编码');
            $table->text('description')->nullable()->comment('描述');
            
            $table->dropColumn('updated_by');
        });
    }
}
