<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUpType;

class FollowUpTypesSeeder extends Seeder
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
                'name' => '询价洽谈',
                'code' => 'PRICE_INQUIRY',
                'description' => '客户询价和价格洽谈',
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => '合同签署',
                'code' => 'CONTRACT_SIGNING',
                'description' => '合同签署相关跟进',
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => '售后服务',
                'code' => 'AFTER_SALES',
                'description' => '项目售后服务跟进',
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => '项目推进',
                'code' => 'PROJECT_PROGRESS',
                'description' => '项目进展推进跟进',
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => '需求了解',
                'code' => 'REQUIREMENT_UNDERSTANDING',
                'description' => '了解客户具体需求',
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => '方案确认',
                'code' => 'SOLUTION_CONFIRMATION',
                'description' => '方案确认和调整',
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => '合作洽谈',
                'code' => 'COOPERATION_NEGOTIATION',
                'description' => '合作方式洽谈',
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => '技术咨询',
                'code' => 'TECHNICAL_CONSULTATION',
                'description' => '技术相关咨询跟进',
                'status' => 1,
                'sort_order' => 8,
            ],
        ];

        foreach ($data as $item) {
            FollowUpType::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        if ($this->command) {


            $this->command->info('跟进类型数据初始化完成');


        }
    }
}

