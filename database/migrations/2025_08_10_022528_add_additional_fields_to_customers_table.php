<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 地址信息
            $table->string('province', 50)->nullable()->comment('省份');
            $table->string('city', 50)->nullable()->comment('城市');
            $table->string('district', 50)->nullable()->comment('区县');
            
            // 项目相关
            $table->integer('case_count')->default(0)->comment('委案数量');
            $table->string('customer_no', 50)->nullable()->comment('客户编号');
            $table->bigInteger('process_staff_id')->nullable()->comment('流程人员ID（关联users表）');
            
            // 国民经济行业分类
            $table->string('economic_category', 10)->nullable()->comment('国民经济行业分类');
            $table->string('economic_door', 100)->nullable()->comment('国民经济行业分类门类');
            $table->string('economic_big_class', 100)->nullable()->comment('国民经济行业分类大类');
            $table->string('economic_mid_class', 100)->nullable()->comment('国民经济行业分类中类');
            $table->string('economic_small_class', 100)->nullable()->comment('国民经济行业分类小类');
            
            // 2021年度数据
            $table->decimal('sales_2021', 15, 2)->nullable()->comment('2021年度销售额');
            $table->decimal('research_fee_2021', 15, 2)->nullable()->comment('2021年研发费用');
            $table->decimal('loan_2021', 15, 2)->nullable()->comment('2021贷款');
            
            // 企业认证状态
            $table->boolean('high_tech_enterprise')->default(false)->comment('高新技术企业');
            $table->boolean('province_enterprise')->default(false)->comment('省级企业');
            $table->boolean('city_enterprise')->default(false)->comment('市级企业');
            $table->boolean('province_tech_center')->default(false)->comment('省级工程技术中心');
            $table->boolean('ip_standard')->default(false)->comment('知识产权贯标');
            $table->boolean('it_standard')->default(false)->comment('两化融合贯标');
            
            // 指数
            $table->smallInteger('innovation_index')->nullable()->comment('创新指数：1-高，2-中，3-低');
            $table->smallInteger('price_index')->nullable()->comment('价格指数：1-高，2-中，3-低');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 删除添加的字段
            $table->dropColumn([
                'province',
                'city', 
                'district',
                'case_count',
                'customer_no',
                'process_staff_id',
                'economic_category',
                'economic_door',
                'economic_big_class', 
                'economic_mid_class',
                'economic_small_class',
                'sales_2021',
                'research_fee_2021',
                'loan_2021',
                'high_tech_enterprise',
                'province_enterprise',
                'city_enterprise',
                'province_tech_center',
                'ip_standard',
                'it_standard',
                'innovation_index',
                'price_index'
            ]);
        });
    }
}
