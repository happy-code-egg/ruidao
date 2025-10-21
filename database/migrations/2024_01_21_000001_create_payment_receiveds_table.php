<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentReceivedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_receiveds', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 50)->unique()->comment('到款单号');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->unsignedBigInteger('contract_id')->nullable()->comment('合同ID');
            $table->tinyInteger('status')->default(1)->comment('状态: 1-草稿, 2-待认领, 3-已认领, 4-已核销');
            $table->decimal('amount', 15, 2)->default(0)->comment('到款金额');
            $table->decimal('claimed_amount', 15, 2)->default(0)->comment('已认领金额');
            $table->decimal('unclaimed_amount', 15, 2)->default(0)->comment('未认领金额');
            $table->string('currency', 10)->default('CNY')->comment('币种');
            $table->string('payer', 200)->nullable()->comment('付款方');
            $table->string('payer_account', 100)->nullable()->comment('付款账号');
            $table->string('bank_account', 100)->nullable()->comment('收款账号');
            $table->string('payment_method', 50)->nullable()->comment('付款方式');
            $table->string('transaction_ref', 100)->nullable()->comment('交易流水号');
            $table->date('received_date')->nullable()->comment('到款日期');
            $table->unsignedBigInteger('claimed_by')->nullable()->comment('认领人ID');
            $table->timestamp('claimed_at')->nullable()->comment('认领时间');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('contract_id');
            $table->index('status');
            $table->index('received_date');
            $table->index('created_by');
            $table->index('claimed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_receiveds');
    }
}

