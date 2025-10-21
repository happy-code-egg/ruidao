<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class ReviewTestDataSeeder extends Seeder
{
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

        // 创建核稿管理测试数据

        // 1. 草稿状态的处理事项
        $draftCase = Cases::create([
            'case_code' => 'DRAFT-2024-001',
            'case_name' => '智能传感器专利申请',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_PATENT,
            'application_type' => '发明专利',
            'case_status' => Cases::STATUS_IN_PROGRESS,
            'application_no' => 'CN2024DRAFT001',
            'application_date' => Carbon::now()->subDays(10),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '撰写阶段',
            'created_by' => $businessUser->id
        ]);

        CaseProcess::create([
            'case_id' => $draftCase->id,
            'process_code' => 'DRAFT-001',
            'process_name' => '专利申请文件撰写',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_DRAFT, // 草稿状态
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => $techUser1->id,
            'reviewer' => $techUser2->id,
            'due_date' => Carbon::now()->addDays(15),
            'internal_deadline' => Carbon::now()->addDays(10),
            'official_deadline' => Carbon::now()->addDays(20),
            'issue_date' => Carbon::now()->subDays(5),
            'case_stage' => '草稿阶段',
            'created_by' => $businessUser->id
        ]);

        // 2. 待处理状态的处理事项
        $pendingCase = Cases::create([
            'case_code' => 'PENDING-2024-001',
            'case_name' => '商标注册申请',
            'customer_id' => $customer2->id,
            'case_type' => Cases::TYPE_TRADEMARK,
            'application_type' => '商标注册',
            'case_status' => Cases::STATUS_IN_PROGRESS,
            'application_no' => 'TM2024PENDING001',
            'application_date' => Carbon::now()->subDays(8),
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser2->id,
            'country_code' => '中国',
            'case_phase' => '待处理阶段',
            'created_by' => $businessUser->id
        ]);

        CaseProcess::create([
            'case_id' => $pendingCase->id,
            'process_code' => 'PENDING-001',
            'process_name' => '商标注册申请文件准备',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_PENDING, // 待处理状态
            'priority_level' => CaseProcess::PRIORITY_MEDIUM,
            'assigned_to' => $techUser2->id,
            'reviewer' => $techUser1->id,
            'due_date' => Carbon::now()->addDays(12),
            'internal_deadline' => Carbon::now()->addDays(8),
            'official_deadline' => Carbon::now()->addDays(15),
            'issue_date' => Carbon::now()->subDays(3),
            'case_stage' => '待处理阶段',
            'created_by' => $businessUser->id
        ]);

        // 3. 待开始状态的处理事项
        $toBeStartCase = Cases::create([
            'case_code' => 'TOSTART-2024-001',
            'case_name' => '版权登记申请',
            'customer_id' => $customer1->id,
            'case_type' => Cases::TYPE_COPYRIGHT,
            'application_type' => '软件著作权',
            'case_status' => Cases::STATUS_IN_PROGRESS,
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser1->id,
            'country_code' => '中国',
            'case_phase' => '待开始阶段',
            'created_by' => $businessUser->id
        ]);

        CaseProcess::create([
            'case_id' => $toBeStartCase->id,
            'process_code' => 'TOSTART-001',
            'process_name' => '版权登记材料整理',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_ASSIGNED, // 已分配状态（待开始）
            'priority_level' => CaseProcess::PRIORITY_LOW,
            'assigned_to' => $techUser1->id,
            'reviewer' => $techUser2->id,
            'due_date' => Carbon::now()->addDays(20),
            'internal_deadline' => Carbon::now()->addDays(15),
            'official_deadline' => Carbon::now()->addDays(25),
            'issue_date' => Carbon::now()->subDays(2),
            'case_stage' => '待开始阶段',
            'created_by' => $businessUser->id
        ]);

        // 4. 核稿中状态的处理事项
        $inReviewCase = Cases::create([
            'case_code' => 'REVIEW-2024-001',
            'case_name' => '高新技术企业认定',
            'customer_id' => $customer2->id,
            'case_type' => Cases::TYPE_TECH_SERVICE,
            'application_type' => '高新认定',
            'case_status' => Cases::STATUS_IN_PROGRESS,
            'business_person_id' => $businessUser->id,
            'tech_leader' => $techUser2->id,
            'country_code' => '中国',
            'case_phase' => '核稿阶段',
            'created_by' => $businessUser->id
        ]);

        CaseProcess::create([
            'case_id' => $inReviewCase->id,
            'process_code' => 'REVIEW-001',
            'process_name' => '高新认定材料核稿',
            'process_type' => '内部事项',
            'process_status' => CaseProcess::STATUS_IN_PROGRESS, // 进行中状态（核稿中）
            'priority_level' => CaseProcess::PRIORITY_HIGH,
            'assigned_to' => $techUser2->id,
            'reviewer' => $techUser1->id,
            'due_date' => Carbon::now()->addDays(10),
            'internal_deadline' => Carbon::now()->addDays(7),
            'official_deadline' => Carbon::now()->addDays(12),
            'issue_date' => Carbon::now()->subDays(1),
            'case_stage' => '核稿阶段',
            'created_by' => $businessUser->id
        ]);

        echo "核稿管理测试数据创建完成！\n";
        echo "创建了以下状态的处理事项：\n";
        echo "- 草稿状态: " . CaseProcess::where('process_status', CaseProcess::STATUS_DRAFT)->count() . " 个\n";
        echo "- 待处理状态: " . CaseProcess::where('process_status', CaseProcess::STATUS_PENDING)->count() . " 个\n";
        echo "- 已分配状态（待开始）: " . CaseProcess::where('process_status', CaseProcess::STATUS_ASSIGNED)->count() . " 个\n";
        echo "- 进行中状态（核稿中）: " . CaseProcess::where('process_status', CaseProcess::STATUS_IN_PROGRESS)->count() . " 个\n";
        echo "- 已完成状态: " . CaseProcess::where('process_status', CaseProcess::STATUS_COMPLETED)->count() . " 个\n";
    }
}
