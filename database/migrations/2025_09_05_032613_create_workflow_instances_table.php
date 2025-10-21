<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id')->comment('工作流ID');
            $table->unsignedBigInteger('business_id')->comment('业务ID');
            $table->string('business_type', 50)->comment('业务类型：contract、case、payment等');
            $table->string('business_title')->comment('业务标题，用于显示');
            $table->integer('current_node_index')->default(0)->comment('当前节点索引');
            $table->enum('status', ['pending', 'completed', 'rejected', 'cancelled'])->default('pending')->comment('状态');
            $table->unsignedBigInteger('created_by')->comment('创建人');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['business_type', 'business_id'], 'idx_business');
            $table->index(['workflow_id', 'status'], 'idx_workflow_status');
            $table->index('created_by');
            $table->index('status');

            // 外键
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_instances');
    }
}
