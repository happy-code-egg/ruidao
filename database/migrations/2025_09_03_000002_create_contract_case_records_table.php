<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractCaseRecordsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建合同项目记录表
     * @return void
     */
    public function up()
    {
        Schema::create('contract_case_records', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('记录ID');
            $table->string('case_code', 50)->unique()->comment('项目编码');
            $table->string('case_name', 200)->comment('项目名称');
            $table->bigInteger('customer_id')->comment('客户ID');
            $table->bigInteger('contract_id')->nullable()->comment('合同ID');
            $table->bigInteger('case_id')->nullable()->comment('关联的案件ID');
            $table->smallInteger('case_type')->comment('项目类型：1-专利，2-商标，3-版权，4-科服');
            $table->string('case_subtype', 50)->nullable()->comment('项目子类型');
            $table->string('application_type', 50)->nullable()->comment('申请类型');
            $table->smallInteger('case_status')->default(2)->comment('项目状态：1-草稿，2-待立项，3-已提交，4-处理中，5-已授权，6-已驳回，7-已完成');
            $table->string('case_phase', 50)->nullable()->comment('项目阶段');
            $table->smallInteger('priority_level')->default(3)->comment('优先级：1-高，2-中，3-低');
            $table->string('application_no', 100)->nullable()->comment('申请号');
            $table->date('application_date')->nullable()->comment('申请日');
            $table->string('registration_no', 100)->nullable()->comment('注册号');
            $table->date('registration_date')->nullable()->comment('注册日');
            $table->string('acceptance_no', 100)->nullable()->comment('受理号（版权）');
            $table->string('country_code', 10)->default('CN')->comment('国家/地区代码');
            $table->bigInteger('presale_support')->nullable()->comment('售前支持联系人ID');
            $table->bigInteger('tech_leader')->nullable()->comment('技术主导联系人ID');
            $table->bigInteger('tech_contact')->nullable()->comment('技术联系人ID');
            $table->smallInteger('is_authorized')->default(0)->comment('是否已有项目：0-否，1-是');
            $table->string('project_no', 100)->nullable()->comment('项目编号');
            $table->string('tech_service_name', 200)->nullable()->comment('科技服务名称');
            $table->string('trademark_category', 50)->nullable()->comment('商标类别');
            $table->bigInteger('product_id')->nullable()->comment('产品ID');
            $table->smallInteger('entity_type')->nullable()->comment('实体类型：1-大实体，2-小实体，3-微实体');
            $table->jsonb('applicant_info')->nullable()->comment('申请人信息（JSON格式）');
            $table->jsonb('inventor_info')->nullable()->comment('发明人信息（JSON格式）');
            $table->bigInteger('business_person_id')->nullable()->comment('业务人员ID');
            $table->bigInteger('agent_id')->nullable()->comment('代理师ID');
            $table->bigInteger('assistant_id')->nullable()->comment('助理ID');
            $table->bigInteger('agency_id')->nullable()->comment('代理机构ID');
            $table->date('deadline_date')->nullable()->comment('期限日期');
            $table->date('annual_fee_due_date')->nullable()->comment('年费到期日');
            $table->decimal('estimated_cost', 15, 2)->nullable()->comment('预估费用');
            $table->decimal('actual_cost', 15, 2)->default(0.00)->comment('实际费用');
            $table->decimal('service_fee', 15, 2)->nullable()->comment('服务费');
            $table->decimal('official_fee', 15, 2)->nullable()->comment('官费');
            $table->smallInteger('is_priority')->default(0)->comment('是否优先权：0-否，1-是');
            $table->jsonb('priority_info')->nullable()->comment('优先权信息（JSON格式）');
            $table->jsonb('classification_info')->nullable()->comment('分类信息（JSON格式）');
            $table->text('case_description')->nullable()->comment('项目描述');
            $table->text('technical_field')->nullable()->comment('技术领域');
            $table->text('innovation_points')->nullable()->comment('创新点');
            $table->text('remarks')->nullable()->comment('备注');
            
            // 费用信息（JSON格式存储）
            $table->jsonb('service_fees')->nullable()->comment('服务费信息（JSON格式）');
            $table->jsonb('official_fees')->nullable()->comment('官费信息（JSON格式）');
            
            // 附件信息（JSON格式存储）
            $table->jsonb('attachments')->nullable()->comment('附件信息（JSON格式）');
            
            // 是否已立项标识
            $table->boolean('is_filed')->default(false)->comment('是否已立项');
            $table->timestamp('filed_at')->nullable()->comment('立项时间');
            $table->bigInteger('filed_by')->nullable()->comment('立项人ID');
            
            $table->bigInteger('created_by')->nullable()->comment('创建人ID');
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index('case_code');
            $table->index('customer_id');
            $table->index('contract_id');
            $table->index('case_id');
            $table->index('case_type');
            $table->index('case_status');
            $table->index('is_filed');
            $table->index('created_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_case_records');
    }
}
