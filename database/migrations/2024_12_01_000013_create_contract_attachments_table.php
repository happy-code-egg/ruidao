<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     * 合同附件表
     * @return void
     */
    public function up()
    {
        Schema::create('contract_attachments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('附件ID');
            $table->bigInteger('contract_id')->comment('合同ID（关联contracts表）');
            $table->string('file_type', 50)->comment('文件大类');
            $table->string('file_sub_type', 50)->nullable()->comment('文件小类');
            $table->string('file_name', 255)->comment('文件名称');
            $table->string('file_path', 500)->nullable()->comment('文件路径');
            $table->bigInteger('file_size')->default(0)->comment('文件大小（字节）');
            $table->string('file_extension', 10)->nullable()->comment('文件扩展名');
            $table->string('mime_type', 100)->nullable()->comment('MIME类型');
            $table->bigInteger('uploader_id')->nullable()->comment('上传人员ID（关联users表）');
            $table->timestamp('upload_time')->nullable()->comment('上传时间');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['contract_id']);
            $table->index(['file_type']);
            $table->index(['uploader_id']);
            $table->index(['upload_time']);
            
            // 外键约束
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_attachments');
    }
}
