<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerContractsSeeder extends Seeder
{
    /**
     * 运行客户合同数据种子
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        DB::table('customer_contracts')->truncate();

        $now = Carbon::now();

        // 创建示例合同数据
        $contracts = [
            [
                'id' => 1,
                'customer_id' => 1,
                'business_opportunity_id' => null,
                'contract_no' => 'CON20240101001',
                'contract_name' => '知识产权代理服务合同',
                'contract_amount' => 50000.00,
                'sign_date' => '2024-01-15',
                'start_date' => '2024-01-15',
                'end_date' => '2024-12-31',
                'contract_type' => '代理服务合同',
                'status' => '执行中',
                'business_person_id' => 1,
                'contract_content' => '为客户提供专利申请、商标注册等知识产权代理服务',
                'payment_method' => '分期付款',
                'paid_amount' => 30000.00,
                'unpaid_amount' => 20000.00,
                'remark' => '重点合同，需要优先处理',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'business_opportunity_id' => null,
                'contract_no' => 'CON20240301001',
                'contract_name' => '专利申请代理合同',
                'contract_amount' => 25000.00,
                'sign_date' => '2024-03-10',
                'start_date' => '2024-03-10',
                'end_date' => '2024-09-10',
                'contract_type' => '专利代理合同',
                'status' => '执行中',
                'business_person_id' => 1,
                'contract_content' => '提供发明专利申请代理服务，包含10项发明专利申请',
                'payment_method' => '按阶段付款',
                'paid_amount' => 15000.00,
                'unpaid_amount' => 10000.00,
                'remark' => 'AI相关专利申请项目',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'customer_id' => 1,
                'business_opportunity_id' => null,
                'contract_no' => 'CON20240601001',
                'contract_name' => '商标注册代理合同',
                'contract_amount' => 15000.00,
                'sign_date' => '2024-06-20',
                'start_date' => '2024-06-20',
                'end_date' => '2025-06-20',
                'contract_type' => '商标代理合同',
                'status' => '执行中',
                'business_person_id' => 1,
                'contract_content' => '提供商标注册代理服务，包含5个商标的注册申请',
                'payment_method' => '一次性付清',
                'paid_amount' => 15000.00,
                'unpaid_amount' => 0.00,
                'remark' => '品牌建设项目，优先级高',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'customer_id' => 1,
                'business_opportunity_id' => null,
                'contract_no' => 'CON20241201001',
                'contract_name' => '知识产权顾问服务合同',
                'contract_amount' => 80000.00,
                'sign_date' => '2024-12-01',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'contract_type' => '顾问服务合同',
                'status' => '执行中',
                'business_person_id' => 1,
                'contract_content' => '提供全年度知识产权顾问服务，包含战略规划、申请指导、风险评估等',
                'payment_method' => '按月支付',
                'paid_amount' => 6666.67,
                'unpaid_amount' => 73333.33,
                'remark' => '年度战略合作，长期服务合同',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('customer_contracts')->insert($contracts);
        
        $this->command->info('客户合同数据种子已成功植入！');
    }
}
