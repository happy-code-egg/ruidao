<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户申请人表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_applicants', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('申请人ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('applicant_name_cn', 200)->comment('申请人名称(中文)');
            $table->string('applicant_name_en', 200)->nullable()->comment('申请人名称(英文)');
            $table->string('applicant_code', 50)->unique()->comment('申请人编号');
            $table->string('applicant_type', 50)->nullable()->comment('申请人类型');
            $table->string('id_type', 50)->nullable()->comment('证件类型');
            $table->string('id_number', 100)->nullable()->comment('证件号');
            $table->string('country', 50)->default('中国')->comment('国籍');
            $table->string('business_location', 100)->nullable()->comment('经常经营地或营业地');
            $table->boolean('fee_reduction')->default(false)->comment('费减备案');
            $table->date('fee_reduction_start_date')->nullable()->comment('费减有效期开始日期');
            $table->date('fee_reduction_end_date')->nullable()->comment('费减有效期结束日期');
            $table->string('province', 50)->nullable()->comment('省');
            $table->string('city', 50)->nullable()->comment('市');
            $table->string('district', 50)->nullable()->comment('区');
            $table->string('street', 200)->nullable()->comment('街道');
            $table->string('postal_code', 20)->nullable()->comment('邮编');
            $table->string('entity_type', 50)->nullable()->comment('实体类型');
            $table->string('address_en', 500)->nullable()->comment('地址(英文)');
            $table->string('total_condition_no', 100)->nullable()->comment('总委托书编号');
            $table->date('sync_date')->nullable()->comment('同步日期');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('phone', 50)->nullable()->comment('电话');
            $table->text('inventor_note')->nullable()->comment('发明人备注');
            $table->text('remark')->nullable()->comment('申请人备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['applicant_name_cn']);
            $table->index(['country']);
            $table->index(['applicant_type']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_applicants');
    }
}
