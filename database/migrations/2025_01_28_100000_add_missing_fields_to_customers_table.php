<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     * 为客户表添加前端需要的缺失字段
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // 基础信息字段
            $table->string('name', 200)->nullable()->comment('客户名称（前端字段别名）');
            $table->string('name_en', 200)->nullable()->comment('客户英文名称');
            $table->string('customer_code_alias', 50)->nullable()->comment('信用代码（前端字段别名）');
            $table->string('legal_representative', 100)->nullable()->comment('法定代表人');
            $table->string('company_manager', 100)->nullable()->comment('公司负责人');
            $table->string('level', 10)->nullable()->comment('客户等级（前端字符串格式）');
            $table->string('employee_count', 50)->nullable()->comment('公司人数');
            $table->string('business_person', 100)->nullable()->comment('业务人员（前端字符串）');
            $table->string('business_assistant', 100)->nullable()->comment('业务助理（前端字符串）');
            $table->string('business_partner', 200)->nullable()->comment('业务协作人（前端字符串）');
            $table->string('price_index_str', 10)->nullable()->comment('价格指数字符串');
            $table->string('innovation_index_str', 10)->nullable()->comment('创新指数字符串');
            $table->string('contract_count_str', 10)->nullable()->comment('合同数量字符串');
            $table->string('latest_contract_date_str', 50)->nullable()->comment('最新合同日期字符串');
            $table->string('creator', 100)->nullable()->comment('创建人姓名');
            $table->string('create_date', 20)->nullable()->comment('创建日期');
            $table->string('create_time', 30)->nullable()->comment('创建时间');
            $table->string('updater', 100)->nullable()->comment('更新人姓名');
            $table->string('update_time', 30)->nullable()->comment('更新时间');
            $table->text('remark')->nullable()->comment('客户资料备注');

            // 联系信息
            $table->string('contact_name', 100)->nullable()->comment('联系人姓名');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('qq', 50)->nullable()->comment('QQ号');
            $table->string('wechat', 50)->nullable()->comment('微信号');

            // 地址详细信息
            $table->string('country', 50)->default('中国')->comment('国籍');
            $table->text('address')->nullable()->comment('街道地址');
            $table->string('address_en', 500)->nullable()->comment('英文地址');
            $table->string('other_address', 500)->nullable()->comment('其它地址');
            $table->string('industrial_park', 100)->nullable()->comment('产业园区');
            $table->string('zip_code', 20)->nullable()->comment('邮政编码');

            // 费用信息
            $table->string('account_name', 200)->nullable()->comment('开户名称');
            $table->string('bank_name', 200)->nullable()->comment('开户银行');
            $table->string('bank_account', 100)->nullable()->comment('银行账号');
            $table->text('invoice_address')->nullable()->comment('开票地址');
            $table->string('invoice_phone', 50)->nullable()->comment('开票电话');
            $table->boolean('is_general_taxpayer')->default(false)->comment('一般纳税人');
            $table->text('billing_address')->nullable()->comment('账单地址');
            $table->string('invoice_credit_code', 50)->nullable()->comment('开票信用代码');

            // 工商信息
            $table->date('founding_date')->nullable()->comment('成立时间');
            $table->string('main_products', 500)->nullable()->comment('主营业务产品');
            $table->string('company_staff_count', 50)->nullable()->comment('公司人数（工商信息）');
            $table->string('registered_capital', 50)->nullable()->comment('注册资本');
            $table->string('research_staff_count', 50)->nullable()->comment('研发人数');
            $table->string('doctor_count', 50)->nullable()->comment('博士人数');
            $table->string('master_count', 50)->nullable()->comment('硕士人数');
            $table->string('bachelor_count', 50)->nullable()->comment('本科人数');
            $table->string('overseas_returnee_count', 50)->nullable()->comment('海归人数');
            $table->string('middle_engineer_count', 50)->nullable()->comment('中工人数');
            $table->string('senior_engineer_count', 50)->nullable()->comment('高工人数');

            // 知识产权信息
            $table->integer('trademark_count')->default(0)->comment('商标数量');
            $table->integer('patent_count')->default(0)->comment('专利数量');
            $table->integer('invention_patent_count')->default(0)->comment('授权发明专利数量');
            $table->integer('copyright_count')->default(0)->comment('版权登记数量');
            $table->boolean('has_additional_deduction')->nullable()->comment('是否享受过加计扣除');
            $table->boolean('has_school_cooperation')->nullable()->comment('是否有校企合作');
            $table->string('cooperation_school', 200)->nullable()->comment('合作高校名称');

            // 资质认定时间字段
            $table->string('is_jinxin_verified', 10)->default('0')->comment('经信口是否验收');
            $table->string('jinxin_verify_date', 20)->nullable()->comment('经信口验收时间');
            $table->string('is_science_verified', 10)->default('0')->comment('科技口是否验收');
            $table->string('science_verify_date', 20)->nullable()->comment('科技口验收时间');
            $table->string('high_tech_enterprise_str', 10)->default('0')->comment('高企');
            $table->string('high_tech_date', 20)->nullable()->comment('高企认定时间');
            $table->string('province_enterprise_str', 10)->default('0')->comment('省企');
            $table->string('province_enterprise_date', 20)->nullable()->comment('省企认定时间');
            $table->string('city_enterprise_str', 10)->default('0')->comment('市企');
            $table->string('city_enterprise_date', 20)->nullable()->comment('市企认定时间');
            $table->string('province_tech_center_str', 10)->default('0')->comment('省级工程技术中心');
            $table->string('province_tech_center_date', 20)->nullable()->comment('省级工程技术中心认定时间');
            $table->string('ip_standard_str', 10)->default('0')->comment('知识产权贯标');
            $table->string('ip_standard_date', 20)->nullable()->comment('知识产权贯标认定时间');
            $table->string('it_standard_str', 10)->default('0')->comment('两化融合贯标');
            $table->string('info_standard_date', 20)->nullable()->comment('两化融合贯标认定时间');

            // 预留字段
            $table->string('spare1', 500)->nullable()->comment('预留字段1');
            $table->string('spare2', 500)->nullable()->comment('预留字段2');
            $table->string('spare3', 500)->nullable()->comment('预留字段3');
            $table->string('spare4', 500)->nullable()->comment('预留字段4');
            $table->string('spare5', 500)->nullable()->comment('预留字段5');
            $table->string('original_salesperson', 100)->nullable()->comment('原销售');
            $table->string('public_sea_name', 100)->nullable()->comment('公海名称');

            // 动态年份数据（JSON格式存储）
            $table->json('sales_data')->nullable()->comment('年度销售额数据');
            $table->json('rd_cost_data')->nullable()->comment('研发费用数据');
            $table->json('loan_data')->nullable()->comment('贷款数据');
            $table->json('research_project_data')->nullable()->comment('研发项目数据');
            $table->json('project_amount_data')->nullable()->comment('项目金额数据');
            $table->json('rd_equipment_original_value_data')->nullable()->comment('研发设备原值数据');
            $table->json('has_audit_report_data')->nullable()->comment('是否有审计报告数据');
            $table->json('asset_liability_ratio_data')->nullable()->comment('资产负债率数据');
            $table->json('fixed_asset_investment_data')->nullable()->comment('固定资产投资数据');
            $table->json('equipment_investment_data')->nullable()->comment('设备投资数据');
            $table->json('smart_equipment_investment_data')->nullable()->comment('智能设备投资数据');
            $table->json('rd_equipment_investment_data')->nullable()->comment('研发设备投资数据');
            $table->json('it_investment_data')->nullable()->comment('信息化投资数据');
            $table->json('has_imported_equipment_data')->nullable()->comment('是否进口设备数据');
            $table->json('has_investment_record_data')->nullable()->comment('是否有投资备案表数据');
            $table->json('record_amount_data')->nullable()->comment('备案金额数据');
            $table->json('record_period_data')->nullable()->comment('备案起止时间数据');

            // 评分和状态
            $table->decimal('rating', 2, 1)->default(0.0)->comment('客户评级');
            $table->string('avatar', 500)->nullable()->comment('客户头像');
            $table->json('tags')->nullable()->comment('标签信息');
            $table->json('important_events')->nullable()->comment('重要事件');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'name_en', 'customer_code_alias', 'legal_representative', 'company_manager',
                'level', 'employee_count', 'business_person', 'business_assistant', 'business_partner',
                'price_index_str', 'innovation_index_str', 'contract_count_str', 'latest_contract_date_str',
                'creator', 'create_date', 'create_time', 'updater', 'update_time', 'remark',
                'contact_name', 'email', 'qq', 'wechat',
                'country', 'address', 'address_en', 'other_address', 'industrial_park', 'zip_code',
                'account_name', 'bank_name', 'bank_account', 'invoice_address', 'invoice_phone',
                'is_general_taxpayer', 'billing_address', 'invoice_credit_code',
                'founding_date', 'main_products', 'company_staff_count', 'registered_capital',
                'research_staff_count', 'doctor_count', 'master_count', 'bachelor_count',
                'overseas_returnee_count', 'middle_engineer_count', 'senior_engineer_count',
                'trademark_count', 'patent_count', 'invention_patent_count', 'copyright_count',
                'has_additional_deduction', 'has_school_cooperation', 'cooperation_school',
                'is_jinxin_verified', 'jinxin_verify_date', 'is_science_verified', 'science_verify_date',
                'high_tech_enterprise_str', 'high_tech_date', 'province_enterprise_str', 'province_enterprise_date',
                'city_enterprise_str', 'city_enterprise_date', 'province_tech_center_str', 'province_tech_center_date',
                'ip_standard_str', 'ip_standard_date', 'it_standard_str', 'info_standard_date',
                'spare1', 'spare2', 'spare3', 'spare4', 'spare5', 'original_salesperson', 'public_sea_name',
                'sales_data', 'rd_cost_data', 'loan_data', 'research_project_data', 'project_amount_data',
                'rd_equipment_original_value_data', 'has_audit_report_data', 'asset_liability_ratio_data',
                'fixed_asset_investment_data', 'equipment_investment_data', 'smart_equipment_investment_data',
                'rd_equipment_investment_data', 'it_investment_data', 'has_imported_equipment_data',
                'has_investment_record_data', 'record_amount_data', 'record_period_data',
                'rating', 'avatar', 'tags', 'important_events'
            ]);
        });
    }
}
