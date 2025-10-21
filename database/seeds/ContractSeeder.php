<?php

use Illuminate\Database\Seeder;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 创建合同模拟数据
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        Contract::truncate();

        // 确保有客户数据
        $customers = Customer::all();
        if ($customers->isEmpty()) {
            // 创建一些客户数据
            $customers = collect([
                Customer::create([
                    'customer_name' => '北京科技有限公司',
                    'customer_type' => '企业',
                    'contact_person' => '张经理',
                    'contact_phone' => '13800138001',
                    'contact_email' => 'zhang@example.com',
                    'address' => '北京市海淀区中关村大街1号',
                    'created_by' => 1,
                    'updated_by' => 1,
                ]),
                Customer::create([
                    'customer_name' => '上海创新科技公司',
                    'customer_type' => '企业',
                    'contact_person' => '李总',
                    'contact_phone' => '13800138002',
                    'contact_email' => 'li@example.com',
                    'address' => '上海市浦东新区张江高科技园区',
                    'created_by' => 1,
                    'updated_by' => 1,
                ]),
                Customer::create([
                    'customer_name' => '深圳智能制造有限公司',
                    'customer_type' => '企业',
                    'contact_person' => '王主任',
                    'contact_phone' => '13800138003',
                    'contact_email' => 'wang@example.com',
                    'address' => '深圳市南山区科技园',
                    'created_by' => 1,
                    'updated_by' => 1,
                ]),
            ]);
        }

        // 确保有用户数据
        $users = User::all();
        if ($users->isEmpty()) {
            $users = collect([
                User::create([
                    'name' => '业务员01',
                    'email' => 'sales01@example.com',
                    'password' => bcrypt('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
                User::create([
                    'name' => '技术主导01',
                    'email' => 'tech01@example.com',
                    'password' => bcrypt('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            ]);
        }

        // 业务服务类型
        $serviceTypes = [
            '商标注册',
            '专利申请',
            '版权登记',
            '商标转让',
            '专利维权',
            '商标变更',
            '商标续展',
            '发明专利',
            '实用新型',
            '外观设计'
        ];

        // 合同状态
        $statuses = ['草稿', '审批中', '已完成', '已终止'];

        // 乙方签约公司
        $partyBCompanies = [
            '睿道知识产权代理有限公司',
            '北京睿道科技有限公司'
        ];

        // 创建20条合同数据
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $businessPerson = $users->random();
            $technicalDirector = $users->random();

            $serviceFee = rand(5000, 50000);
            $officialFee = rand(1000, 10000);
            $totalAmount = $serviceFee + $officialFee;

            $signingDate = Carbon::now()->subDays(rand(1, 365));

            Contract::create([
                'contract_no' => 'HT-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'contract_code' => 'CODE' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'contract_name' => $serviceTypes[array_rand($serviceTypes)] . '服务合同' . $i,
                'customer_id' => $customer->id,
                'service_type' => $serviceTypes[array_rand($serviceTypes)],
                'status' => $statuses[array_rand($statuses)],
                'summary' => '这是一份关于' . $serviceTypes[array_rand($serviceTypes)] . '的合同，包含知识产权相关条款及服务内容约定。',
                'business_person_id' => $businessPerson->id,
                'technical_director_id' => $technicalDirector->id,
                'technical_department' => '技术部' . rand(1, 5) . '组',
                'paper_status' => rand(0, 1) ? true : false,
                'party_a_phone' => '1' . rand(3, 8) . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'party_a_email' => 'client' . $i . '@example.com',
                'party_a_address' => '北京市' . ['海淀区', '朝阳区', '西城区', '东城区'][array_rand(['海淀区', '朝阳区', '西城区', '东城区'])] . '某某路' . rand(1, 100) . '号',
                'party_b_signer' => '签约人' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'party_b_phone' => '1' . rand(3, 8) . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'party_b_company' => $partyBCompanies[array_rand($partyBCompanies)],
                'party_b_address' => '北京市海淀区知春路' . rand(1, 100) . '号',
                'service_fee' => $serviceFee,
                'official_fee' => $officialFee,
                'channel_fee' => 0,
                'total_service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'case_count' => rand(1, 10),
                'opportunity_no' => 'SJ' . str_pad(2000 + $i, 4, '0', STR_PAD_LEFT),
                'opportunity_name' => '商机' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'currency' => 'CNY',
                'signing_date' => $signingDate->format('Y-m-d'),
                'validity_start_date' => $signingDate->format('Y-m-d'),
                'validity_end_date' => $signingDate->addYear()->format('Y-m-d'),
                'additional_terms' => '特殊条款与约定',
                'remark' => '合同备注信息',
                'last_process_time' => $signingDate->addDays(rand(1, 30)),
                'process_remark' => '流程处理记录',
                'created_by' => $businessPerson->id,
                'updated_by' => $businessPerson->id,
                'created_at' => $signingDate,
                'updated_at' => $signingDate->addDays(1),
            ]);
        }

        echo "合同数据创建完成，共创建了20条记录\n";
    }
}
