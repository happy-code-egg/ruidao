<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFileCategoriesTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_categories', function (Blueprint $table) {
            // 删除不需要的字段
            $table->dropColumn(['code', 'parent_id', 'level', 'description']);

            // 重命名字段以匹配前端
            $table->renameColumn('name', 'main_category');
            $table->renameColumn('status', 'is_valid');
            $table->renameColumn('sort_order', 'sort');

            // 添加子分类字段
            $table->string('sub_category', 100)->default('默认小类')->comment('文件小类名称');


            // 添加用户跟踪字段
            $table->integer('created_by')->default(1)->comment('创建人');
            $table->integer('updated_by')->default(1)->comment('更新人');
        });

        // 在单独的操作中修改字段类型（PostgreSQL需要显式转换）
        DB::statement('ALTER TABLE file_categories ALTER COLUMN is_valid DROP DEFAULT');
        DB::statement('ALTER TABLE file_categories ALTER COLUMN is_valid TYPE BOOLEAN USING (is_valid = 1)');
        DB::statement('ALTER TABLE file_categories ALTER COLUMN is_valid SET DEFAULT true');
        DB::statement('ALTER TABLE file_categories ALTER COLUMN sort SET DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_categories', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('main_category', 'name');
            $table->renameColumn('is_valid', 'status');
            $table->renameColumn('sort', 'sort_order');
            
            $table->tinyInteger('status')->default(1)->change();
            $table->integer('sort_order')->default(0)->change();
            
            $table->string('code', 50)->unique()->comment('文件分类编码');
            $table->bigInteger('parent_id')->default(0)->comment('父分类ID');
            $table->tinyInteger('level')->default(1)->comment('层级');
            $table->text('description')->nullable()->comment('描述');
            
            $table->dropColumn(['sub_category', 'updated_by']);
        });
    }
}
