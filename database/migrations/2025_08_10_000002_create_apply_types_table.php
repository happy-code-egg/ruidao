<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplyTypesTable extends Migration
{
    /**
     * Run the migrations.
     * 创建申请类型表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('apply_types', function (Blueprint $table) {
            $table->id();
            $table->string('country', 100)->nullable()->comment('国家/地区');
            $table->string('case_type', 100)->nullable()->comment('项目类型');
            $table->string('business_type', 100)->nullable()->comment('业务类型');
            $table->string('apply_type_name', 100)->comment('申请类型名称');
            $table->string('apply_type_code', 50)->comment('申请类型代码');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->tinyInteger('is_default')->default(0)->comment('是否默认(1是0否)');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->string('update_user', 100)->nullable()->comment('更新人');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 添加索引
            $table->index(['status', 'sort_order']);
            $table->index(['country']);
            $table->index(['case_type']);
            $table->index(['business_type']);
            $table->index(['apply_type_name']);
            $table->index(['apply_type_code']);
            $table->index(['is_valid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apply_types');
    }
}
