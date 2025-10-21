<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateRelatedTypesTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('related_types', function (Blueprint $table) {
            // 删除不需要的字段
            $table->dropColumn(['category']);

            // 重命名字段以匹配前端
            $table->renameColumn('name', 'type_name');
            $table->renameColumn('code', 'type_code');
            $table->renameColumn('status', 'is_valid');

            // 添加新字段
            $table->integer('sort')->default(1)->comment('排序')->after('id');
            $table->string('case_type', 100)->nullable()->comment('项目类型')->after('sort');
            $table->string('updater', 100)->nullable()->comment('更新人')->after('sort_order');
        });

        // 填充case_type字段的默认值
        DB::statement("UPDATE related_types SET case_type = '发明专利' WHERE case_type IS NULL");

        // 使用原生SQL修改字段类型
        DB::statement('ALTER TABLE related_types ALTER COLUMN is_valid DROP DEFAULT');
        DB::statement('ALTER TABLE related_types ALTER COLUMN is_valid TYPE BOOLEAN USING (is_valid = 1)');
        DB::statement('ALTER TABLE related_types ALTER COLUMN is_valid SET DEFAULT true');

        // 设置case_type为不可为空
        DB::statement('ALTER TABLE related_types ALTER COLUMN case_type SET NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('related_types', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('type_name', 'name');
            $table->renameColumn('type_code', 'code');
            $table->renameColumn('is_valid', 'status');
            
            $table->tinyInteger('status')->default(1)->change();
            
            $table->string('category', 50)->comment('分类');
            
            $table->dropColumn(['sort', 'case_type', 'updater']);
        });
    }
}
