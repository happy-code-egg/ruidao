<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUpProgress;

class FollowUpProgressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => '初步接洽',
                'code' => 'INITIAL_CONTACT',
                'percentage' => 10,
                'description' => '首次接触客户，建立联系',
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => '需求沟通',
                'code' => 'REQUIREMENT_COMMUNICATION',
                'percentage' => 25,
                'description' => '了解客户具体需求',
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => '方案确认',
                'code' => 'SOLUTION_CONFIRMATION',
                'percentage' => 40,
                'description' => '确认服务方案和报价',
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => '合同谈判',
                'code' => 'CONTRACT_NEGOTIATION',
                'percentage' => 60,
                'description' => '合同条款协商谈判',
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => '签约完成',
                'code' => 'CONTRACT_SIGNED',
                'percentage' => 80,
                'description' => '合同签署完成',
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => '项目执行',
                'code' => 'PROJECT_EXECUTION',
                'percentage' => 90,
                'description' => '项目正在执行中',
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => '项目完成',
                'code' => 'PROJECT_COMPLETED',
                'percentage' => 100,
                'description' => '项目执行完成',
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => '已暂停',
                'code' => 'PAUSED',
                'percentage' => 0,
                'description' => '项目暂时暂停',
                'status' => 1,
                'sort_order' => 8,
            ],
            [
                'name' => '已取消',
                'code' => 'CANCELLED',
                'percentage' => 0,
                'description' => '项目已取消',
                'status' => 1,
                'sort_order' => 9,
            ],
        ];

        foreach ($data as $item) {
            FollowUpProgress::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        if ($this->command) {


            $this->command->info('跟进进度数据初始化完成');


        }
    }
}

