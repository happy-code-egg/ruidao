<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessTypesSeeder extends Seeder
{
    public function run()
    {
        $processTypes = [
            [
                'name' => '提交申请',
                'code' => 'submit_application',
                'category' => 'general',
                'description' => '提交各类申请文件',
                'status' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '缴纳官费',
                'code' => 'pay_official_fee',
                'category' => 'general',
                'description' => '缴纳各类官方费用',
                'status' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '补正文件',
                'code' => 'correct_document',
                'category' => 'general',
                'description' => '补正申请文件中的错误和遗漏',
                'status' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '答复审查意见',
                'code' => 'reply_examination',
                'category' => 'general',
                'description' => '针对审查意见进行答复',
                'status' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '实质审查请求',
                'code' => 'substantive_examination',
                'category' => 'patent',
                'description' => '提出专利实质审查请求',
                'status' => 1,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '费用减缴请求',
                'code' => 'fee_reduction',
                'category' => 'patent',
                'description' => '申请费用减缴',
                'status' => 1,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '优先权声明',
                'code' => 'priority_claim',
                'category' => 'patent',
                'description' => '提交优先权声明',
                'status' => 1,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '著录变更',
                'code' => 'biblio_change',
                'category' => 'general',
                'description' => '变更申请人、发明人等著录事项',
                'status' => 1,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('process_types')->insert($processTypes);
    }
}
