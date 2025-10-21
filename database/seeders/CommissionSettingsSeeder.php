<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissionSettingsSeeder extends Seeder
{
    public function run()
    {
        // 清空表
        DB::table('commission_settings')->truncate();

        $settings = [
            [
                'name' => '初级-发明专利-申请业务-普通申请',
                'code' => 'junior_invention_application_normal',
                'handler_level' => '初级',
                'case_type' => '发明专利',
                'business_type' => '申请业务',
                'application_type' => '普通申请',
                'case_coefficient' => 1.0,
                'matter_coefficient' => 1.2,
                'processing_matter' => '申请文件撰写',
                'case_stage' => '申请阶段',
                'commission_type' => '按件提成',
                'piece_ratio' => 5.0,
                'piece_points' => 100,
                'country' => '中国',
                'rate' => 5.0,
                'status' => 1,
                'sort_order' => 1,
                'description' => '初级代理师处理发明专利申请业务的提成配置',
                'updater' => '系统管理员',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '中级-实用新型-申请业务-加急申请',
                'code' => 'intermediate_utility_application_urgent',
                'handler_level' => '中级',
                'case_type' => '实用新型',
                'business_type' => '申请业务',
                'application_type' => '加急申请',
                'case_coefficient' => 1.5,
                'matter_coefficient' => 1.5,
                'processing_matter' => '答复审查意见',
                'case_stage' => '审查阶段',
                'commission_type' => '按件提成',
                'piece_ratio' => 8.0,
                'piece_points' => 150,
                'country' => '中国',
                'rate' => 8.0,
                'status' => 1,
                'sort_order' => 2,
                'description' => '中级代理师处理实用新型加急申请业务的提成配置',
                'updater' => '系统管理员',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '高级-商标-注册业务-普通申请',
                'code' => 'senior_trademark_registration_normal',
                'handler_level' => '高级',
                'case_type' => '商标',
                'business_type' => '注册业务',
                'application_type' => '普通申请',
                'case_coefficient' => 1.2,
                'matter_coefficient' => 1.0,
                'processing_matter' => '商标注册',
                'case_stage' => '注册阶段',
                'commission_type' => '按件提成',
                'piece_ratio' => 10.0,
                'piece_points' => 200,
                'country' => '美国',
                'rate' => 10.0,
                'status' => 1,
                'sort_order' => 3,
                'description' => '高级代理师处理商标注册业务的提成配置',
                'updater' => '系统管理员',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '专家级-外观设计-申请业务-优先申请',
                'code' => 'expert_design_application_priority',
                'handler_level' => '专家级',
                'case_type' => '外观设计',
                'business_type' => '申请业务',
                'application_type' => '优先申请',
                'case_coefficient' => 2.0,
                'matter_coefficient' => 1.8,
                'processing_matter' => '外观设计撰写',
                'case_stage' => '申请阶段',
                'commission_type' => '按比例提成',
                'piece_ratio' => 12.0,
                'piece_points' => 250,
                'country' => '欧盟',
                'rate' => 12.0,
                'status' => 1,
                'sort_order' => 4,
                'description' => '专家级代理师处理外观设计优先申请业务的提成配置',
                'updater' => '系统管理员',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '中级-版权-注册业务-加急申请',
                'code' => 'intermediate_copyright_registration_urgent',
                'handler_level' => '中级',
                'case_type' => '版权',
                'business_type' => '注册业务',
                'application_type' => '加急申请',
                'case_coefficient' => 1.3,
                'matter_coefficient' => 1.1,
                'processing_matter' => '版权登记',
                'case_stage' => '登记阶段',
                'commission_type' => '固定提成',
                'piece_ratio' => 6.0,
                'piece_points' => 120,
                'country' => '中国',
                'rate' => 6.0,
                'status' => 1,
                'sort_order' => 5,
                'description' => '中级代理师处理版权加急注册业务的提成配置',
                'updater' => '系统管理员',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('commission_settings')->insert($settings);
    }
}
