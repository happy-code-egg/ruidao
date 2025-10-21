<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerInventorsTableAddMissingFields extends Migration
{
    /**
     * Run the migrations.
     * 为客户发明人表添加缺少的字段
     * @return void
     */
    public function up()
    {
        Schema::table('customer_inventors', function (Blueprint $table) {
            // 修改业务人员字段为关联用户表ID
            $table->dropColumn('business_staff');
            $table->bigInteger('business_staff_id')->nullable()->after('remark')->comment('业务人员ID（关联users表）');
        
            
            // 添加索引
            $table->index(['business_staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customer_inventors', function (Blueprint $table) {
            $table->dropIndex(['business_staff_id']);
            $table->dropColumn(['business_staff_id']);
            $table->string('business_staff', 50)->nullable()->comment('业务人员');
        });
    }
}
