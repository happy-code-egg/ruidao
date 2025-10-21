<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessCoefficient;

class ProcessCoefficientsSeeder extends Seeder
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
                'name' => '标准处理系数',
                'sort_order' => 1,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '加急处理系数',
                'sort_order' => 2,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '复杂处理系数',
                'sort_order' => 3,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '特殊处理系数',
                'sort_order' => 4,
                'is_valid' => 1,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '停用处理系数',
                'sort_order' => 5,
                'is_valid' => 0,
                'updated_by' => '系统初始化',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $item) {
            ProcessCoefficient::create($item);
        }
    }
}
