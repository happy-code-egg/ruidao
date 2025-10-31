<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDownloadRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_download_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_application_id')->comment('发票申请ID');
            $table->string('downloader', 100)->comment('下载人');
            $table->string('download_ip', 50)->nullable()->comment('下载IP');
            $table->string('download_type', 50)->default('invoice')->comment('下载类型: invoice-发票文件, attachment-附件, summary-汇总');
            $table->text('remark')->nullable()->comment('备注');
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
        Schema::dropIfExists('invoice_download_records');
    }
}

