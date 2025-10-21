<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseCoefficient;

class CaseCoefficientsSeeder extends Seeder
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
                'name' => '基础系数',
                'sort_order' => 1,
                'is_valid' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '复杂系数',
                'sort_order' => 2,
                'is_valid' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '特殊系数',
                'sort_order' => 3,
                'is_valid' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '紧急系数',
                'sort_order' => 4,
                'is_valid' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '停用系数',
                'sort_order' => 5,
                'is_valid' => 0,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $item) {
            CaseCoefficient::create($item);
        }
    }
}
