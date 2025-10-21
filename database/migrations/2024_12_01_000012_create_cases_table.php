<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasesTable extends Migration
{
    /**
     * Run the migrations.
     * 项目表
     * @return void
     */
    public function up()
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('项目ID');
            $table->string('case_code', 50)->unique()->comment('项目编码');
            $table->string('case_name', 200)->comment('项目名称');
            $table->bigInteger('customer_id')->comment('客户ID');
            $table->bigInteger('contract_id')->nullable()->comment('合同ID');
            $table->smallInteger('case_type')->comment('项目类型：1-专利，2-商标，3-版权，4-科服');
            $table->string('case_subtype', 50)->nullable()->comment('项目子类型');
            $table->string('application_type', 50)->nullable()->comment('申请类型');
            $table->smallInteger('case_status')->default(1)->comment('项目状态');
            $table->string('case_phase', 50)->nullable()->comment('项目阶段');
            $table->smallInteger('priority_level')->default(3)->comment('优先级：1-高，2-中，3-低');
            $table->string('application_no', 100)->nullable()->comment('申请号');
            $table->date('application_date')->nullable()->comment('申请日');
            $table->string('registration_no', 100)->nullable()->comment('注册号');
            $table->date('registration_date')->nullable()->comment('注册日');
            $table->string('country_code', 10)->default('CN')->comment('国家/地区代码');
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
            $table->bigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->softDeletes();
        
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cases');
    }
}
