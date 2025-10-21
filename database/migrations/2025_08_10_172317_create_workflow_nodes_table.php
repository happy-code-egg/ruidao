<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowNodesTable extends Migration
{
    /**
     * Run the migrations.
     * 工作流节点表 - 存储工作流节点详细信息（备用表，主要数据存储在workflows表的nodes字段中）
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_nodes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            
            // 关联工作流
            $table->unsignedBigInteger('workflow_id')->comment('工作流ID');
            
            // 节点基本信息
            $table->string('name', 100)->comment('节点名称');
            $table->string('type', 50)->nullable()->comment('节点类型');
            $table->text('description')->nullable()->comment('节点描述');
            
            // 节点配置
            $table->jsonb('assignee')->nullable()->comment('处理人员配置');
            $table->integer('time_limit')->nullable()->comment('处理时限(小时)');
            $table->integer('sort_order')->default(0)->comment('排序');
            
            // 审计字段
            $table->timestamps();
            $table->softDeletes();
            
            // 外键约束
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            
            // 索引
            $table->index(['workflow_id', 'sort_order']);
            
            $table->comment('工作流节点表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_nodes');
    }
}
