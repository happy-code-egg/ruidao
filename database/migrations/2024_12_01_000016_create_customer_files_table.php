<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerFilesTable extends Migration
{
    /**
     * Run the migrations.
     * 客户文件表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_files', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('文件ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('file_name', 200)->comment('文件名称');
            $table->string('file_original_name', 200)->comment('原始文件名');
            $table->string('file_path', 500)->comment('文件路径');
            $table->string('file_type', 50)->nullable()->comment('文件类型');
            $table->string('file_category', 100)->nullable()->comment('文件分类：合同、证件、技术资料等');
            $table->bigInteger('file_size')->nullable()->comment('文件大小（字节）');
            $table->string('mime_type', 100)->nullable()->comment('MIME类型');
            $table->text('file_description')->nullable()->comment('文件描述');
            $table->boolean('is_private')->default(false)->comment('是否私有文件');
            $table->bigInteger('uploaded_by')->comment('上传人ID（关联users表）');
            $table->text('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['file_category']);
            $table->index(['uploaded_by']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_files');
    }
}
