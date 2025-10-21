<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProtectionCentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 保护中心表种子数据
     */
    public function run()
    {
        $protectionCenters = [
            [
                'sort' => 1,
                'name' => '中国(北京)知识产权保护中心',
                'center_name' => '中国(北京)知识产权保护中心',
                'code' => 'BEIJING_IP_CENTER',
                'address' => '北京市海淀区西土城路6号',
                'contact_person' => '张主任',
                'contact_phone' => '010-62083114',
                'description' => '负责北京地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 1,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 2,
                'name' => '中国(上海)知识产权保护中心',
                'center_name' => '中国(上海)知识产权保护中心',
                'code' => 'SHANGHAI_IP_CENTER',
                'address' => '上海市徐汇区钦州路100号',
                'contact_person' => '李主任',
                'contact_phone' => '021-23085000',
                'description' => '负责上海地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 2,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 3,
                'name' => '中国(广东)知识产权保护中心',
                'center_name' => '中国(广东)知识产权保护中心',
                'code' => 'GUANGDONG_IP_CENTER',
                'address' => '广州市天河区马场路16号',
                'contact_person' => '王主任',
                'contact_phone' => '020-38835588',
                'description' => '负责广东地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 3,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 4,
                'name' => '中国(深圳)知识产权保护中心',
                'center_name' => '中国(深圳)知识产权保护中心',
                'code' => 'SHENZHEN_IP_CENTER',
                'address' => '深圳市南山区深南大道10128号',
                'contact_person' => '陈主任',
                'contact_phone' => '0755-83070000',
                'description' => '负责深圳地区知识产权快速协同保护工作，重点服务高新技术产业',
                'status' => 1,
                'sort_order' => 4,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 5,
                'name' => '中国(江苏)知识产权保护中心',
                'center_name' => '中国(江苏)知识产权保护中心',
                'code' => 'JIANGSU_IP_CENTER',
                'address' => '南京市建邺区江东中路265号',
                'contact_person' => '刘主任',
                'contact_phone' => '025-83455000',
                'description' => '负责江苏地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 5,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 6,
                'name' => '中国(浙江)知识产权保护中心',
                'center_name' => '中国(浙江)知识产权保护中心',
                'code' => 'ZHEJIANG_IP_CENTER',
                'address' => '杭州市西湖区教工路18号',
                'contact_person' => '周主任',
                'contact_phone' => '0571-87054000',
                'description' => '负责浙江地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 6,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 7,
                'name' => '中国(山东)知识产权保护中心',
                'center_name' => '中国(山东)知识产权保护中心',
                'code' => 'SHANDONG_IP_CENTER',
                'address' => '济南市历下区经十路17923号',
                'contact_person' => '赵主任',
                'contact_phone' => '0531-82073000',
                'description' => '负责山东地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 7,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 8,
                'name' => '中国(四川)知识产权保护中心',
                'center_name' => '中国(四川)知识产权保护中心',
                'code' => 'SICHUAN_IP_CENTER',
                'address' => '成都市武侯区人民南路四段11号',
                'contact_person' => '孙主任',
                'contact_phone' => '028-86136000',
                'description' => '负责四川地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 8,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 9,
                'name' => '中国(湖北)知识产权保护中心',
                'center_name' => '中国(湖北)知识产权保护中心',
                'code' => 'HUBEI_IP_CENTER',
                'address' => '武汉市洪山区珞瑜路1037号',
                'contact_person' => '吴主任',
                'contact_phone' => '027-87654000',
                'description' => '负责湖北地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 9,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 10,
                'name' => '中国(陕西)知识产权保护中心',
                'center_name' => '中国(陕西)知识产权保护中心',
                'code' => 'SHAANXI_IP_CENTER',
                'address' => '西安市雁塔区科技路195号',
                'contact_person' => '郑主任',
                'contact_phone' => '029-88765000',
                'description' => '负责陕西地区知识产权快速协同保护工作',
                'status' => 1,
                'sort_order' => 10,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('protection_centers')->count() == 0) {
            DB::table('protection_centers')->insert($protectionCenters);
        }
    }
}
