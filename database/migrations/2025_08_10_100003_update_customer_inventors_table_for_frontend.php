<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerInventorsTableForFrontend extends Migration
{
    /**
     * Run the migrations.
     * 更新客户发明人表以匹配前端需求
     * @return void
     */
    public function up()
    {
        Schema::table('customer_inventors', function (Blueprint $table) {
            // 添加缺少的字段
            $table->string('inventor_type', 50)->nullable()->comment('发明人类型');
            $table->string('landline', 50)->nullable()->comment('座机');
            $table->string('wechat', 50)->nullable()->comment('微信号');
            $table->string('business_staff', 50)->nullable()->comment('业务人员');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_inventors', function (Blueprint $table) {
            $table->dropColumn([
                'inventor_type',
                'landline',
                'wechat',
                'business_staff'
            ]);
        });
    }
}
