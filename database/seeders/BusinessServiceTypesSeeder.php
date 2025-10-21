<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessServiceTypes;

class BusinessServiceTypesSeeder extends Seeder
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
                'name' => '专利申请',
                'code' => 'PATENT_APPLICATION',
                'category' => '专利',
                'description' => '各类专利申请服务',
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => '实用新型申请',
                'code' => 'UTILITY_MODEL_APPLICATION',
                'category' => '专利',
                'description' => '实用新型专利申请服务',
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => '外观设计申请',
                'code' => 'DESIGN_APPLICATION',
                'category' => '专利',
                'description' => '外观设计专利申请服务',
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => '专利无效宣告',
                'code' => 'PATENT_INVALIDATION',
                'category' => '专利',
                'description' => '专利无效宣告服务',
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => '无效宣告答辩',
                'code' => 'INVALIDATION_RESPONSE',
                'category' => '专利',
                'description' => '无效宣告答辩服务',
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => '专利复审',
                'code' => 'PATENT_REVIEW',
                'category' => '专利',
                'description' => '专利复审服务',
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => '专利转让',
                'code' => 'PATENT_TRANSFER',
                'category' => '专利',
                'description' => '专利转让服务',
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => '专利变更',
                'code' => 'PATENT_CHANGE',
                'category' => '专利',
                'description' => '专利变更服务',
                'status' => 1,
                'sort_order' => 8,
            ],
            [
                'name' => '专利年费代缴',
                'code' => 'PATENT_ANNUAL_FEE',
                'category' => '专利',
                'description' => '专利年费代缴服务',
                'status' => 1,
                'sort_order' => 9,
            ],
            [
                'name' => '海外单一国申请',
                'code' => 'OVERSEAS_APPLICATION',
                'category' => '专利',
                'description' => '海外单一国专利申请服务',
                'status' => 1,
                'sort_order' => 10,
            ],
            [
                'name' => '商标注册',
                'code' => 'TRADEMARK_REGISTRATION',
                'category' => '商标',
                'description' => '商标注册服务',
                'status' => 1,
                'sort_order' => 11,
            ],
            [
                'name' => '版权登记',
                'code' => 'COPYRIGHT_REGISTRATION',
                'category' => '版权',
                'description' => '版权登记服务',
                'status' => 1,
                'sort_order' => 12,
            ],
        ];

        foreach ($data as $item) {
            BusinessServiceTypes::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        if ($this->command) {
            if ($this->command) {

                $this->command->info('业务服务类型数据初始化完成');

            }
        }
    }
}

