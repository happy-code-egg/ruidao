<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatentAnnualFeeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     * 专利年费详情表
     * @return void
     */
    public function up()
    {
        Schema::create('patent_annual_fee_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('主键ID');
            $table->bigInteger('patent_annual_fee_id')->comment('专利年费配置ID');
            $table->string('stage_code', 100)->comment('项目阶段');
            $table->integer('rank')->default(0)->comment('排序');
            $table->integer('official_year')->default(0)->comment('官方期限年');
            $table->integer('official_month')->default(0)->comment('官方期限月');
            $table->integer('official_day')->default(0)->comment('官方期限日');
            $table->integer('start_year')->default(0)->comment('起始年度');
            $table->integer('end_year')->default(0)->comment('结束年度');
            $table->decimal('base_fee', 10, 2)->default(0)->comment('基础费用');
            $table->decimal('small_fee', 10, 2)->default(0)->comment('小实体减收70%');
            $table->decimal('micro_fee', 10, 2)->default(0)->comment('微实体减收85%');
            $table->decimal('authorization_fee', 10, 2)->default(0)->comment('授权收费');
            $table->timestamps();
            
            // 添加外键约束
            $table->foreign('patent_annual_fee_id')->references('id')->on('patent_annual_fees')->onDelete('cascade');
            
            // 添加索引
            $table->index(['patent_annual_fee_id']);
            $table->index(['rank']);
            $table->index(['start_year', 'end_year']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patent_annual_fee_details');
    }
}
