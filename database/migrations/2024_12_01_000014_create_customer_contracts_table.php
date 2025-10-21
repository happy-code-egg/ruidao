<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerContractsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户合同表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_contracts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('合同ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->bigInteger('business_opportunity_id')->nullable()->comment('商机ID（关联business_opportunities表）');
            $table->string('contract_no', 100)->unique()->comment('合同号');
            $table->string('contract_name', 200)->comment('合同名称');
            $table->decimal('contract_amount', 15, 2)->comment('合同金额');
            $table->date('sign_date')->comment('签订日期');
            $table->date('start_date')->nullable()->comment('开始日期');
            $table->date('end_date')->nullable()->comment('结束日期');
            $table->string('contract_type', 100)->nullable()->comment('合同类型');
            $table->string('status', 50)->default('执行中')->comment('合同状态：执行中、已完成、已终止');
            $table->bigInteger('business_person_id')->nullable()->comment('业务人员ID（关联users表）');
            $table->text('contract_content')->nullable()->comment('合同内容');
            $table->string('payment_method', 100)->nullable()->comment('付款方式');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('已付金额');
            $table->decimal('unpaid_amount', 15, 2)->default(0)->comment('未付金额');
            $table->text('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['business_opportunity_id']);
            $table->index(['business_person_id']);
            $table->index(['status']);
            $table->index(['sign_date']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_contracts');
    }
}
