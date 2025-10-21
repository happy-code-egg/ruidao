<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id')->comment('工作流实例ID');
            $table->integer('node_index')->comment('节点索引');
            $table->string('node_name')->comment('节点名称');
            $table->unsignedBigInteger('assignee_id')->nullable()->comment('指定处理人ID');
            $table->unsignedBigInteger('processor_id')->nullable()->comment('实际处理人ID');
            $table->enum('action', ['approve', 'reject', 'auto', 'pending'])->default('pending')->comment('处理动作');
            $table->text('comment')->nullable()->comment('处理备注');
            $table->timestamp('processed_at')->nullable()->comment('处理时间');
            $table->timestamps();

            // 索引
            $table->index('instance_id');
            $table->index(['assignee_id', 'action'], 'idx_assignee_action');
            $table->index('processor_id');
            $table->index('action');

            // 外键
            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('processor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_processes');
    }
}
