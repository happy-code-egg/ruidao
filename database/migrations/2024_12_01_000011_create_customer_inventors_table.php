<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerInventorsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户发明人表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_inventors', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('发明人ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('inventor_name_cn', 100)->comment('发明人姓名(中文)');
            $table->string('inventor_name_en', 100)->nullable()->comment('发明人姓名(英文)');
            $table->string('inventor_code', 50)->unique()->comment('发明人编号');
            $table->string('gender', 10)->nullable()->comment('性别');
            $table->string('id_type', 50)->nullable()->comment('证件类型');
            $table->string('id_number', 100)->nullable()->comment('证件号');
            $table->string('country', 50)->default('中国')->comment('国籍');
            $table->string('province', 50)->nullable()->comment('省');
            $table->string('city', 50)->nullable()->comment('市');
            $table->string('district', 50)->nullable()->comment('区');
            $table->string('street', 200)->nullable()->comment('街道');
            $table->string('postal_code', 20)->nullable()->comment('邮编');
            $table->text('address')->nullable()->comment('详细地址');
            $table->string('address_en', 500)->nullable()->comment('地址(英文)');
            $table->string('phone', 50)->nullable()->comment('电话');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('work_unit', 200)->nullable()->comment('工作单位');
            $table->string('department', 100)->nullable()->comment('部门');
            $table->string('position', 100)->nullable()->comment('职位');
            $table->text('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['inventor_name_cn']);
            $table->index(['country']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_inventors');
    }
}
