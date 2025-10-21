<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     * 文件信息表
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('文件ID');
            $table->string('file_name', 255)->comment('文件名');
            $table->string('file_path', 500)->comment('文件路径');
            $table->bigInteger('file_size')->nullable()->comment('文件大小（字节）');
            $table->string('file_type', 50)->nullable()->comment('文件类型');
            $table->string('file_extension', 20)->nullable()->comment('文件扩展名');
            $table->string('file_md5', 32)->nullable()->comment('文件MD5值');
            $table->string('mime_type', 100)->nullable()->comment('MIME类型');
            $table->smallInteger('storage_type')->default(1)->comment('存储类型：1-本地，2-云存储');
            $table->string('business_type', 50)->nullable()->comment('业务类型');
            $table->bigInteger('business_id')->nullable()->comment('业务ID');
            $table->string('file_category', 50)->nullable()->comment('文件分类');
            $table->text('file_description')->nullable()->comment('文件描述');
            $table->integer('download_count')->default(0)->comment('下载次数');
            $table->smallInteger('is_public')->default(0)->comment('是否公开：0-否，1-是');
            $table->bigInteger('created_by')->nullable()->comment('上传人ID');
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
