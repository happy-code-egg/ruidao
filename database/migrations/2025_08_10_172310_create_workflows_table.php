<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     * 工作流主表 - 存储工作流基本信息
     * @return void
     */
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            
            // 工作流基本信息
            $table->string('name', 200)->comment('工作流名称');
            $table->string('code', 100)->unique()->comment('工作流代码');
            $table->text('description')->nullable()->comment('工作流描述');
            
            // 项目类型
            $table->string('case_type', 50)->comment('项目类型');
            
            // 工作流状态
            $table->tinyInteger('status')->default(1)->comment('状态(1启用0禁用)');
            
            // 节点配置（JSON格式）
            $table->jsonb('nodes')->nullable()->comment('工作流节点配置');
            
            // 审计字段
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['case_type', 'status']);
            $table->index('code');
            
            $table->comment('工作流配置表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflows');
    }
}
