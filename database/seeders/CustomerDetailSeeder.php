<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerDetailSeeder extends Seeder
{
    /**
     * 运行客户详情数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customers')->truncate();

        $now = Carbon::now();

        // 创建示例客户数据
        $customers = [
            [
                'id' => 1,
                'customer_code' => 'CUST20250101001',
                'customer_name' => '上海睿道知识产权有限公司',
                'name' => '上海睿道知识产权有限公司',
                'name_en' => 'Shanghai Ruidao Intellectual Property Co., Ltd.',
                'credit_code' => '91310115MA1FL5E91P',
                'customer_code_alias' => '91310115MA1FL5E91P',
                'customer_type' => 1,
                'customer_level' => 1,
                'level' => 'A',
                'customer_scale' => 2,
                'industry' => '知识产权服务业',
                'registered_address' => '上海市浦东新区张江高科技园区博云路2号',
                'office_address' => '上海市浦东新区张江高科技园区博云路2号',
                'legal_person' => '张三',
                'legal_representative' => '张三',
                'company_manager' => '李总',
                'contact_phone' => '021-88889999',
                'contact_name' => '王经理',
                'contact_email' => 'contact@ruidao.com',
                'email' => 'contact@ruidao.com',
                'qq' => '123456789',
                'wechat' => 'wx123456',
                'website' => 'https://www.ruidao.com',
                'business_scope' => '知识产权代理，企业管理咨询，专利代理，商标代理',
                'business_person_id' => 1,
                'business_person' => '王销售',
                'business_assistant_id' => 2,
                'business_assistant' => '王助理',
                'business_partner_id' => 3,
                'business_partner' => '张三、李四、王五',
                'company_manager_id' => 4,
                'process_staff_id' => 5,
                'source_channel' => '网络推广',
                'park_id' => null,
                'customer_status' => 1,
                'latest_contract_date' => '2024-12-15',
                'latest_contract_date_str' => '2024-12-15',
                'contract_count' => 5,
                'contract_count_str' => '5',
                'total_amount' => 150000.00,
                'remarks' => '重要客户，优质服务',
                'remark' => '重要客户，优质服务',
                'created_by' => 1,
                'updated_by' => 1,
                'creator' => '系统管理员',
                'updater' => '系统管理员',
                'create_date' => '2024-01-01',
                'create_time' => '2024-01-01 10:00:00',
                'update_time' => '2024-12-28 15:30:00',
                
                // 地址详细信息
                'country' => '中国',
                'province' => '上海市',
                'city' => '上海市',
                'district' => '浦东新区',
                'address' => '张江高科技园区博云路2号',
                'address_en' => 'No.2 Boyun Road, Zhangjiang Hi-Tech Park, Pudong New District, Shanghai',
                'other_address' => '上海市徐汇区研发中心',
                'industrial_park' => '张江高科技园区',
                'zip_code' => '201203',
                
                // 费用信息
                'account_name' => '上海睿道知识产权有限公司',
                'bank_name' => '中国工商银行上海张江支行',
                'bank_account' => '62227777888899991234',
                'invoice_address' => '上海市浦东新区张江高科技园区博云路2号',
                'invoice_phone' => '021-88889999',
                'is_general_taxpayer' => true,
                'billing_address' => '上海市浦东新区张江高科技园区博云路2号',
                'invoice_credit_code' => '91310115MA1FL5E91P',
                
                // 工商信息
                'economic_category' => 'M',
                'economic_door' => '科学研究和技术服务业',
                'economic_big_class' => '专业技术服务业',
                'economic_mid_class' => '知识产权服务',
                'economic_small_class' => '知识产权代理服务',
                'founding_date' => '2018-03-15',
                'main_products' => '知识产权代理服务，专利服务，商标服务',
                'employee_count' => '150',
                'company_staff_count' => '150',
                'registered_capital' => '1000万元',
                'research_staff_count' => '25',
                'doctor_count' => '5',
                'master_count' => '12',
                'bachelor_count' => '35',
                'overseas_returnee_count' => '3',
                'middle_engineer_count' => '15',
                'senior_engineer_count' => '8',
                
                // 知识产权信息
                'trademark_count' => 25,
                'patent_count' => 42,
                'invention_patent_count' => 18,
                'copyright_count' => 13,
                'has_additional_deduction' => true,
                'has_school_cooperation' => true,
                'cooperation_school' => '上海交通大学',
                
                // 企业资质
                'is_jinxin_verified' => '1',
                'jinxin_verify_date' => '2023-05-15',
                'is_science_verified' => '1',
                'science_verify_date' => '2023-06-15',
                'high_tech_enterprise' => true,
                'high_tech_enterprise_str' => '1',
                'high_tech_date' => '2020-09-15',
                'province_enterprise' => true,
                'province_enterprise_str' => '1',
                'province_enterprise_date' => '2019-11-20',
                'city_enterprise' => true,
                'city_enterprise_str' => '1',
                'city_enterprise_date' => '2018-07-12',
                'province_tech_center' => true,
                'province_tech_center_str' => '1',
                'province_tech_center_date' => '2022-03-15',
                'ip_standard' => true,
                'ip_standard_str' => '1',
                'ip_standard_date' => '2021-05-18',
                'it_standard' => false,
                'it_standard_str' => '0',
                'info_standard_date' => null,
                
                // 指数
                'innovation_index' => 92,
                'innovation_index_str' => '92',
                'price_index' => 85,
                'price_index_str' => '85',
                
                // 预留字段
                'spare1' => '预留字段1',
                'spare2' => '预留字段2',
                'spare3' => '预留字段3',
                'spare4' => '预留字段4',
                'spare5' => '预留字段5',
                'original_salesperson' => '李原销售',
                'public_sea_name' => '科技企业公海',
                
                // 财务数据（JSON格式）
                'sales_data' => json_encode([
                    '2021' => 1580,
                    '2022' => 1820,
                    '2023' => 2150,
                ]),
                'rd_cost_data' => json_encode([
                    '2021' => 320,
                    '2022' => 380,
                    '2023' => 450,
                ]),
                'loan_data' => json_encode([
                    '2021' => 500,
                    '2022' => 600,
                    '2023' => 800,
                ]),
                'research_project_data' => json_encode([
                    '2021' => '人工智能专利分析，商标图像识别',
                    '2022' => 'AI知识产权检索系统，智能申请系统',
                    '2023' => '区块链专利保护，智慧园区IP管理平台',
                ]),
                'project_amount_data' => json_encode([
                    '2021' => 280,
                    '2022' => 320,
                    '2023' => 380,
                ]),
                'rd_equipment_original_value_data' => json_encode([
                    '2021' => 1100,
                    '2022' => 1350,
                    '2023' => 1600,
                ]),
                'has_audit_report_data' => json_encode([
                    '2021' => '1',
                    '2022' => '1',
                    '2023' => '1',
                ]),
                'asset_liability_ratio_data' => json_encode([
                    '2021' => 38,
                    '2022' => 35,
                    '2023' => 32,
                ]),
                'fixed_asset_investment_data' => json_encode([
                    '2021' => 400,
                    '2022' => 450,
                    '2023' => 520,
                ]),
                'equipment_investment_data' => json_encode([
                    '2021' => 260,
                    '2022' => 290,
                    '2023' => 330,
                ]),
                'smart_equipment_investment_data' => json_encode([
                    '2021' => 200,
                    '2022' => 240,
                    '2023' => 280,
                ]),
                'rd_equipment_investment_data' => json_encode([
                    '2021' => 150,
                    '2022' => 180,
                    '2023' => 220,
                ]),
                'it_investment_data' => json_encode([
                    '2021' => 120,
                    '2022' => 140,
                    '2023' => 160,
                ]),
                'has_imported_equipment_data' => json_encode([
                    '2021' => '1',
                    '2022' => '1',
                    '2023' => '1',
                ]),
                'has_investment_record_data' => json_encode([
                    '2021' => '1',
                    '2022' => '1',
                    '2023' => '1',
                ]),
                'record_amount_data' => json_encode([
                    '2021' => 350,
                    '2022' => 400,
                    '2023' => 480,
                ]),
                'record_period_data' => json_encode([
                    '2021' => '2021-01-01至2021-12-31',
                    '2022' => '2022-01-01至2022-12-31',
                    '2023' => '2023-01-01至2023-12-31',
                ]),
                
                // UI相关
                'rating' => 4.5,
                'avatar' => '',
                'tags' => json_encode([]),
                'important_events' => json_encode([]),
                
                // 时间戳
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                
                // 其他字段
                'case_count' => 12,
                'customer_no' => 'CUST20250101001',
                'sales_2021' => 1580.00,
                'research_fee_2021' => 320.00,
                'loan_2021' => 500.00,
            ]
        ];

        DB::table('customers')->insert($customers);
        
        $this->command->info('客户详情数据种子已成功植入！');
    }
}
