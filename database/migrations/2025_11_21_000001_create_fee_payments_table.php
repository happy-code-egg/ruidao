<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建缴费单表
     * @return void
     */
    public function up()
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 50)->unique()->comment('缴费单号');
            $table->string('payment_name', 200)->nullable()->comment('缴费单名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name', 200)->nullable()->comment('客户名称');
            $table->unsignedBigInteger('company_id')->nullable()->comment('出款公司ID');
            $table->string('company_name', 200)->nullable()->comment('出款公司名称');
            $table->unsignedBigInteger('agency_id')->nullable()->comment('代理机构ID');
            $table->string('agency_name', 200)->nullable()->comment('代理机构名称');
            $table->tinyInteger('status')->default(1)->comment('状态：1-草稿，2-待处理，3-已提交，4-已完成');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('总金额');
            $table->string('currency', 10)->default('CNY')->comment('币种');
            $table->date('payment_date')->nullable()->comment('缴费日期');
            $table->date('actual_payment_date')->nullable()->comment('实际缴费日期');
            $table->tinyInteger('payment_method')->nullable()->comment('缴费方式：1-银行转账，2-网上支付，3-现金');
            $table->string('payment_account', 100)->nullable()->comment('付款账户');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('creator_id')->nullable()->comment('创建人ID');
            $table->string('creator_name', 100)->nullable()->comment('创建人名称');
            $table->unsignedBigInteger('modifier_id')->nullable()->comment('修改人ID');
            $table->string('modifier_name', 100)->nullable()->comment('修改人名称');
            $table->timestamp('submitted_at')->nullable()->comment('提交时间');
            $table->timestamps();
            $table->softDeletes();

            // 添加索引
            $table->index('payment_no');
            $table->index('customer_id');
            $table->index('company_id');
            $table->index('agency_id');
            $table->index('status');
            $table->index('payment_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_payments');
    }
}

