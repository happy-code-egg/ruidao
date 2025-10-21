<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentReceivedRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_received_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_received_id')->comment('到款单ID');
            $table->unsignedBigInteger('payment_request_id')->comment('请款单ID');
            $table->decimal('allocated_amount', 15, 2)->default(0)->comment('分配金额');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index('payment_received_id');
            $table->index('payment_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_received_requests');
    }
}

