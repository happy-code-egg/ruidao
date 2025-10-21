<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     * 商机管理表
     * @return void
     */
    public function up()
    {
        Schema::create('business_opportunities', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('商机ID');
            $table->string('opportunity_code', 50)->unique()->comment('商机编码');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('opportunity_name', 200)->comment('商机名称');
            $table->smallInteger('opportunity_type')->nullable()->comment('商机类型：1-专利，2-商标，3-版权，4-科服');
            $table->smallInteger('opportunity_status')->default(1)->comment('商机状态：1-潜在，2-跟进中，3-报价，4-成交，5-失败');
            $table->decimal('estimated_amount', 15, 2)->nullable()->comment('预估金额');
            $table->smallInteger('probability')->nullable()->comment('成交概率（%）');
            $table->date('expected_close_date')->nullable()->comment('预计成交日期');
            $table->bigInteger('business_person_id')->nullable()->comment('负责人ID（关联users表）');
            $table->string('source_channel', 100)->nullable()->comment('来源渠道');
            $table->text('competitor_info')->nullable()->comment('竞争对手信息');
            $table->text('description')->nullable()->comment('商机描述');
            $table->text('remarks')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();
        
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_opportunities');
    }
}
