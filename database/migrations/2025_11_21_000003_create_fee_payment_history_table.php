<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentHistoryTable extends Migration
{
    /**
     * Run the migrations.
     * 创建缴费单历史记录表
     * @return void
     */
    public function up()
    {
        Schema::create('fee_payment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_payment_id')->comment('缴费单ID');
            $table->string('operation', 50)->comment('操作类型');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作人ID');
            $table->string('operator_name', 100)->nullable()->comment('操作人名称');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            // 添加索引
            $table->index('fee_payment_id');
            $table->index('operation');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_payment_history');
    }
}

