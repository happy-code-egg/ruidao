<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessRulesTable extends Migration
{
    /**
     * Run the migrations.
     * 处理事项规则表
     * @return void
     */
    public function up()
    {
        Schema::create('process_rules', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            
            // 基本信息
            $table->string('name', 200)->comment('规则名称');
            $table->text('description')->nullable()->comment('规则描述');
            $table->string('rule_type', 50)->comment('规则类型');
            
            // 关联信息
            $table->unsignedBigInteger('process_item_id')->nullable()->comment('处理事项ID');
            $table->string('case_type', 100)->nullable()->comment('项目类型');
            $table->string('business_type', 100)->nullable()->comment('业务类型');
            $table->string('application_type', 100)->nullable()->comment('申请类型');
            $table->string('country', 100)->nullable()->comment('国家（地区）');
            $table->string('process_item_type', 100)->nullable()->comment('处理事项类型');
            
            // 规则条件和动作（JSON格式）
            $table->jsonb('conditions')->nullable()->comment('规则条件');
            $table->jsonb('actions')->nullable()->comment('规则动作');
            
            // 生成或完成
            $table->string('generate_or_complete', 20)->nullable()->comment('生成或完成');
            
            // 处理人员信息
            $table->string('processor', 100)->nullable()->comment('处理人');
            $table->string('fixed_personnel', 100)->nullable()->comment('固定人员');
            $table->boolean('is_assign_case')->default(false)->comment('是否配案');
            
            // 期限设置
            $table->jsonb('internal_deadline')->nullable()->comment('内部期限');
            $table->jsonb('customer_deadline')->nullable()->comment('客户期限');
            $table->jsonb('official_deadline')->nullable()->comment('官方期限');
            $table->jsonb('complete_date')->nullable()->comment('完成日期');
            
            // 状态和优先级
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('priority')->default(1)->comment('优先级');
            $table->boolean('is_effective')->default(true)->comment('是否有效');
            
            // 排序
            $table->integer('sort_order')->default(0)->comment('排序');
            
            // 审计字段
            $table->string('updated_by', 100)->nullable()->comment('更新人');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->unsignedBigInteger('updated_by_id')->nullable()->comment('更新人ID');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 添加索引
            $table->index(['status', 'priority']);
            $table->index(['rule_type']);
            $table->index(['case_type']);
            $table->index(['country']);
            $table->index(['process_item_id']);
            $table->index(['sort_order']);
            $table->index(['is_effective']);
            
            // 外键约束
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_rules');
    }
}
