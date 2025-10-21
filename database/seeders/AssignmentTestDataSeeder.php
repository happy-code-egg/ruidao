<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class AssignmentTestDataSeeder extends Seeder
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
                'username' => 'business',
                'real_name' => '张业务',
                'password' => bcrypt('password'),
                'status' => 1
            ]
        );

        $techUser1 = User::firstOrCreate(
            ['email' => 'tech1@test.com'],
            [
                'username' => 'tech1',
                'real_name' => '李工程师',
                'password' => bcrypt('password'),
                'status' => 1
            ]
        );

        $techUser2 = User::firstOrCreate(
            ['email' => 'tech2@test.com'],
            [
                'username' => 'tech2',
                'real_name' => '王工程师',
                'password' => bcrypt('password'),
                'status' => 1
            ]
        );

        $reviewer1 = User::firstOrCreate(
            ['email' => 'reviewer1@test.com'],
            [
                'username' => 'reviewer1',
                'real_name' => '陈审核员',
                'password' => bcrypt('password'),
                'status' => 1
            ]
        );

        // 创建测试客户
        $customer1 = Customer::firstOrCreate(
            ['customer_name' => '上海科技有限公司'],
            [
                'customer_code' => 'CUST001',
                'contact_person' => '张总',
                'contact_phone' => '13800138001',
                'contact_email' => 'customer1@test.com',
                'address' => '上海市浦东新区'
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['customer_name' => '北京创新科技公司'],
            [
                'customer_code' => 'CUST002',
                'contact_person' => '李总',
                'contact_phone' => '13800138002',
                'contact_email' => 'customer2@test.com',
                'address' => '北京市海淀区'
            ]
        );

        $customer3 = Customer::firstOrCreate(
            ['customer_name' => '深圳智能制造有限公司'],
            [
                'customer_code' => 'CUST003',
                'contact_person' => '王总',
                'contact_phone' => '13800138003',
                'contact_email' => 'customer3@test.com',
                'address' => '深圳市南山区'
            ]
        );

        // 创建专利案例和新申请处理事项
        $patentCase1 = Cases::create([
            'case_code' => 'PT-2024-001',
            'case_name' => '智能感应装置专利申请',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '发明专利',
            'case_status' => Cases::STATUS_PROCESSING,
            'application_no' => 'CN202410001',
            'application_date' => Carbon::now()->subDays(30),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '申请阶段',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 新申请处理事项（未分配）
        CaseProcess::create([
            'case_id' => $patentCase1->id,
            'process_code' => 'PROC-001',
            'process_name' => '发明专利新申请',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => null, // 未分配
            'reviewer' => null,
            'due_date' => Carbon::now()->addDays(15),
            'internal_deadline' => Carbon::now()->addDays(10),
            'official_deadline' => Carbon::now()->addDays(20),
            'customer_deadline' => Carbon::now()->addDays(18),
            'issue_date' => Carbon::now()->subDays(5),
            'case_stage' => '申请阶段',
            'is_frozen' => false,
            'created_by' => $businessUser->id
        ]);

        $patentCase2 = Cases::create([
            'case_code' => 'PT-2024-002',
            'case_name' => '节能环保装置专利申请',
            'customer_id' => $customer2->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '实用新型',
            'case_status' => Cases::STATUS_PROCESSING,
            'application_no' => 'CN202410002',
            'application_date' => Carbon::now()->subDays(25),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser2->id,
            'country_code' => '中国',
            'case_phase' => '申请阶段',
            'applicant_info' => json_encode([
                'name' => '北京创新科技公司',
                'address' => '北京市海淀区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 新申请处理事项（未分配，冻结）
        CaseProcess::create([
            'case_id' => $patentCase2->id,
            'process_code' => 'PROC-002',
            'process_name' => '实用新型新申请',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => null, // 未分配
            'reviewer' => null,
            'due_date' => Carbon::now()->addDays(12),
            'internal_deadline' => Carbon::now()->addDays(8),
            'official_deadline' => Carbon::now()->addDays(15),
            'customer_deadline' => Carbon::now()->addDays(12),
            'issue_date' => Carbon::now()->subDays(3),
            'case_stage' => '申请阶段',
            'is_frozen' => true, // 冻结
            'created_by' => $businessUser->id
        ]);

        // 创建商标案例和新申请处理事项
        $trademarkCase = Cases::create([
            'case_code' => 'TM-2024-001',
            'case_name' => '创新LOGO商标注册',
            'customer_id' => $customer3->id,
            'case_type' => Cases::TYPE_TRADEMARK,
            'application_type' => '商标注册',
            'case_status' => Cases::STATUS_PROCESSING,
            'application_no' => 'TM202410001',
            'application_date' => Carbon::now()->subDays(20),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '申请阶段',
            'trademark_category' => '42',
            'applicant_info' => json_encode([
                'name' => '深圳智能制造有限公司',
                'address' => '深圳市南山区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 商标新申请处理事项（未分配）
        CaseProcess::create([
            'case_id' => $trademarkCase->id,
            'process_code' => 'PROC-003',
            'process_name' => '商标注册新申请',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => null, // 未分配
            'reviewer' => null,
            'due_date' => Carbon::now()->addDays(18),
            'internal_deadline' => Carbon::now()->addDays(12),
            'official_deadline' => Carbon::now()->addDays(25),
            'customer_deadline' => Carbon::now()->addDays(20),
            'issue_date' => Carbon::now()->subDays(2),
            'case_stage' => '申请阶段',
            'is_frozen' => false,
            'created_by' => $businessUser->id
        ]);

        // 创建中间案处理事项（非新申请）
        $patentCase3 = Cases::create([
            'case_code' => 'PT-2024-003',
            'case_name' => '智能控制系统专利',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '发明专利',
            'case_status' => Cases::STATUS_PROCESSING,
            'application_no' => 'CN202410003',
            'application_date' => Carbon::now()->subDays(60),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser2->id,
            'country_code' => '中国',
            'case_phase' => '审查阶段',
            'applicant_info' => json_encode([
                'name' => '上海科技有限公司',
                'address' => '上海市浦东新区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 中间案处理事项（审查意见答复）
        CaseProcess::create([
            'case_id' => $patentCase3->id,
            'process_code' => 'PROC-004',
            'process_name' => '审查意见答复',
            'process_type' => '官方来文',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => null, // 未分配
            'reviewer' => null,
            'due_date' => Carbon::now()->addDays(30),
            'internal_deadline' => Carbon::now()->addDays(25),
            'official_deadline' => Carbon::now()->addDays(45),
            'customer_deadline' => Carbon::now()->addDays(35),
            'issue_date' => Carbon::now()->subDays(10),
            'case_stage' => '审查阶段',
            'is_frozen' => false,
            'created_by' => $businessUser->id
        ]);

        // 创建科技服务案例
        $techServiceCase = Cases::create([
            'case_code' => 'TS-2024-001',
            'case_name' => '高新技术企业认定服务',
            'customer_id' => $customer2->id,
            'case_type' => Cases::TYPE_TECH_SERVICE,
            'application_type' => '高新认定',
            'case_status' => Cases::STATUS_PROCESSING,
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '服务阶段',
            'applicant_info' => json_encode([
                'name' => '北京创新科技公司',
                'address' => '北京市海淀区'
            ]),
            'created_by' => $businessUser->id
        ]);

        // 科技服务处理事项
        CaseProcess::create([
            'case_id' => $techServiceCase->id,
            'process_code' => 'PROC-005',
            'process_name' => '高新认定材料准备',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_PENDING,
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => null, // 未分配
            'reviewer' => null,
            'due_date' => Carbon::now()->addDays(20),
            'internal_deadline' => Carbon::now()->addDays(15),
            'official_deadline' => Carbon::now()->addDays(30),
            'customer_deadline' => Carbon::now()->addDays(25),
            'issue_date' => Carbon::now()->subDays(1),
            'case_stage' => '服务阶段',
            'is_frozen' => false,
            'created_by' => $businessUser->id
        ]);

        // 创建已分配的处理事项
        $assignedProcess = CaseProcess::create([
            'case_id' => $patentCase1->id,
            'process_code' => 'PROC-006',
            'process_name' => '缴费通知',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_PROCESSING,
            'priority_level' => CaseProcess::PRIORITY_LOW,
            'assigned_to' => $techUser1->id, // 已分配
            'reviewer' => $reviewer1->id,
            'due_date' => Carbon::now()->addDays(10),
            'internal_deadline' => Carbon::now()->addDays(7),
            'official_deadline' => Carbon::now()->addDays(15),
            'customer_deadline' => Carbon::now()->addDays(12),
            'issue_date' => Carbon::now()->subDays(5),
            'case_stage' => '申请阶段',
            'is_frozen' => false,
            'created_by' => $businessUser->id
        ]);

        echo "分配管理测试数据创建完成！\n";
        echo "创建了" . Cases::count() . "个案例\n";
        echo "创建了" . CaseProcess::count() . "个处理事项\n";
        echo "其中未分配的新申请事项：" . CaseProcess::whereNull('assigned_to')->where('process_name', 'like', '%新申请%')->count() . "个\n";
        echo "其中未分配的中间案事项：" . CaseProcess::whereNull('assigned_to')->where('process_name', 'not like', '%新申请%')->count() . "个\n";
        echo "其中已分配的事项：" . CaseProcess::whereNotNull('assigned_to')->count() . "个\n";
    }
}
