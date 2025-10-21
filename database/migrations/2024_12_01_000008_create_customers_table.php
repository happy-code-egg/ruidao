<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     * 客户表
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('客户ID');
            $table->string('customer_code', 50)->unique()->comment('客户编码');
            $table->string('customer_name', 200)->comment('客户名称');
            $table->string('credit_code', 50)->nullable()->comment('统一社会信用代码');
            $table->smallInteger('customer_type')->nullable()->comment('客户类型');
            $table->smallInteger('customer_level')->nullable()->comment('客户等级');
            $table->smallInteger('customer_scale')->nullable()->comment('客户规模');
            $table->string('industry', 100)->nullable()->comment('所属行业');
            $table->text('registered_address')->nullable()->comment('注册地址');
            $table->text('office_address')->nullable()->comment('办公地址');
            $table->string('legal_person', 50)->nullable()->comment('法定代表人');
            $table->string('contact_phone', 50)->nullable()->comment('联系电话');
            $table->string('contact_email', 100)->nullable()->comment('联系邮箱');
            $table->string('website', 200)->nullable()->comment('公司网站');
            $table->text('business_scope')->nullable()->comment('经营范围');
            $table->bigInteger('business_person_id')->nullable()->comment('业务人员ID（关联users表）');
            $table->bigInteger('business_assistant_id')->nullable()->comment('业务助理ID（关联users表）');
            $table->bigInteger('business_partner_id')->nullable()->comment('业务协作人ID（关联users表）');
            $table->bigInteger('company_manager_id')->nullable()->comment('公司负责人ID（关联users表）');
            $table->string('source_channel', 100)->nullable()->comment('客户来源渠道');
            $table->bigInteger('park_id')->nullable()->comment('园区ID（关联parks表）');
            $table->smallInteger('customer_status')->default(1)->comment('客户状态');
            $table->date('latest_contract_date')->nullable()->comment('最新合同日期');
            $table->integer('contract_count')->default(0)->comment('合同数量');
            $table->decimal('total_amount', 15, 2)->default(0.00)->comment('累计合同金额');
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
        Schema::dropIfExists('customers');
    }
}
