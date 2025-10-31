<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommissionConfig;

class CommissionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configs = [
            // 商务提成配置
            [
                'config_name' => '商务初级配置',
                'config_type' => 'business',
                'level' => '初级',
                'base_rate' => 5.00,
                'bonus_rate' => 2.00,
                'min_amount' => 10000,
                'max_amount' => 50000,
                'status' => 'active',
                'remark' => '商务初级人员提成配置'
            ],
            [
                'config_name' => '商务中级配置',
                'config_type' => 'business',
                'level' => '中级',
                'base_rate' => 7.00,
                'bonus_rate' => 3.00,
                'min_amount' => 50000,
                'max_amount' => 100000,
                'status' => 'active',
                'remark' => '商务中级人员提成配置'
            ],
            [
                'config_name' => '商务高级配置',
                'config_type' => 'business',
                'level' => '高级',
                'base_rate' => 10.00,
                'bonus_rate' => 5.00,
                'min_amount' => 100000,
                'max_amount' => 500000,
                'status' => 'active',
                'remark' => '商务高级人员提成配置'
            ],
            // 代理师提成配置
            [
                'config_name' => '代理师初级配置',
                'config_type' => 'agent',
                'level' => '初级',
                'base_rate' => 3.00,
                'bonus_rate' => 1.00,
                'min_amount' => 5000,
                'max_amount' => 30000,
                'status' => 'active',
                'remark' => '代理师初级人员提成配置'
            ],
            [
                'config_name' => '代理师中级配置',
                'config_type' => 'agent',
                'level' => '中级',
                'base_rate' => 5.00,
                'bonus_rate' => 2.00,
                'min_amount' => 30000,
                'max_amount' => 80000,
                'status' => 'active',
                'remark' => '代理师中级人员提成配置'
            ],
            // 咨询师提成配置
            [
                'config_name' => '咨询师初级配置',
                'config_type' => 'consultant',
                'level' => '初级',
                'base_rate' => 4.00,
                'bonus_rate' => 1.50,
                'min_amount' => 8000,
                'max_amount' => 40000,
                'status' => 'active',
                'remark' => '咨询师初级人员提成配置'
            ],
            [
                'config_name' => '咨询师中级配置',
                'config_type' => 'consultant',
                'level' => '中级',
                'base_rate' => 6.00,
                'bonus_rate' => 2.50,
                'min_amount' => 40000,
                'max_amount' => 90000,
                'status' => 'active',
                'remark' => '咨询师中级人员提成配置'
            ]
        ];

        foreach ($configs as $config) {
            CommissionConfig::create($config);
        }
    }
}

