<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserLevelConfig;

class UserLevelConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configs = [
            // 商务人员等级
            [
                'level_name' => '商务初级',
                'level_code' => 'BUSINESS_JUNIOR',
                'level_order' => 1,
                'user_type' => 'business',
                'min_experience' => 0,
                'max_experience' => 2,
                'base_salary' => 8000,
                'required_skills' => json_encode(['销售技巧', '客户沟通']),
                'description' => '商务初级人员，需要0-2年工作经验',
                'status' => 'active',
                'remark' => '商务初级等级配置'
            ],
            [
                'level_name' => '商务中级',
                'level_code' => 'BUSINESS_MIDDLE',
                'level_order' => 2,
                'user_type' => 'business',
                'min_experience' => 2,
                'max_experience' => 5,
                'base_salary' => 12000,
                'required_skills' => json_encode(['项目管理', '团队协作', '客户维护']),
                'description' => '商务中级人员，需要2-5年工作经验',
                'status' => 'active',
                'remark' => '商务中级等级配置'
            ],
            [
                'level_name' => '商务高级',
                'level_code' => 'BUSINESS_SENIOR',
                'level_order' => 3,
                'user_type' => 'business',
                'min_experience' => 5,
                'max_experience' => 10,
                'base_salary' => 18000,
                'required_skills' => json_encode(['战略规划', '大客户管理', '团队领导']),
                'description' => '商务高级人员，需要5-10年工作经验',
                'status' => 'active',
                'remark' => '商务高级等级配置'
            ],
            // 代理师等级
            [
                'level_name' => '代理师初级',
                'level_code' => 'AGENT_JUNIOR',
                'level_order' => 1,
                'user_type' => 'agent',
                'min_experience' => 0,
                'max_experience' => 2,
                'base_salary' => 10000,
                'required_skills' => json_encode(['法律基础', '代理知识']),
                'description' => '代理师初级人员，需要0-2年工作经验',
                'status' => 'active',
                'remark' => '代理师初级等级配置'
            ],
            [
                'level_name' => '代理师中级',
                'level_code' => 'AGENT_MIDDLE',
                'level_order' => 2,
                'user_type' => 'agent',
                'min_experience' => 2,
                'max_experience' => 5,
                'base_salary' => 15000,
                'required_skills' => json_encode(['案件处理', '客户服务', '法律咨询']),
                'description' => '代理师中级人员，需要2-5年工作经验',
                'status' => 'active',
                'remark' => '代理师中级等级配置'
            ],
            // 咨询师等级
            [
                'level_name' => '咨询师初级',
                'level_code' => 'CONSULTANT_JUNIOR',
                'level_order' => 1,
                'user_type' => 'consultant',
                'min_experience' => 0,
                'max_experience' => 2,
                'base_salary' => 9000,
                'required_skills' => json_encode(['咨询技巧', '行业知识']),
                'description' => '咨询师初级人员，需要0-2年工作经验',
                'status' => 'active',
                'remark' => '咨询师初级等级配置'
            ],
            [
                'level_name' => '咨询师中级',
                'level_code' => 'CONSULTANT_MIDDLE',
                'level_order' => 2,
                'user_type' => 'consultant',
                'min_experience' => 2,
                'max_experience' => 5,
                'base_salary' => 14000,
                'required_skills' => json_encode(['方案设计', '项目咨询', '客户管理']),
                'description' => '咨询师中级人员，需要2-5年工作经验',
                'status' => 'active',
                'remark' => '咨询师中级等级配置'
            ],
            // 运营人员等级
            [
                'level_name' => '运营初级',
                'level_code' => 'OPERATION_JUNIOR',
                'level_order' => 1,
                'user_type' => 'operation',
                'min_experience' => 0,
                'max_experience' => 2,
                'base_salary' => 7000,
                'required_skills' => json_encode(['数据处理', '流程管理']),
                'description' => '运营初级人员，需要0-2年工作经验',
                'status' => 'active',
                'remark' => '运营初级等级配置'
            ],
            [
                'level_name' => '运营中级',
                'level_code' => 'OPERATION_MIDDLE',
                'level_order' => 2,
                'user_type' => 'operation',
                'min_experience' => 2,
                'max_experience' => 5,
                'base_salary' => 11000,
                'required_skills' => json_encode(['系统管理', '团队协调', '数据分析']),
                'description' => '运营中级人员，需要2-5年工作经验',
                'status' => 'active',
                'remark' => '运营中级等级配置'
            ]
        ];

        foreach ($configs as $config) {
            UserLevelConfig::create($config);
        }
    }
}

