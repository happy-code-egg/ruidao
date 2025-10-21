<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerScale;

class CustomerScalesSeeder extends Seeder
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
                'scale_name' => '大型企业',
                'is_valid' => true,
                'sort' => 1,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '中型企业',
                'is_valid' => true,
                'sort' => 2,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '小型企业',
                'is_valid' => true,
                'sort' => 3,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '微型企业',
                'is_valid' => true,
                'sort' => 4,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '初创企业',
                'is_valid' => true,
                'sort' => 5,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '央企',
                'is_valid' => true,
                'sort' => 6,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '国企',
                'is_valid' => true,
                'sort' => 7,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '外企',
                'is_valid' => true,
                'sort' => 8,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '民营企业',
                'is_valid' => true,
                'sort' => 9,
                'updated_by' => '系统管理员'
            ],
            [
                'scale_name' => '个体工商户',
                'is_valid' => true,
                'sort' => 10,
                'updated_by' => '系统管理员'
            ]
        ];

        foreach ($data as $item) {
            CustomerScale::updateOrCreate(
                ['scale_name' => $item['scale_name']],
                $item
            );
        }

        if ($this->command) {


            $this->command->info('客户规模数据初始化完成');


        }
    }
}
