<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PriceIndicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceIndices = [
            [
                'sort' => 1,
                'name' => '专利申请价格指数',
                'code' => 'PATENT_APPLICATION_PRICE_INDEX',
                'index_name' => '专利申请价格指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 108.50,
                'description' => '专利申请服务价格指数，反映专利申请费用的变化趋势',
                'status' => 1,
                'sort_order' => 1,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 2,
                'name' => '商标注册价格指数',
                'code' => 'TRADEMARK_REGISTRATION_PRICE_INDEX',
                'index_name' => '商标注册价格指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 105.20,
                'description' => '商标注册服务价格指数，反映商标注册费用的变化趋势',
                'status' => 1,
                'sort_order' => 2,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 3,
                'name' => '版权登记价格指数',
                'code' => 'COPYRIGHT_REGISTRATION_PRICE_INDEX',
                'index_name' => '版权登记价格指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 103.80,
                'description' => '版权登记服务价格指数，反映版权登记费用的变化趋势',
                'status' => 1,
                'sort_order' => 3,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 4,
                'name' => '知识产权维权价格指数',
                'code' => 'IP_ENFORCEMENT_PRICE_INDEX',
                'index_name' => '知识产权维权价格指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 112.30,
                'description' => '知识产权维权服务价格指数，反映维权服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 4,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 5,
                'name' => '知识产权评估价格指数',
                'code' => 'IP_VALUATION_PRICE_INDEX',
                'index_name' => '知识产权评估价格指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 110.70,
                'description' => '知识产权评估服务价格指数，反映评估服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 5,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 6,
                'name' => '专利检索价格指数',
                'code' => 'PATENT_SEARCH_PRICE_INDEX',
                'index_name' => '专利检索价格指数',
                'index_level' => 'C',
                'base_value' => 100.00,
                'current_value' => 106.90,
                'description' => '专利检索服务价格指数，反映检索服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 6,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 7,
                'name' => '知识产权培训价格指数',
                'code' => 'IP_TRAINING_PRICE_INDEX',
                'index_name' => '知识产权培训价格指数',
                'index_level' => 'C',
                'base_value' => 100.00,
                'current_value' => 104.50,
                'description' => '知识产权培训服务价格指数，反映培训服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 7,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 8,
                'name' => '知识产权咨询价格指数',
                'code' => 'IP_CONSULTING_PRICE_INDEX',
                'index_name' => '知识产权咨询价格指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 109.20,
                'description' => '知识产权咨询服务价格指数，反映咨询服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 8,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 9,
                'name' => '国际专利申请价格指数',
                'code' => 'INTERNATIONAL_PATENT_PRICE_INDEX',
                'index_name' => '国际专利申请价格指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 115.60,
                'description' => '国际专利申请服务价格指数，反映国际申请费用的变化趋势',
                'status' => 1,
                'sort_order' => 9,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 10,
                'name' => '综合知识产权服务价格指数',
                'code' => 'COMPREHENSIVE_IP_SERVICE_PRICE_INDEX',
                'index_name' => '综合知识产权服务价格指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 107.80,
                'description' => '综合知识产权服务价格指数，反映整体服务费用的变化趋势',
                'status' => 1,
                'sort_order' => 10,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        echo "开始插入价格指数数据...\n";
        $currentCount = DB::table('price_indices')->count();
        echo "当前价格指数表记录数: {$currentCount}\n";
        
        if ($currentCount == 0) {
            try {
                DB::table('price_indices')->insert($priceIndices);
                $newCount = DB::table('price_indices')->count();
                echo "已成功插入 " . count($priceIndices) . " 条价格指数记录\n";
                echo "插入后记录数: {$newCount}\n";
            } catch (\Exception $e) {
                echo "插入失败: " . $e->getMessage() . "\n";
                throw $e;
            }
        } else {
            echo "价格指数表已有数据，跳过插入\n";
        }
    }
}
