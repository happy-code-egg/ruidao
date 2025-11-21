<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentItemsTable extends Migration
{
    /**
     * Run the migrations.
     * 创建缴费单明细表
     * @return void
     */
    public function up()
    {
        Schema::create('fee_payment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_payment_id')->comment('缴费单ID');
            $table->unsignedBigInteger('case_id')->nullable()->comment('案件ID');
            $table->string('our_ref', 100)->nullable()->comment('我方编号');
            $table->string('application_no', 100)->nullable()->comment('申请号');
            $table->string('case_name', 200)->nullable()->comment('案件名称');
            $table->string('applicant', 200)->nullable()->comment('申请人');
            $table->string('process_item', 200)->nullable()->comment('处理项目');
            $table->string('fee_name', 200)->nullable()->comment('费用名称');
            $table->string('fee_type', 50)->nullable()->comment('费用类型');
            $table->decimal('amount', 15, 2)->default(0)->comment('金额');
            $table->string('currency', 10)->default('CNY')->comment('币种');
            $table->date('payment_deadline')->nullable()->comment('缴费期限');
            $table->date('actual_payment_date')->nullable()->comment('实际缴费日期');
            $table->string('receipt_no', 100)->nullable()->comment('缴费回执号');
            $table->string('pickup_code', 50)->nullable()->comment('取票码');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            // 添加索引和外键
            $table->index('fee_payment_id');
            $table->index('case_id');
            $table->index('our_ref');
            $table->index('application_no');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_payment_items');
    }
}

