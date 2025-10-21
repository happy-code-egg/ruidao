<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractServicesTable extends Migration
{
    /**
     * Run the migrations.
     * 合同服务明细表
     * @return void
     */
    public function up()
    {
        Schema::create('contract_services', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('服务明细ID');
            $table->bigInteger('contract_id')->comment('合同ID（关联contracts表）');
            $table->string('service_name', 200)->comment('服务内容');
            $table->text('service_description')->nullable()->comment('项目名称/服务描述');
            $table->decimal('amount', 15, 2)->default(0.00)->comment('服务费');
            $table->decimal('official_fee', 15, 2)->default(0.00)->comment('官费');
            $table->text('remark')->nullable()->comment('备注');
            $table->integer('sort_order')->default(0)->comment('排序');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['contract_id']);
            $table->index(['sort_order']);
            
            // 外键约束
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_services');
    }
}
