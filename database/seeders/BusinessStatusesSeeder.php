<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessStatus;

class BusinessStatusesSeeder extends Seeder
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
                'status_name' => '初步接触',
                'is_valid' => true,
                'sort' => 1,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '需求确认',
                'is_valid' => true,
                'sort' => 2,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '方案制定',
                'is_valid' => true,
                'sort' => 3,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '报价阶段',
                'is_valid' => true,
                'sort' => 4,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '商务谈判',
                'is_valid' => true,
                'sort' => 5,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '合同签署',
                'is_valid' => true,
                'sort' => 6,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '项目执行',
                'is_valid' => true,
                'sort' => 7,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '项目完成',
                'is_valid' => true,
                'sort' => 8,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '客户流失',
                'is_valid' => true,
                'sort' => 9,
                'updated_by' => '系统管理员'
            ],
            [
                'status_name' => '暂停跟进',
                'is_valid' => false,
                'sort' => 10,
                'updated_by' => '系统管理员'
            ]
        ];

        foreach ($data as $item) {
            BusinessStatus::updateOrCreate(
                ['status_name' => $item['status_name']],
                $item
            );
        }

        if ($this->command) {
            if ($this->command) {

                $this->command->info('商机状态数据初始化完成');

            }
        }
    }
}
