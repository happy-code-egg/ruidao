<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerRelatedPersonsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户相关人员表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_related_persons', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('相关人员ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('person_name', 100)->comment('人员姓名');
            $table->string('person_type', 50)->comment('人员类型：技术负责人、商务负责人、财务负责人等');
            $table->string('phone', 50)->nullable()->comment('电话');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('position', 100)->nullable()->comment('职位');
            $table->string('department', 100)->nullable()->comment('部门');
            $table->string('relationship', 100)->nullable()->comment('与客户关系');
            $table->text('responsibility')->nullable()->comment('职责范围');
            $table->boolean('is_active')->default(true)->comment('是否在职');
            $table->text('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->nullable()->comment('创建人ID（关联users表）');
            $table->timestamps();
            $table->bigInteger('updated_by')->nullable()->comment('更新人ID（关联users表）');
            $table->softDeletes();

            $table->index(['customer_id']);
            $table->index(['person_type']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_related_persons');
    }
}
