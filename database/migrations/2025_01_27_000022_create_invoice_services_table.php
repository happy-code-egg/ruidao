<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceServicesTable extends Migration
{
    /**
     * Run the migrations.
     * 开票服务类型设置表
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_services', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('开票服务类型ID');
            $table->string('service_name', 200)->comment('开票服务内容名称');
            $table->string('service_code', 50)->comment('开票服务编码')->nullable();
            $table->text('description')->nullable()->comment('描述');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效：0-否，1-是');
            $table->integer('sort_order')->default(0)->comment('排序号');
            $table->bigInteger('created_by')->nullable()->comment('创建人');
            $table->bigInteger('updated_by')->nullable()->comment('更新人');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_services');
    }
}
