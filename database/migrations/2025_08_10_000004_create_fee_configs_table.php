<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeConfigsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建费用配置表 - 整合所有相关迁移文件
     * @return void
     */
    public function up()
    {
        Schema::create('fee_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(1)->comment('排序');
            $table->json('case_type')->nullable()->comment('项目类型(JSON)');
            $table->json('business_type')->nullable()->comment('业务类型(JSON)');
            $table->json('apply_type')->nullable()->comment('申请类型(JSON)');
            $table->json('country')->nullable()->comment('国家/地区(JSON)');
            $table->string('fee_type', 50)->comment('费用类型');
            $table->string('fee_name', 200)->comment('费用名称');
            $table->string('fee_name_en', 200)->nullable()->comment('费用名称(英文)');
            $table->string('currency', 10)->default('CNY')->comment('币种');
            $table->string('fee_code', 100)->nullable()->comment('费用代码');
            $table->decimal('base_fee', 15, 2)->default(0)->comment('基础费用');
            $table->decimal('small_entity_fee', 15, 2)->default(0)->comment('小实体费用');
            $table->decimal('micro_entity_fee', 15, 2)->default(0)->comment('微实体费用');
            $table->json('role')->nullable()->comment('角色(JSON)');
            $table->json('use_stage')->nullable()->comment('使用阶段(JSON)');
            $table->string('fee_category', 50)->default('官费')->comment('费用类别');
            $table->decimal('additional_fee', 10, 2)->default(0)->comment('附加费用');
            $table->string('unit', 20)->default('元')->comment('单位');
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->tinyInteger('is_required')->default(1)->comment('是否必需(1是0否)');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->integer('updated_by')->nullable()->comment('更新人');
            $table->integer('created_by')->nullable()->comment('创建人');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            
            // 添加索引
            $table->index(['status', 'sort_order']);
            $table->index(['fee_type']);
            $table->index(['fee_category']);
            $table->index(['case_type']);
            $table->index(['country']);
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
        Schema::dropIfExists('fee_configs');
    }
}
