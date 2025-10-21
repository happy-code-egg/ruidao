<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerFollowupRecordsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户跟进记录表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_followup_records', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('跟进记录ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->bigInteger('business_opportunity_id')->nullable()->comment('商机ID（关联business_opportunities表）');
            $table->string('followup_type', 50)->comment('跟进类型：电话、上门拜访、商务洽谈等');
            $table->string('location', 200)->nullable()->comment('跟进地点');
            $table->string('contact_person', 100)->nullable()->comment('联系人');
            $table->string('contact_phone', 50)->nullable()->comment('联系电话');
            $table->text('content')->comment('跟进内容');
            $table->datetime('followup_time')->comment('跟进时间');
            $table->datetime('next_followup_time')->nullable()->comment('下次跟进时间');
            $table->string('result', 100)->nullable()->comment('跟进结果');
            $table->bigInteger('followup_person_id')->comment('跟进人员ID（关联users表）');
            $table->text('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['business_opportunity_id']);
            $table->index(['followup_person_id']);
            $table->index(['followup_time']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_followup_records');
    }
}
