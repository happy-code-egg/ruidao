<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\CaseFee;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class MonitorTestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run()
    {
        // 创建测试用户
        $businessUser = User::firstOrCreate(
            ['email' => 'business@test.com'],
            [
                'name' => '张三',
                'password' => bcrypt('password'),
                'role' => 'business'
            ]
        );

        $techUser = User::firstOrCreate(
            ['email' => 'tech@test.com'],
            [
                'name' => '李工',
                'password' => bcrypt('password'),
                'role' => 'tech'
            ]
        );

        // 创建测试客户
        $customer = Customer::firstOrCreate(
            ['company_name' => '上海科技有限公司'],
            [
                'contact_person' => '王总',
                'phone' => '13800138000',
                'email' => 'customer@test.com',
                'address' => '上海市浦东新区'
            ]
        );

        // 创建测试案例
        $case = Cases::create([
            'case_code' => 'TM2024-001',
            'case_name' => '智能感应装置专利',
            'customer_id' => $customer->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '发明专利',
            'case_status' => Cases::STATUS_PROCESSING,
            'application_no' => 'CN202410001',
            'application_date' => Carbon::now()->subDays(30),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser->id,
            'country_code' => '中国',
            'case_phase' => '申请阶段',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 创建处理事项
        CaseProcess::create([
            'case_id' => $case->id,
            'process_code' => 'PROC-001',
            'process_name' => '申请',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => $techUser->id,
            'due_date' => Carbon::now()->addDays(15),
            'internal_deadline' => Carbon::now()->addDays(10),
            'official_deadline' => Carbon::now()->addDays(20),
            'customer_deadline' => Carbon::now()->addDays(18),
            'issue_date' => Carbon::now()->subDays(5),
            'case_stage' => '申请阶段',
            'created_by' => $businessUser->id
        ]);

        // 创建费用记录
        CaseFee::create([
            'case_id' => $case->id,
            'fee_type' => CaseFee::TYPE_APPLICATION,
            'fee_name' => '申请费',
            'fee_description' => '发明专利申请费',
            'amount' => 1500.00,
            'currency' => 'CNY',
            'payment_deadline' => Carbon::now()->addDays(30),
            'receivable_date' => Carbon::now()->addDays(25),
            'payment_status' => CaseFee::STATUS_UNPAID,
            'is_reduction' => false,
            'remarks' => '标准申请费'
        ]);

        // 创建更多测试数据
        $this->createMoreTestData($businessUser, $techUser, $customer);
    }

    private function createMoreTestData($businessUser, $techUser, $customer)
    {
        // 商标案例
        $trademarkCase = Cases::create([
            'case_code' => 'TM2024-002',
            'case_name' => '创新LOGO商标',
            'customer_id' => $customer->id,
            'case_type' => Cases::TYPE_TRADEMARK,
            'application_type' => '商标注册',
            'case_status' => Cases::STATUS_SUBMITTED,
            'registration_no' => 'TMREG002',
            'application_date' => Carbon::now()->subDays(60),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser->id,
            'country_code' => '中国',
            'case_phase' => '注册阶段',
            'trademark_category' => '42',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 商标处理事项
        CaseProcess::create([
            'case_id' => $trademarkCase->id,
            'process_code' => 'PROC-002',
            'process_name' => '注册',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_PROCESSING,
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => $techUser->id,
            'due_date' => Carbon::now()->addDays(10),
            'internal_deadline' => Carbon::now()->addDays(7),
            'official_deadline' => Carbon::now()->addDays(15),
            'customer_deadline' => Carbon::now()->addDays(12),
            'issue_date' => Carbon::now()->subDays(10),
            'case_stage' => '注册阶段',
            'created_by' => $businessUser->id
        ]);

        // 商标费用
        CaseFee::create([
            'case_id' => $trademarkCase->id,
            'fee_type' => CaseFee::TYPE_REGISTRATION,
            'fee_name' => '商标注册费',
            'fee_description' => '商标注册官费',
            'amount' => 800.00,
            'currency' => 'CNY',
            'payment_deadline' => Carbon::now()->addDays(15),
            'receivable_date' => Carbon::now()->addDays(10),
            'actual_receive_date' => Carbon::now()->subDays(5),
            'payment_status' => CaseFee::STATUS_PAID,
            'is_reduction' => false,
            'remarks' => '已缴费'
        ]);

        // 版权案例
        $copyrightCase = Cases::create([
            'case_code' => 'CR2024-003',
            'case_name' => '软件著作权登记',
            'customer_id' => $customer->id,
            'case_type' => Cases::TYPE_COPYRIGHT,
            'application_type' => '软件著作权',
            'case_status' => Cases::STATUS_COMPLETED,
            'application_no' => 'CR202410003',
            'application_date' => Carbon::now()->subDays(90),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser->id,
            'country_code' => '中国',
            'case_phase' => '已完成',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 版权处理事项
        CaseProcess::create([
            'case_id' => $copyrightCase->id,
            'process_code' => 'PROC-003',
            'process_name' => '登记',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_COMPLETED,
            'priority_level' => CaseProcess::PRIORITY_LOW,
            'assigned_to' => $techUser->id,
            'due_date' => Carbon::now()->subDays(30),
            'internal_deadline' => Carbon::now()->subDays(35),
            'official_deadline' => Carbon::now()->subDays(25),
            'customer_deadline' => Carbon::now()->subDays(28),
            'issue_date' => Carbon::now()->subDays(40),
            'completion_date' => Carbon::now()->subDays(30),
            'case_stage' => '已完成',
            'created_by' => $businessUser->id
        ]);

        // 版权费用
        CaseFee::create([
            'case_id' => $copyrightCase->id,
            'fee_type' => CaseFee::TYPE_REGISTRATION,
            'fee_name' => '版权登记费',
            'fee_description' => '软件著作权登记费',
            'amount' => 300.00,
            'currency' => 'CNY',
            'payment_deadline' => Carbon::now()->subDays(60),
            'receivable_date' => Carbon::now()->subDays(65),
            'actual_receive_date' => Carbon::now()->subDays(70),
            'payment_status' => CaseFee::STATUS_PAID,
            'is_reduction' => true,
            'remarks' => '享受减缓政策'
        ]);
    }
}
