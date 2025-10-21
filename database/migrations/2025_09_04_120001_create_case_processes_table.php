<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseProcessesTable extends Migration
{
    /**
     * Run the migrations.
     * 处理事项表
     * @return void
     */
    public function up()
    {
        Schema::create('case_processes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('处理事项ID');
            $table->bigInteger('case_id')->comment('项目ID');
            $table->string('process_code', 50)->nullable()->comment('处理事项编码');
            $table->string('process_name', 200)->comment('处理事项名称');
            $table->string('process_type', 100)->nullable()->comment('处理事项类型');
            $table->smallInteger('process_status')->default(1)->comment('处理状态：1-待处理，2-处理中，3-已完成，4-已取消');
            $table->smallInteger('priority_level')->default(3)->comment('优先级：1-高，2-中，3-低');
            $table->bigInteger('assigned_to')->nullable()->comment('负责人ID');
            $table->bigInteger('assignee')->nullable()->comment('配案人ID');
            $table->boolean('is_assign')->default(false)->comment('是否配案');
            $table->date('due_date')->nullable()->comment('截止日期');
            $table->date('internal_deadline')->nullable()->comment('内部期限');
            $table->date('official_deadline')->nullable()->comment('官方期限');
            $table->date('customer_deadline')->nullable()->comment('客户期限');
            $table->date('expected_complete_date')->nullable()->comment('预计完成日期');
            $table->date('completion_date')->nullable()->comment('完成日期');
            $table->decimal('estimated_hours', 8, 2)->nullable()->comment('预估工时');
            $table->decimal('actual_hours', 8, 2)->nullable()->comment('实际工时');
            $table->string('process_coefficient', 100)->nullable()->comment('处理事项系数');
            $table->text('process_description')->nullable()->comment('事项描述');
            $table->text('process_result')->nullable()->comment('处理结果');
            $table->text('process_remark')->nullable()->comment('处理事项备注');
            $table->jsonb('attachments')->nullable()->comment('附件信息（JSON格式）');
            $table->bigInteger('parent_process_id')->nullable()->comment('父事项ID');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->timestamp('completed_time')->nullable()->comment('完成时间');
            
            // 添加索引
            $table->index('case_id');
            $table->index('process_status');
            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('internal_deadline');
            $table->index('official_deadline');
            $table->index('customer_deadline');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('case_processes');
    }
}
