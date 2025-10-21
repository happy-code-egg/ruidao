<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerApplicantsTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     * 更新客户申请人表以匹配前端需求
     * @return void
     */
    public function up()
    {
        Schema::table('customer_applicants', function (Blueprint $table) {
            // 添加缺少的字段
            $table->string('business_staff', 50)->nullable()->comment('业务人员');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_applicants', function (Blueprint $table) {
            $table->dropColumn(['business_staff']);
        });
    }
}
