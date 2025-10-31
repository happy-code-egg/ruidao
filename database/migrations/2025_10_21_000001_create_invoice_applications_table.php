<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no', 50)->unique()->comment('申请单号');
            $table->date('application_date')->comment('申请日期');
            $table->string('applicant', 100)->comment('申请人');
            $table->string('department', 100)->nullable()->comment('部门');
            
            // 客户信息
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name', 200)->nullable()->comment('客户名称');
            $table->string('customer_no', 50)->nullable()->comment('客户编号');
            
            // 合同信息
            $table->unsignedBigInteger('contract_id')->nullable()->comment('合同ID');
            $table->string('contract_name', 200)->nullable()->comment('合同名称');
            $table->string('contract_no', 50)->nullable()->comment('合同编号');
            
            // 购买方开票信息（从客户信息自动获取，不可修改）
            $table->string('buyer_name', 200)->nullable()->comment('购买方名称');
            $table->string('buyer_tax_id', 100)->nullable()->comment('纳税人识别号');
            $table->string('buyer_address', 500)->nullable()->comment('地址、电话');
            $table->string('buyer_bank_account', 200)->nullable()->comment('开户银行及账号');
            
            // 发票信息
            $table->string('invoice_type', 50)->default('special')->comment('发票类型: special-专票, normal-普票, electronic-电子');
            $table->decimal('invoice_amount', 15, 2)->default(0)->comment('开票金额');
            $table->json('items')->nullable()->comment('开票项目明细');
            
            // 流程状态
            $table->string('flow_status', 50)->default('draft')->comment('流程状态: draft-草稿, reviewing-审核中, approved-已通过, rejected-已退回, completed-已完成');
            $table->string('current_handler', 100)->nullable()->comment('当前处理人');
            $table->string('priority', 20)->default('normal')->comment('优先级: urgent-紧急, normal-普通, low-低');
            
            // 发票上传信息
            $table->string('invoice_number', 100)->nullable()->comment('发票号码');
            $table->date('invoice_date')->nullable()->comment('开票日期');
            $table->json('invoice_files')->nullable()->comment('发票文件');
            $table->text('upload_remark')->nullable()->comment('上传备注');
            
            // 审批信息
            $table->text('approval_comment')->nullable()->comment('审批意见');
            $table->timestamp('approved_at')->nullable()->comment('审批时间');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('审批人ID');
            
            // 备注
            $table->text('remark')->nullable()->comment('备注');
            
            // 创建和更新信息
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新人ID');
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index('application_no');
            $table->index('customer_id');
            $table->index('contract_id');
            $table->index('flow_status');
            $table->index('application_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_applications');
    }
}

