<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InnovationIndicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $innovationIndices = [
            [
                'sort' => 1,
                'name' => '专利创新指数',
                'code' => 'PATENT_INNOVATION_INDEX',
                'index_name' => '专利创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 125.30,
                'description' => '专利创新指数，反映专利申请的创新活跃度',
                'status' => 1,
                'sort_order' => 1,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 2,
                'name' => '商标创新指数',
                'code' => 'TRADEMARK_INNOVATION_INDEX',
                'index_name' => '商标创新指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 118.70,
                'description' => '商标创新指数，反映商标注册的创新活跃度',
                'status' => 1,
                'sort_order' => 2,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 3,
                'name' => '版权创新指数',
                'code' => 'COPYRIGHT_INNOVATION_INDEX',
                'index_name' => '版权创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 132.50,
                'description' => '版权创新指数，反映版权登记的创新活跃度',
                'status' => 1,
                'sort_order' => 3,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 4,
                'name' => '高新技术创新指数',
                'code' => 'HIGH_TECH_INNOVATION_INDEX',
                'index_name' => '高新技术创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 145.20,
                'description' => '高新技术创新指数，反映高新技术领域的创新活跃度',
                'status' => 1,
                'sort_order' => 4,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 5,
                'name' => '人工智能创新指数',
                'code' => 'AI_INNOVATION_INDEX',
                'index_name' => '人工智能创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 168.90,
                'description' => '人工智能创新指数，反映AI领域的创新活跃度',
                'status' => 1,
                'sort_order' => 5,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 6,
                'name' => '生物医药创新指数',
                'code' => 'BIOMEDICAL_INNOVATION_INDEX',
                'index_name' => '生物医药创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 156.40,
                'description' => '生物医药创新指数，反映生物医药领域的创新活跃度',
                'status' => 1,
                'sort_order' => 6,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 7,
                'name' => '新能源创新指数',
                'code' => 'NEW_ENERGY_INNOVATION_INDEX',
                'index_name' => '新能源创新指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 142.80,
                'description' => '新能源创新指数，反映新能源领域的创新活跃度',
                'status' => 1,
                'sort_order' => 7,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 8,
                'name' => '新材料创新指数',
                'code' => 'NEW_MATERIALS_INNOVATION_INDEX',
                'index_name' => '新材料创新指数',
                'index_level' => 'B',
                'base_value' => 100.00,
                'current_value' => 138.60,
                'description' => '新材料创新指数，反映新材料领域的创新活跃度',
                'status' => 1,
                'sort_order' => 8,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 9,
                'name' => '电子信息创新指数',
                'code' => 'ELECTRONIC_INFO_INNOVATION_INDEX',
                'index_name' => '电子信息创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 152.30,
                'description' => '电子信息创新指数，反映电子信息领域的创新活跃度',
                'status' => 1,
                'sort_order' => 9,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'sort' => 10,
                'name' => '综合创新指数',
                'code' => 'COMPREHENSIVE_INNOVATION_INDEX',
                'index_name' => '综合创新指数',
                'index_level' => 'A',
                'base_value' => 100.00,
                'current_value' => 135.70,
                'description' => '综合创新指数，反映整体创新活跃度',
                'status' => 1,
                'sort_order' => 10,
                'updater' => '系统管理员',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('innovation_indices')->count() == 0) {
            DB::table('innovation_indices')->insert($innovationIndices);
            echo "已成功插入 " . count($innovationIndices) . " 条创新指数记录\n";
        } else {
            echo "创新指数表已有数据，跳过插入\n";
        }
    }
}
