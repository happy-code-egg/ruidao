<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFileDescriptionsTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_descriptions', function (Blueprint $table) {
            // 删除不需要的字段
            $table->dropColumn(['name', 'code', 'category_id', 'description']);
            
            // 添加前端需要的所有字段
            $table->string('case_type', 100)->comment('项目类型');
            $table->string('country', 100)->comment('国家（地区）');
            $table->string('file_category_major', 100)->comment('文件大类');
            $table->string('file_category_minor', 100)->comment('文件小类');
            $table->string('file_name', 200)->comment('文件名称');
            $table->string('file_name_en', 200)->nullable()->comment('文件名称（英）');
            $table->string('file_code', 50)->comment('文件编号');
            $table->string('internal_code', 50)->nullable()->comment('内部代码');
            $table->text('file_description')->nullable()->comment('文件描述');
            $table->integer('authorized_client')->nullable()->comment('授权客户');
            $table->text('authorized_role')->nullable()->comment('授权角色');
            
            // 重命名字段以匹配前端
            $table->renameColumn('status', 'is_valid');
            
            // 添加用户跟踪字段
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->integer('created_by')->nullable()->comment('创建人');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_descriptions', function (Blueprint $table) {
            // 恢复原始结构
            $table->renameColumn('is_valid', 'status');
            
            $table->tinyInteger('status')->default(1)->change();
            
            $table->string('name', 100)->comment('文件描述名称');
            $table->string('code', 50)->unique()->comment('文件描述编码');
            $table->bigInteger('category_id')->comment('分类ID');
            $table->text('description')->nullable()->comment('描述');
            
            $table->dropColumn([
                'case_type', 'country', 'file_category_major', 'file_category_minor',
                'file_name', 'file_name_en', 'file_code', 'internal_code',
                'file_description', 'authorized_client', 'authorized_role', 'updated_by', 'created_by'
            ]);
        });
    }
}
