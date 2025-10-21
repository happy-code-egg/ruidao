<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 货币表种子数据
     */
    public function run()
    {
        $currencies = [
            ['name' => '人民币', 'code' => 'CNY', 'symbol' => '¥', 'description' => '中华人民共和国法定货币', 'status' => 1, 'sort_order' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '美元', 'code' => 'USD', 'symbol' => '$', 'description' => '美利坚合众国法定货币', 'status' => 1, 'sort_order' => 2, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '欧元', 'code' => 'EUR', 'symbol' => '€', 'description' => '欧洲联盟统一货币', 'status' => 1, 'sort_order' => 3, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '日元', 'code' => 'JPY', 'symbol' => '¥', 'description' => '日本国法定货币', 'status' => 1, 'sort_order' => 4, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '英镑', 'code' => 'GBP', 'symbol' => '£', 'description' => '英国法定货币', 'status' => 1, 'sort_order' => 5, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '韩元', 'code' => 'KRW', 'symbol' => '₩', 'description' => '韩国法定货币', 'status' => 1, 'sort_order' => 6, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '港币', 'code' => 'HKD', 'symbol' => 'HK$', 'description' => '香港特别行政区法定货币', 'status' => 1, 'sort_order' => 7, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '澳元', 'code' => 'AUD', 'symbol' => 'A$', 'description' => '澳大利亚法定货币', 'status' => 1, 'sort_order' => 8, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '加元', 'code' => 'CAD', 'symbol' => 'C$', 'description' => '加拿大法定货币', 'status' => 1, 'sort_order' => 9, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '瑞士法郎', 'code' => 'CHF', 'symbol' => 'CHF', 'description' => '瑞士法定货币', 'status' => 1, 'sort_order' => 10, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        if (DB::table('currencies')->count() == 0) {
            DB::table('currencies')->insert($currencies);
        }
    }
}
