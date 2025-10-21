<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkConfig;

class ParksConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parksConfig = [
            [
                'park_name' => '成都高新技术产业开发区',
                'park_code' => 'CHENGDU_HITECH',
                'description' => '成都高新区是国家级高新技术产业开发区',
                'address' => '四川省成都市高新区天府大道中段801号',
                'contact_person' => '园区管委会',
                'contact_phone' => '028-85311111',
                'is_valid' => 1,
                'sort_order' => 10,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '成都天府新区',
                'park_code' => 'CHENGDU_TIANFU',
                'description' => '国家级新区，重点发展现代服务业和高端制造业',
                'address' => '四川省成都市天府新区华阳街道',
                'contact_person' => '新区管委会',
                'contact_phone' => '028-85322222',
                'is_valid' => 1,
                'sort_order' => 20,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '金牛高新技术产业园',
                'park_code' => 'JINNIU_HITECH',
                'description' => '以电子信息、生物医药为主导产业的高新技术园区',
                'address' => '四川省成都市金牛区金牛大道',
                'contact_person' => '园区招商部',
                'contact_phone' => '028-85333333',
                'is_valid' => 1,
                'sort_order' => 30,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '郫都区科技园',
                'park_code' => 'PIDU_TECH',
                'description' => '以高校产学研合作为特色的科技园区',
                'address' => '四川省成都市郫都区犀浦镇',
                'contact_person' => '科技园管理处',
                'contact_phone' => '028-85344444',
                'is_valid' => 1,
                'sort_order' => 40,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '双流空港经济区',
                'park_code' => 'SHUANGLIU_AIRPORT',
                'description' => '依托双流国际机场的临空经济区',
                'address' => '四川省成都市双流区空港经济区',
                'contact_person' => '经济区管委会',
                'contact_phone' => '028-85355555',
                'is_valid' => 1,
                'sort_order' => 50,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '泸州酒业集中发展区',
                'park_code' => 'LUZHOU_LIQUOR',
                'description' => '以白酒产业为核心的特色产业园区',
                'address' => '四川省泸州市纳溪区酒业园区',
                'contact_person' => '酒业园区办',
                'contact_phone' => '0830-3456666',
                'is_valid' => 1,
                'sort_order' => 60,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '绵阳科技城',
                'park_code' => 'MIANYANG_TECH',
                'description' => '国家科技城，以军工、电子信息为主导',
                'address' => '四川省绵阳市涪城区科技城',
                'contact_person' => '科技城管委会',
                'contact_phone' => '0816-2467777',
                'is_valid' => 1,
                'sort_order' => 70,
                'updater' => '系统初始化',
            ],
            [
                'park_name' => '德阳经济技术开发区',
                'park_code' => 'DEYANG_ECON',
                'description' => '以装备制造业为主导的经济开发区',
                'address' => '四川省德阳市旌阳区经开区',
                'contact_person' => '开发区管委会',
                'contact_phone' => '0838-2478888',
                'is_valid' => 0,
                'sort_order' => 80,
                'updater' => '系统初始化',
            ],
        ];

        foreach ($parksConfig as $park) {
            ParkConfig::updateOrCreate(
                ['park_code' => $park['park_code']],
                $park
            );
        }

        if ($this->command) {


            $this->command->info('园区配置数据初始化完成');


        }
    }
}
