<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatentAnnualFeesTable extends Migration
{
    /**
     * Run the migrations.
     * 专利年费配置主表
     * @return void
     */
    public function up()
    {
        Schema::create('patent_annual_fees', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            $table->string('case_type', 100)->comment('项目类型');
            $table->string('apply_type', 100)->comment('申请类型');
            $table->string('country', 100)->comment('国家（地区）');
            $table->string('start_date', 100)->comment('起算日');
            $table->string('currency', 10)->comment('币别');
            $table->tinyInteger('has_fee_guide')->default(1)->comment('是否缴费导览(1是0否)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->tinyInteger('is_valid')->default(1)->comment('是否有效(1是0否)');
            $table->string('updated_by', 100)->nullable()->comment('更新人');
            $table->string('created_by', 100)->nullable()->comment('创建人');
            $table->timestamps();
            
            // 添加索引
            $table->index(['case_type']);
            $table->index(['apply_type']);
            $table->index(['country']);
            $table->index(['currency']);
            $table->index(['is_valid']);
            $table->index(['sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patent_annual_fees');
    }
}
