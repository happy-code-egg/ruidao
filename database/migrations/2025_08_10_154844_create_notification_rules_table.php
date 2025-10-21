<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationRulesTable extends Migration
{
    /**
     * Run the migrations.
     * 通知书规则表 - 用于管理各种文件类型的处理规则
     * @return void
     */
    public function up()
    {
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            
            // 基本信息
            $table->string('name', 200)->comment('规则名称');
            $table->text('description')->nullable()->comment('规则描述');
            $table->string('rule_type', 50)->comment('规则类型');
            
            // 关联文件类型
            $table->unsignedBigInteger('file_type_id')->nullable()->comment('文件类型ID');
            
            // 规则条件和动作（JSON格式）
            $table->jsonb('conditions')->nullable()->comment('规则条件');
            $table->jsonb('actions')->nullable()->comment('规则动作');
            
            // 是否配置
            $table->string('is_config', 10)->nullable()->comment('是否配置');
            
            // 处理事项相关
            $table->string('process_item', 100)->nullable()->comment('处理事项');
            $table->string('process_status', 50)->nullable()->comment('处理状态');
            
            // 上传和转文对象
            $table->string('is_upload', 10)->nullable()->comment('是否上传附件');
            $table->string('transfer_target', 10)->nullable()->comment('转文对象');
            
            // 附件名称配置
            $table->jsonb('attachment_config')->nullable()->comment('附件名称配置');
            $table->string('generated_filename', 500)->nullable()->comment('生成的文件名');
            
            // 处理人
            $table->string('processor', 100)->nullable()->comment('处理人');
            $table->string('fixed_personnel', 100)->nullable()->comment('固定人员');
            
            // 期限配置（JSON格式）
            $table->jsonb('internal_deadline')->nullable()->comment('内部期限配置');
            $table->jsonb('customer_deadline')->nullable()->comment('客户期限配置');
            $table->jsonb('official_deadline')->nullable()->comment('官方期限配置');
            $table->jsonb('internal_priority_deadline')->nullable()->comment('内部期限(优先)');
            $table->jsonb('customer_priority_deadline')->nullable()->comment('客户期限(优先)');
            $table->jsonb('official_priority_deadline')->nullable()->comment('官方期限(优先)');
            $table->jsonb('internal_precheck_deadline')->nullable()->comment('内部期限(预审)');
            $table->jsonb('customer_precheck_deadline')->nullable()->comment('客户期限(预审)');
            $table->jsonb('official_precheck_deadline')->nullable()->comment('官方期限(预审)');
            $table->jsonb('complete_date')->nullable()->comment('完成日期配置');
            
            // 状态和优先级
            $table->tinyInteger('is_effective')->default(1)->comment('是否有效(1有效0无效)');
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            $table->integer('priority')->default(1)->comment('优先级');
            $table->integer('sort_order')->default(0)->comment('排序');
            
            // 审计字段
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->string('updater', 100)->nullable()->comment('更新人');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 添加索引
            $table->index(['file_type_id']);
            $table->index(['rule_type']);
            $table->index(['status', 'priority']);
            $table->index(['is_effective']);
            $table->index(['sort_order']);
            
            // 外键约束
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_rules');
    }
}
