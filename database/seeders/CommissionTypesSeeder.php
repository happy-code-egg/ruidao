<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissionTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => '签单提成',
                'code' => 'contract_commission',
                'rate' => 5.00,
                'description' => '签订合同时的提成',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '业绩提成',
                'code' => 'performance_commission',
                'rate' => 8.00,
                'description' => '基于业绩完成情况的提成',
                'status' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '团队提成',
                'code' => 'team_commission',
                'rate' => 3.00,
                'description' => '团队整体业绩的提成',
                'status' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '部门提成',
                'code' => 'department_commission',
                'rate' => 2.00,
                'description' => '部门业绩的提成',
                'status' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '年度提成',
                'code' => 'annual_commission',
                'rate' => 10.00,
                'description' => '年度业绩达标的额外提成',
                'status' => 1,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('commission_types')->insert($types);
    }
}
