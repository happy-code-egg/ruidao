<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 业务类型表种子数据
     */
    public function run()
    {
        $businessTypes = [
            ['name' => '发明专利', 'code' => 'INVENTION_PATENT', 'description' => '发明专利申请和维护业务', 'status' => 1, 'sort_order' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '实用新型专利', 'code' => 'UTILITY_MODEL_PATENT', 'description' => '实用新型专利申请和维护业务', 'status' => 1, 'sort_order' => 2, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '外观设计专利', 'code' => 'DESIGN_PATENT', 'description' => '外观设计专利申请和维护业务', 'status' => 1, 'sort_order' => 3, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '商标注册', 'code' => 'TRADEMARK_REGISTRATION', 'description' => '商标注册申请业务', 'status' => 1, 'sort_order' => 4, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '商标续展', 'code' => 'TRADEMARK_RENEWAL', 'description' => '商标续展业务', 'status' => 1, 'sort_order' => 5, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '软件著作权', 'code' => 'SOFTWARE_COPYRIGHT', 'description' => '软件著作权登记业务', 'status' => 1, 'sort_order' => 6, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '作品著作权', 'code' => 'WORK_COPYRIGHT', 'description' => '作品著作权登记业务', 'status' => 1, 'sort_order' => 7, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'PCT国际申请', 'code' => 'PCT_APPLICATION', 'description' => 'PCT国际专利申请业务', 'status' => 1, 'sort_order' => 8, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '马德里商标', 'code' => 'MADRID_TRADEMARK', 'description' => '马德里国际商标注册业务', 'status' => 1, 'sort_order' => 9, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '高新技术企业认定', 'code' => 'HIGH_TECH_CERTIFICATION', 'description' => '高新技术企业认定咨询业务', 'status' => 1, 'sort_order' => 10, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '知识产权贯标', 'code' => 'IP_MANAGEMENT_STANDARD', 'description' => '知识产权管理体系认证业务', 'status' => 1, 'sort_order' => 11, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '专利检索分析', 'code' => 'PATENT_SEARCH_ANALYSIS', 'description' => '专利检索和分析服务', 'status' => 1, 'sort_order' => 12, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '知识产权维权', 'code' => 'IP_ENFORCEMENT', 'description' => '知识产权维权诉讼业务', 'status' => 1, 'sort_order' => 13, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '知识产权评估', 'code' => 'IP_VALUATION', 'description' => '知识产权价值评估业务', 'status' => 1, 'sort_order' => 14, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => '知识产权培训', 'code' => 'IP_TRAINING', 'description' => '知识产权培训咨询业务', 'status' => 1, 'sort_order' => 15, 'created_by' => 1, 'updated_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        if (DB::table('business_types')->count() == 0) {
            DB::table('business_types')->insert($businessTypes);
        }
    }
}
