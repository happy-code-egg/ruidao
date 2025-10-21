<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWriteOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('write_offs', function (Blueprint $table) {
            $table->id();
            $table->string('write_off_no', 50)->unique()->comment('核销单号');
            $table->unsignedBigInteger('payment_received_id')->comment('到款单ID');
            $table->unsignedBigInteger('payment_request_id')->nullable()->comment('请款单ID');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->unsignedBigInteger('contract_id')->nullable()->comment('合同ID');
            $table->decimal('write_off_amount', 15, 2)->comment('核销金额');
            $table->date('write_off_date')->comment('核销日期');
            $table->tinyInteger('status')->default(1)->comment('状态: 1-已完成, 2-已撤销');
            $table->text('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('write_off_by')->nullable()->comment('核销人ID');
            $table->timestamp('write_off_at')->nullable()->comment('核销时间');
            $table->unsignedBigInteger('reverted_by')->nullable()->comment('撤销人ID');
            $table->timestamp('reverted_at')->nullable()->comment('撤销时间');
            $table->text('revert_reason')->nullable()->comment('撤销原因');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index('write_off_no');
            $table->index('payment_received_id');
            $table->index('payment_request_id');
            $table->index('customer_id');
            $table->index('status');
            $table->index('write_off_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('write_offs');
    }
}

