<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     * 合同表 - 根据前端页面需求重新设计
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('合同ID');

            // 基本信息
            $table->string('contract_no', 50)->unique()->comment('合同编号');
            $table->string('contract_code', 50)->nullable()->comment('合同代码');
            $table->string('contract_name', 200)->comment('合同名称');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->json('service_type')->nullable()->comment('业务服务类型');
            $table->string('status', 20)->default('草稿')->comment('合同状态');
            $table->text('summary')->nullable()->comment('合同摘要');
            $table->string('contract_type', 20)->default('standard')->comment('合同类型：standard=标准合同，non-standard=非标合同');



            // 人员信息
            $table->bigInteger('business_person_id')->nullable()->comment('业务人员ID（关联users表）');
            $table->bigInteger('technical_director_id')->nullable()->comment('技术主导ID（关联users表）');
            $table->string('technical_department', 100)->nullable()->comment('技术主导部门');
            $table->boolean('paper_status')->default(false)->comment('纸件状态');

            // 甲方信息
            $table->bigInteger('party_a_contact_id')->nullable()->comment('甲方联系人ID（关联customer_contacts表）');
            $table->string('party_a_phone', 20)->nullable()->comment('甲方电话');
            $table->string('party_a_email', 100)->nullable()->comment('甲方邮箱');
            $table->text('party_a_address')->nullable()->comment('甲方签约地址');

            // 乙方信息
            $table->string('party_b_signer', 50)->nullable()->comment('乙方签约人');
            $table->string('party_b_phone', 20)->nullable()->comment('乙方手机');
            $table->string('party_b_company', 100)->nullable()->comment('乙方签约公司');
            $table->text('party_b_address')->nullable()->comment('乙方签约地址');

            // 金额信息
            $table->decimal('service_fee', 15, 2)->default(0.00)->comment('服务费');
            $table->decimal('official_fee', 15, 2)->default(0.00)->comment('官费');
            $table->decimal('channel_fee', 15, 2)->default(0.00)->comment('渠道费');
            $table->decimal('total_service_fee', 15, 2)->default(0.00)->comment('总服务费');
            $table->decimal('total_amount', 15, 2)->default(0.00)->comment('总金额');
            $table->string('currency', 10)->default('CNY')->comment('币种');

            // 日期信息
            $table->date('signing_date')->nullable()->comment('签约日期');
            $table->date('validity_start_date')->nullable()->comment('合同有效期开始日期');
            $table->date('validity_end_date')->nullable()->comment('合同有效期结束日期');

            // 其他信息
            $table->text('additional_terms')->nullable()->comment('附加条款');
            $table->text('remark')->nullable()->comment('合同备注');

            // 审计字段
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['customer_id']);
            $table->index(['business_person_id']);
            $table->index(['technical_director_id']);
            $table->index(['status']);
            $table->index(['signing_date']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
