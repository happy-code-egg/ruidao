<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert test data for commission configs and user level configs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 清空现有数据
        DB::table('commission_configs')->truncate();
        DB::table('user_level_configs')->truncate();

        // 插入提成配置数据
        $commissionConfigs = [
            ['config_name' => '商务初级配置', 'config_type' => 'business', 'level' => '初级', 'base_rate' => 5.00, 'bonus_rate' => 2.00, 'min_amount' => 10000, 'max_amount' => 50000, 'status' => 'active', 'remark' => '商务初级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '商务中级配置', 'config_type' => 'business', 'level' => '中级', 'base_rate' => 7.00, 'bonus_rate' => 3.00, 'min_amount' => 50000, 'max_amount' => 100000, 'status' => 'active', 'remark' => '商务中级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '商务高级配置', 'config_type' => 'business', 'level' => '高级', 'base_rate' => 10.00, 'bonus_rate' => 5.00, 'min_amount' => 100000, 'max_amount' => 500000, 'status' => 'active', 'remark' => '商务高级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '代理师初级配置', 'config_type' => 'agent', 'level' => '初级', 'base_rate' => 3.00, 'bonus_rate' => 1.00, 'min_amount' => 5000, 'max_amount' => 30000, 'status' => 'active', 'remark' => '代理师初级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '代理师中级配置', 'config_type' => 'agent', 'level' => '中级', 'base_rate' => 5.00, 'bonus_rate' => 2.00, 'min_amount' => 30000, 'max_amount' => 80000, 'status' => 'active', 'remark' => '代理师中级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '咨询师初级配置', 'config_type' => 'consultant', 'level' => '初级', 'base_rate' => 4.00, 'bonus_rate' => 1.50, 'min_amount' => 8000, 'max_amount' => 40000, 'status' => 'active', 'remark' => '咨询师初级人员提成配置', 'created_at' => now(), 'updated_at' => now()],
            ['config_name' => '咨询师中级配置', 'config_type' => 'consultant', 'level' => '中级', 'base_rate' => 6.00, 'bonus_rate' => 2.50, 'min_amount' => 40000, 'max_amount' => 90000, 'status' => 'active', 'remark' => '咨询师中级人员提成配置', 'created_at' => now(), 'updated_at' => now()]
        ];

        DB::table('commission_configs')->insert($commissionConfigs);
        $this->info('✓ 提成配置数据插入成功！');

        // 插入用户等级配置数据
        $userLevelConfigs = [
            ['level_name' => '商务初级', 'level_code' => 'BUSINESS_JUNIOR', 'level_order' => 1, 'user_type' => 'business', 'min_experience' => 0, 'max_experience' => 2, 'base_salary' => 8000, 'required_skills' => '["销售技巧","客户沟通"]', 'description' => '商务初级人员，需要0-2年工作经验', 'status' => 'active', 'remark' => '商务初级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '商务中级', 'level_code' => 'BUSINESS_MIDDLE', 'level_order' => 2, 'user_type' => 'business', 'min_experience' => 2, 'max_experience' => 5, 'base_salary' => 12000, 'required_skills' => '["项目管理","团队协作","客户维护"]', 'description' => '商务中级人员，需要2-5年工作经验', 'status' => 'active', 'remark' => '商务中级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '商务高级', 'level_code' => 'BUSINESS_SENIOR', 'level_order' => 3, 'user_type' => 'business', 'min_experience' => 5, 'max_experience' => 10, 'base_salary' => 18000, 'required_skills' => '["战略规划","大客户管理","团队领导"]', 'description' => '商务高级人员，需要5-10年工作经验', 'status' => 'active', 'remark' => '商务高级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '代理师初级', 'level_code' => 'AGENT_JUNIOR', 'level_order' => 1, 'user_type' => 'agent', 'min_experience' => 0, 'max_experience' => 2, 'base_salary' => 10000, 'required_skills' => '["法律基础","代理知识"]', 'description' => '代理师初级人员，需要0-2年工作经验', 'status' => 'active', 'remark' => '代理师初级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '代理师中级', 'level_code' => 'AGENT_MIDDLE', 'level_order' => 2, 'user_type' => 'agent', 'min_experience' => 2, 'max_experience' => 5, 'base_salary' => 15000, 'required_skills' => '["案件处理","客户服务","法律咨询"]', 'description' => '代理师中级人员，需要2-5年工作经验', 'status' => 'active', 'remark' => '代理师中级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '咨询师初级', 'level_code' => 'CONSULTANT_JUNIOR', 'level_order' => 1, 'user_type' => 'consultant', 'min_experience' => 0, 'max_experience' => 2, 'base_salary' => 9000, 'required_skills' => '["咨询技巧","行业知识"]', 'description' => '咨询师初级人员，需要0-2年工作经验', 'status' => 'active', 'remark' => '咨询师初级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '咨询师中级', 'level_code' => 'CONSULTANT_MIDDLE', 'level_order' => 2, 'user_type' => 'consultant', 'min_experience' => 2, 'max_experience' => 5, 'base_salary' => 14000, 'required_skills' => '["方案设计","项目咨询","客户管理"]', 'description' => '咨询师中级人员，需要2-5年工作经验', 'status' => 'active', 'remark' => '咨询师中级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '运营初级', 'level_code' => 'OPERATION_JUNIOR', 'level_order' => 1, 'user_type' => 'operation', 'min_experience' => 0, 'max_experience' => 2, 'base_salary' => 7000, 'required_skills' => '["数据处理","流程管理"]', 'description' => '运营初级人员，需要0-2年工作经验', 'status' => 'active', 'remark' => '运营初级等级配置', 'created_at' => now(), 'updated_at' => now()],
            ['level_name' => '运营中级', 'level_code' => 'OPERATION_MIDDLE', 'level_order' => 2, 'user_type' => 'operation', 'min_experience' => 2, 'max_experience' => 5, 'base_salary' => 11000, 'required_skills' => '["系统管理","团队协调","数据分析"]', 'description' => '运营中级人员，需要2-5年工作经验', 'status' => 'active', 'remark' => '运营中级等级配置', 'created_at' => now(), 'updated_at' => now()]
        ];

        DB::table('user_level_configs')->insert($userLevelConfigs);
        $this->info('✓ 用户等级配置数据插入成功！');

        return 0;
    }
}

