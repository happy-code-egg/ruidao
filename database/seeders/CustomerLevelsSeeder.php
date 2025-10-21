<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerLevel;

class CustomerLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customerLevels = [
            [
                'sort' => 1,
                'level_name' => 'VIP客户',
                'level_code' => 'VIP',
                'description' => '重要VIP客户，享受优先服务',
                'is_valid' => 1,
                'sort_order' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'sort' => 2,
                'level_name' => '重点客户',
                'level_code' => 'IMPORTANT',
                'description' => '重点关注客户，定期回访',
                'is_valid' => 1,
                'sort_order' => 2,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'sort' => 3,
                'level_name' => '普通客户',
                'level_code' => 'NORMAL',
                'description' => '普通合作客户',
                'is_valid' => 1,
                'sort_order' => 3,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'sort' => 4,
                'level_name' => '潜在客户',
                'level_code' => 'POTENTIAL',
                'description' => '有合作意向的潜在客户',
                'is_valid' => 1,
                'sort_order' => 4,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'sort' => 5,
                'level_name' => '试用客户',
                'level_code' => 'TRIAL',
                'description' => '正在试用服务的客户',
                'is_valid' => 1,
                'sort_order' => 5,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($customerLevels as $level) {
            CustomerLevel::create($level);
        }
        
        $this->command->info('客户等级数据初始化完成');
    }
}
