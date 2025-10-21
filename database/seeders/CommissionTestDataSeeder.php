<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class CommissionTestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        // 获取现有用户
        $techUser1 = User::where('email', 'tech1@test.com')->first();
        $techUser2 = User::where('email', 'tech2@test.com')->first();
        $businessUser = User::where('email', 'business@test.com')->first();

        if (!$techUser1 || !$techUser2 || !$businessUser) {
            echo "请先运行 AssignmentTestDataSeeder 创建基础数据\n";
            return;
        }

        // 获取现有客户
        $customer1 = Customer::where('customer_name', '上海科技有限公司')->first();
        $customer2 = Customer::where('customer_name', '北京创新科技公司')->first();

        // 创建一些已完成的处理事项用于提成计算
        
        // 1. 已完成的专利案例
        $completedPatentCase = Cases::create([
            'case_code' => 'PT-2024-COMP-001',
            'case_name' => '智能机器人控制系统专利',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '发明专利',
            'case_status' => Cases::STATUS_COMPLETED,
            'application_no' => 'CN2024COMP001',
            'application_date' => Carbon::now()->subDays(90),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '已完成',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 已完成的专利处理事项
        CaseProcess::create([
            'case_id' => $completedPatentCase->id,
            'process_code' => 'COMP-001',
            'process_name' => '专利申请文件撰写',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_COMPLETED,
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => $techUser1->id,
            'reviewer' => $techUser2->id,
            'due_date' => Carbon::now()->subDays(30),
            'internal_deadline' => Carbon::now()->subDays(35),
            'official_deadline' => Carbon::now()->subDays(25),
            'completion_date' => Carbon::now()->subDays(35), // 提前5天完成
            'issue_date' => Carbon::now()->subDays(40),
            'case_stage' => '已完成',
            'process_coefficient' => 1.2, // 难度系数
            'created_by' => $businessUser->id
        ]);

        // 2. 已完成的商标案例
        $completedTrademarkCase = Cases::create([
            'case_code' => 'TM-2024-COMP-001',
            'case_name' => '智能品牌商标注册',
            'customer_id' => $customer2->id,
            'case_type' => Cases::TYPE_TRADEMARK,
            'application_type' => '商标注册',
            'case_status' => Cases::STATUS_COMPLETED,
            'application_no' => 'TM2024COMP001',
            'application_date' => Carbon::now()->subDays(80),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser2->id,
            'country_code' => '中国',
            'case_phase' => '已完成',
            'trademark_category' => '09',
            'applicant_info' => json_encode([
                'name' => '北京创新科技公司',
                'address' => '北京市海淀区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 已完成的商标处理事项
        CaseProcess::create([
            'case_id' => $completedTrademarkCase->id,
            'process_code' => 'COMP-002',
            'process_name' => '商标注册申请',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_COMPLETED,
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => $techUser2->id,
            'reviewer' => $techUser1->id,
            'due_date' => Carbon::now()->subDays(20),
            'internal_deadline' => Carbon::now()->subDays(25),
            'official_deadline' => Carbon::now()->subDays(15),
            'completion_date' => Carbon::now()->subDays(25), // 按时完成
            'issue_date' => Carbon::now()->subDays(30),
            'case_stage' => '已完成',
            'process_coefficient' => 1.0, // 标准难度
            'created_by' => $businessUser->id
        ]);

        // 3. 科技服务已完成案例
        $completedTechServiceCase = Cases::create([
            'case_code' => 'TS-2024-COMP-001',
            'case_name' => '高新技术企业认定咨询服务',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_TECH_SERVICE,
            'application_type' => '高新认定',
            'case_status' => Cases::STATUS_COMPLETED,
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '已完成',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 已完成的科技服务处理事项
        CaseProcess::create([
            'case_id' => $completedTechServiceCase->id,
            'process_code' => 'COMP-003',
            'process_name' => '高新认定材料整理',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_COMPLETED,
            'priority_level' => CaseProcess::PRIORITY_LOW,
            'assigned_to' => $techUser1->id,
            'reviewer' => $techUser2->id,
            'due_date' => Carbon::now()->subDays(10),
            'internal_deadline' => Carbon::now()->subDays(15),
            'official_deadline' => Carbon::now()->subDays(5),
            'completion_date' => Carbon::now()->subDays(60), // 提前50天完成，获得奖励
            'issue_date' => Carbon::now()->subDays(65),
            'case_stage' => '已完成',
            'process_coefficient' => 1.5, // 高难度系数
            'created_by' => $businessUser->id
        ]);

        // 4. 给techUser2再增加一些已完成的事项
        CaseProcess::create([
            'case_id' => $completedPatentCase->id,
            'process_code' => 'COMP-004',
            'process_name' => '专利申请答复',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_COMPLETED,
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => $techUser2->id,
            'reviewer' => $techUser1->id,
            'due_date' => Carbon::now()->subDays(15),
            'internal_deadline' => Carbon::now()->subDays(20),
            'official_deadline' => Carbon::now()->subDays(10),
            'completion_date' => Carbon::now()->subDays(20), // 按时完成
            'issue_date' => Carbon::now()->subDays(25),
            'case_stage' => '已完成',
            'process_coefficient' => 1.1,
            'created_by' => $businessUser->id
        ]);

        echo "提成测试数据创建完成！\n";
        echo "创建了" . Cases::where('case_status', Cases::STATUS_COMPLETED)->count() . "个已完成案例\n";
        echo "创建了" . CaseProcess::where('process_status', CaseProcess::STATUS_COMPLETED)->count() . "个已完成处理事项\n";
        echo "tech1用户完成事项：" . CaseProcess::where('assigned_to', $techUser1->id)->where('process_status', CaseProcess::STATUS_COMPLETED)->count() . "个\n";
        echo "tech2用户完成事项：" . CaseProcess::where('assigned_to', $techUser2->id)->where('process_status', CaseProcess::STATUS_COMPLETED)->count() . "个\n";
    }
}
