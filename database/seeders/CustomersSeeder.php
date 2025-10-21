<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 客户表种子数据 - 完整版本
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customers')->truncate();

        // 确保有用户数据
        $users = DB::table('users')->get();
        if ($users->isEmpty()) {
            // 创建一些测试用户
            DB::table('users')->insert([
                [
                    'name' => '张三',
                    'username' => 'zhangsan',
                    'email' => 'zhangsan@example.com',
                    'password' => bcrypt('password'),
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => '李四',
                    'username' => 'lisi',
                    'email' => 'lisi@example.com',
                    'password' => bcrypt('password'),
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => '王五',
                    'username' => 'wangwu',
                    'email' => 'wangwu@example.com',
                    'password' => bcrypt('password'),
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
            $users = DB::table('users')->get();
        }

        // 创建完整的测试客户数据
        $customers = [
            [
                // 基本信息
                'customer_code' => 'C20250810001',
                'customer_name' => '北京科技创新有限公司',
                'name' => '北京科技创新有限公司',
                'name_en' => 'Beijing Technology Innovation Co., Ltd.',
                'credit_code' => '91110000123456789A',
                'customer_type' => 1,
                'customer_level' => 1,
                'level' => 'A',
                'legal_representative' => '张科技',
                'company_manager' => '张总',
                'employee_count' => '150',
                'industry' => '软件和信息技术服务业',
                'business_person_id' => $users->first()->id,
                'business_person' => $users->first()->name,
                'business_assistant_id' => $users->skip(1)->first()->id,
                'business_assistant' => $users->skip(1)->first()->name,
                'business_partner_id' => $users->skip(2)->first()->id,
                'business_partner' => $users->skip(2)->first()->name,
                'price_index_str' => '85',
                'innovation_index_str' => '92',
                'contract_count_str' => '5',
                'latest_contract_date_str' => '2024-12-01',
                'creator' => $users->first()->name,
                'create_date' => '2024-01-15',
                'create_time' => '2024-01-15 10:30:00',
                'updater' => $users->first()->name,
                'update_time' => '2024-12-15 14:20:00',
                'remark' => '重要客户，技术实力强，与我公司合作关系良好',
                
                // 联系信息
                'contact_name' => '李联系',
                'contact_phone' => '010-12345678',
                'contact_email' => 'contact@bjkjcx.com',
                'email' => 'contact@bjkjcx.com',
                'qq' => '123456789',
                'wechat' => 'bjkjcx_tech',
                
                // 地址信息
                'country' => '中国',
                'province' => '北京',
                'city' => '北京',
                'district' => '海淀区',
                'address' => '中关村软件园1号楼A座15层',
                'address_en' => 'Floor 15, Building A1, Zhongguancun Software Park, Haidian District, Beijing',
                'other_address' => '北京市朝阳区研发分中心',
                'industrial_park' => '中关村软件园',
                'zip_code' => '100190',
                'website' => 'https://www.bjkjcx.com',
                
                // 费用信息
                'account_name' => '北京科技创新有限公司',
                'bank_name' => '中国工商银行北京中关村支行',
                'bank_account' => '0200123456789012345',
                'invoice_address' => '北京市海淀区中关村软件园1号楼A座15层',
                'invoice_phone' => '010-12345678',
                'is_general_taxpayer' => true,
                'billing_address' => '北京市海淀区中关村软件园1号楼A座15层',
                'invoice_credit_code' => '91110000123456789A',
                
                // 企业信息
                'company_type' => '有限责任公司',
                'registered_capital' => '1000万元',
                'founding_date' => '2015-03-20',
                'main_products' => '软件开发，系统集成，技术咨询',
                'business_scope' => '技术开发，技术推广，技术转让，技术咨询，技术服务；软件开发；计算机系统服务',
                
                // 工商信息
                'economic_category' => 'I',
                'economic_door' => 'I 信息传输、软件和信息技术服务业',
                'economic_big_class' => 'I65 软件和信息技术服务业',
                'economic_mid_class' => 'I651 软件开发',
                'economic_small_class' => 'I6510 基础软件开发',
                'company_staff_count' => '150',
                'research_staff_count' => '80',
                'doctor_count' => '5',
                'senior_engineer_count' => '12',
                'master_count' => '25',
                'middle_engineer_count' => '15',
                'bachelor_count' => '85',
                'overseas_returnee_count' => '8',
                
                // 知识产权信息
                'trademark_count' => 12,
                'patent_count' => 35,
                'invention_patent_count' => 15,
                'copyright_count' => 8,
                'has_additional_deduction' => true,
                'has_school_cooperation' => true,
                'cooperation_school' => '清华大学，北京理工大学',
                
                // 公司资质信息
                'is_jinxin_verified' => '1',
                'jinxin_verify_date' => '2023-05-15',
                'is_science_verified' => '1',
                'science_verify_date' => '2023-06-20',
                'high_tech_enterprise_str' => '1',
                'high_tech_date' => '2022-09-15',
                'province_enterprise_str' => '1',
                'province_enterprise_date' => '2021-11-20',
                'city_enterprise_str' => '0',
                'city_enterprise_date' => null,
                'province_tech_center_str' => '1',
                'province_tech_center_date' => '2023-03-15',
                'ip_standard_str' => '1',
                'ip_standard_date' => '2022-05-18',
                'it_standard_str' => '1',
                'info_standard_date' => '2023-08-10',
                
                // 动态年份数据（JSON格式）
                'sales_data' => json_encode([
                    '2021' => 2000,
                    '2022' => 2500,
                    '2023' => 3200
                ]),
                'rd_cost_data' => json_encode([
                    '2021' => 200,
                    '2022' => 280,
                    '2023' => 350
                ]),
                'loan_data' => json_encode([
                    '2021' => 500,
                    '2022' => 300,
                    '2023' => 0
                ]),
                'research_project_data' => json_encode([
                    '2021' => '智能数据分析平台，企业级ERP系统',
                    '2022' => '人工智能客服系统，云原生微服务架构',
                    '2023' => '区块链数据存储，量子加密通信系统'
                ]),
                'project_amount_data' => json_encode([
                    '2021' => 150,
                    '2022' => 200,
                    '2023' => 280
                ]),
                
                // 其他系统字段
                'customer_status' => 1,
                'latest_contract_date' => '2024-12-01',
                'contract_count' => 5,
                'total_amount' => 500000.00,
                'case_count' => 8,
                'customer_no' => 'KH001',
                'sales_2021' => 2000000.00,
                'research_fee_2021' => 200000.00,
                'loan_2021' => 500000.00,
                'high_tech_enterprise' => true,
                'province_enterprise' => true,
                'city_enterprise' => false,
                'province_tech_center' => true,
                'ip_standard' => true,
                'it_standard' => true,
                'innovation_index' => 1,
                'price_index' => 2,
                'rating' => 4.5,
                'avatar' => null,
                'tags' => json_encode([
                    ['id' => 1, 'name' => '高新技术企业', 'type' => 'success'],
                    ['id' => 2, 'name' => '重点客户', 'type' => 'warning'],
                    ['id' => 3, 'name' => '软件开发', 'type' => 'primary']
                ]),
                'important_events' => json_encode([
                    ['time' => '2024-12-01', 'type' => 'success', 'content' => '签署新年度服务合同'],
                    ['time' => '2024-10-15', 'type' => 'primary', 'content' => '完成项目阶段性交付'],
                    ['time' => '2024-08-20', 'type' => 'info', 'content' => '参加技术交流会议']
                ]),
                'remarks' => '重要客户，技术实力强',
                'created_by' => $users->first()->id,
                'updated_by' => $users->first()->id,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'customer_code' => 'C20250810002',
                'customer_name' => '上海智能制造股份有限公司',
                'name' => '上海智能制造股份有限公司',
                'credit_code' => '91310000987654321B',
                'customer_type' => 1,
                'customer_level' => 2,
                'level' => 'B',
                'industry' => '通用设备制造业',
                'contact_phone' => '021-87654321',
                'contact_email' => 'info@shznzz.com',
                'business_person_id' => $users->skip(1)->first()->id,
                'business_person' => $users->skip(1)->first()->name,
                'business_assistant_id' => $users->first()->id,
                'business_assistant' => $users->first()->name,
                'company_manager' => '李总',
                'customer_status' => 1,
                'latest_contract_date' => '2024-11-15',
                'contract_count' => 3,
                'total_amount' => 300000.00,
                'province' => '上海',
                'city' => '上海',
                'district' => '浦东新区',
                'case_count' => 5,
                'customer_no' => 'KH002',
                'economic_category' => 'C',
                'economic_door' => 'C 制造业',
                'economic_big_class' => 'C34 通用设备制造业',
                'economic_mid_class' => 'C341 锅炉及原动设备制造',
                'economic_small_class' => 'C3411 锅炉制造',
                'sales_2021' => 5000000.00,
                'research_fee_2021' => 300000.00,
                'loan_2021' => 1000000.00,
                'high_tech_enterprise' => false,
                'province_enterprise' => true,
                'city_enterprise' => true,
                'province_tech_center' => false,
                'ip_standard' => false,
                'it_standard' => true,
                'innovation_index' => 2,
                'price_index' => 1,
                'remarks' => '制造业客户，有发展潜力',
                'remark' => '制造业客户，有发展潜力',
                'industrial_park' => '张江高科技园区',
                'created_by' => $users->skip(1)->first()->id,
                'updated_by' => $users->skip(1)->first()->id,
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(3),
            ],
        ];

        // 直接插入数据
        foreach ($customers as $customerData) {
            \App\Models\Customer::create($customerData);
        }

        $this->command->info('客户测试数据创建完成！');
    }
}
