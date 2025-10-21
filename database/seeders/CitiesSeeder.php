<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 城市表种子数据
     */
    public function run()
    {
        $cities = [
            ['name' => '北京市', 'code' => 'BJ', 'description' => '中华人民共和国首都', 'status' => 1, 'sort_order' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '上海市', 'code' => 'SH', 'description' => '中华人民共和国直辖市', 'status' => 1, 'sort_order' => 2, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '广州市', 'code' => 'GZ', 'description' => '广东省省会', 'status' => 1, 'sort_order' => 3, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '深圳市', 'code' => 'SZ', 'description' => '广东省副省级市', 'status' => 1, 'sort_order' => 4, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '杭州市', 'code' => 'HZ', 'description' => '浙江省省会', 'status' => 1, 'sort_order' => 5, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '南京市', 'code' => 'NJ', 'description' => '江苏省省会', 'status' => 1, 'sort_order' => 6, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '成都市', 'code' => 'CD', 'description' => '四川省省会', 'status' => 1, 'sort_order' => 7, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '武汉市', 'code' => 'WH', 'description' => '湖北省省会', 'status' => 1, 'sort_order' => 8, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '西安市', 'code' => 'XA', 'description' => '陕西省省会', 'status' => 1, 'sort_order' => 9, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '天津市', 'code' => 'TJ', 'description' => '中华人民共和国直辖市', 'status' => 1, 'sort_order' => 10, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '重庆市', 'code' => 'CQ', 'description' => '中华人民共和国直辖市', 'status' => 1, 'sort_order' => 11, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '青岛市', 'code' => 'QD', 'description' => '山东省副省级市', 'status' => 1, 'sort_order' => 12, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '大连市', 'code' => 'DL', 'description' => '辽宁省副省级市', 'status' => 1, 'sort_order' => 13, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '宁波市', 'code' => 'NB', 'description' => '浙江省副省级市', 'status' => 1, 'sort_order' => 14, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '厦门市', 'code' => 'XM', 'description' => '福建省副省级市', 'status' => 1, 'sort_order' => 15, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        if (DB::table('cities')->count() == 0) {
            DB::table('cities')->insert($cities);
        }
    }
}
