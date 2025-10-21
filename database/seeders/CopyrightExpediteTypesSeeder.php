<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CopyrightExpediteTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => '普通',
                'code' => 'normal',
                'days' => 30,
                'extra_fee' => 0.00,
                'description' => '普通版权申请，不加急',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '加急',
                'code' => 'urgent',
                'days' => 15,
                'extra_fee' => 300.00,
                'description' => '版权加急申请，15个工作日内完成',
                'status' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '特急',
                'code' => 'super_urgent',
                'days' => 7,
                'extra_fee' => 600.00,
                'description' => '版权特急申请，7个工作日内完成',
                'status' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '立即',
                'code' => 'immediate',
                'days' => 3,
                'extra_fee' => 1200.00,
                'description' => '版权立即申请，3个工作日内完成',
                'status' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('copyright_expedite_types')->insert($types);
    }
}
