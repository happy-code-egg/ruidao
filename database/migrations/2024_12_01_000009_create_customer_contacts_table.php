<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerContactsTable extends Migration
{
    /**
     * Run the migrations.
     * 客户联系人表
     * @return void
     */
    public function up()
    {
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('联系人ID');
            $table->bigInteger('customer_id')->comment('客户ID（关联customers表）');
            $table->string('contact_name', 50)->comment('联系人姓名');
            $table->smallInteger('contact_type')->nullable()->comment('联系人类型：1-经办人，2-技术人员，3-财务人员，4-IPR，5-发明人');
            $table->string('position', 50)->nullable()->comment('职位');
            $table->string('phone', 50)->nullable()->comment('手机号');
            $table->string('telephone', 50)->nullable()->comment('固定电话');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('wechat', 50)->nullable()->comment('微信号');
            $table->string('qq', 20)->nullable()->comment('QQ号');
            $table->text('address')->nullable()->comment('地址');
            $table->string('id_card', 20)->nullable()->comment('身份证号');
            $table->smallInteger('is_primary')->default(0)->comment('是否主要联系人：0-否，1-是');
            $table->smallInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
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
        Schema::dropIfExists('customer_contacts');
    }
}
