<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessInformationsTable extends Migration
{
    /**
     * Run the migrations.
     * 处理事项信息表
     * @return void
     */
    public function up()
    {
        Schema::create('process_informations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            $table->string('case_type', 100)->comment('项目类型');
            $table->string('business_type', 100)->comment('业务类型');
            $table->jsonb('application_type')->nullable()->comment('申请类型(多选)');
            $table->string('country', 100)->comment('国家（地区）');
            $table->string('process_name', 200)->comment('处理事项名称');
            $table->integer('flow_completed')->nullable()->comment('缴费后完成');
            $table->integer('proposal_inquiry')->nullable()->comment('提案是否可用');
            $table->integer('data_updater_inquiry')->nullable()->comment('数据更新是否可用');
            $table->integer('update_case_handler')->nullable()->comment('更新项目处理人');
            $table->jsonb('process_status')->nullable()->comment('处理状态(多选)');
            $table->string('case_phase', 100)->nullable()->comment('项目阶段');
            $table->string('process_type', 100)->comment('处理事项类型');
            $table->integer('is_case_node')->nullable()->comment('是否项目节点');
            $table->integer('is_commission')->nullable()->comment('是否提成');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->string('updated_by', 100)->nullable()->comment('更新人');
            $table->timestamps();
            
            // 添加索引
            $table->index(['case_type']);
            $table->index(['business_type']);
            $table->index(['country']);
            $table->index(['process_type']);
            $table->index(['is_valid']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_informations');
    }
}
