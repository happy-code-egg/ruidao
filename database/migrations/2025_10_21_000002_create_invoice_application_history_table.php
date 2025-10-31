<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceApplicationHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_application_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_application_id')->comment('发票申请ID');
            $table->string('title', 100)->comment('操作标题');
            $table->string('handler', 100)->comment('处理人');
            $table->string('action', 50)->comment('操作类型: submit-提交, approve-通过, reject-退回, transfer-转办, upload-上传');
            $table->text('comment')->nullable()->comment('处理意见');
            $table->string('type', 20)->default('primary')->comment('显示类型: primary, success, warning, danger, info');
            $table->timestamps();
            
            // 索引
            $table->index('invoice_application_id');
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
        Schema::dropIfExists('invoice_application_history');
    }
}

