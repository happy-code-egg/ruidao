<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TechServiceItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 科技服务事项表种子数据
     */
    public function run()
    {
        // 先获取科技服务类型数据
        $techServiceTypes = DB::table('tech_service_types')->get();
        
        if ($techServiceTypes->isEmpty()) {
            echo "警告：科技服务类型表为空，请先运行 TechServiceTypesSeeder\n";
            return;
        }

        $techServiceItems = [];

        // 为每个科技服务类型创建处理事项
        foreach ($techServiceTypes as $type) {
            // 根据申请类型创建不同的处理事项
            if (strpos($type->apply_type, '企业认定') !== false) {
                // 企业认定类处理事项
                $items = [
                    [
                        'name' => '材料准备',
                        'code' => 'MATERIAL_PREP_' . $type->id,
                        'description' => '准备申请材料，包括企业基本信息、财务报表、研发项目等',
                        'expected_start_date' => '2024-01-15',
                        'internal_deadline' => '2024-02-15',
                        'official_deadline' => '2024-03-01',
                    ],
                    [
                        'name' => '申请提交',
                        'code' => 'APPLICATION_SUBMIT_' . $type->id,
                        'description' => '向相关部门提交申请材料',
                        'expected_start_date' => '2024-02-16',
                        'internal_deadline' => '2024-03-01',
                        'official_deadline' => '2024-03-15',
                    ],
                    [
                        'name' => '专家评审',
                        'code' => 'EXPERT_REVIEW_' . $type->id,
                        'description' => '专家组对申请材料进行评审',
                        'expected_start_date' => '2024-03-16',
                        'internal_deadline' => '2024-04-15',
                        'official_deadline' => '2024-05-01',
                    ],
                    [
                        'name' => '结果公示',
                        'code' => 'RESULT_PUBLISH_' . $type->id,
                        'description' => '评审结果公示',
                        'expected_start_date' => '2024-05-02',
                        'internal_deadline' => '2024-05-15',
                        'official_deadline' => '2024-06-01',
                    ]
                ];
            } elseif (strpos($type->apply_type, '成果转化') !== false || strpos($type->apply_type, '成果类型') !== false) {
                // 成果转化类处理事项
                $items = [
                    [
                        'name' => '项目申报',
                        'code' => 'PROJECT_DECLARE_' . $type->id,
                        'description' => '填写项目申报书，准备相关材料',
                        'expected_start_date' => '2024-01-10',
                        'internal_deadline' => '2024-02-10',
                        'official_deadline' => '2024-02-28',
                    ],
                    [
                        'name' => '技术评估',
                        'code' => 'TECH_ASSESS_' . $type->id,
                        'description' => '对技术方案进行评估',
                        'expected_start_date' => '2024-03-01',
                        'internal_deadline' => '2024-03-31',
                        'official_deadline' => '2024-04-15',
                    ],
                    [
                        'name' => '合同签署',
                        'code' => 'CONTRACT_SIGN_' . $type->id,
                        'description' => '签署项目合同',
                        'expected_start_date' => '2024-04-16',
                        'internal_deadline' => '2024-05-01',
                        'official_deadline' => '2024-05-15',
                    ],
                    [
                        'name' => '项目实施',
                        'code' => 'PROJECT_IMPL_' . $type->id,
                        'description' => '项目实施阶段',
                        'expected_start_date' => '2024-05-16',
                        'internal_deadline' => '2024-12-31',
                        'official_deadline' => '2025-01-31',
                    ],
                    [
                        'name' => '验收结题',
                        'code' => 'PROJECT_ACCEPT_' . $type->id,
                        'description' => '项目验收和结题',
                        'expected_start_date' => '2025-01-01',
                        'internal_deadline' => '2025-02-28',
                        'official_deadline' => '2025-03-31',
                    ]
                ];
            } else {
                // 通用处理事项
                $items = [
                    [
                        'name' => '咨询服务',
                        'code' => 'CONSULT_SERVICE_' . $type->id,
                        'description' => '提供专业咨询服务',
                        'expected_start_date' => '2024-01-01',
                        'internal_deadline' => '2024-01-31',
                        'official_deadline' => '2024-02-15',
                    ],
                    [
                        'name' => '方案制定',
                        'code' => 'PLAN_MAKE_' . $type->id,
                        'description' => '制定实施方案',
                        'expected_start_date' => '2024-02-01',
                        'internal_deadline' => '2024-02-28',
                        'official_deadline' => '2024-03-15',
                    ],
                    [
                        'name' => '执行跟踪',
                        'code' => 'EXEC_TRACK_' . $type->id,
                        'description' => '跟踪执行进度',
                        'expected_start_date' => '2024-03-01',
                        'internal_deadline' => '2024-06-30',
                        'official_deadline' => '2024-07-31',
                    ]
                ];
            }

            // 为每个处理事项添加基础信息
            foreach ($items as $index => $item) {
                $techServiceItems[] = [
                    'tech_service_type_id' => $type->id,
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'description' => $item['description'],
                    'expected_start_date' => $item['expected_start_date'],
                    'internal_deadline' => $item['internal_deadline'],
                    'official_deadline' => $item['official_deadline'],
                    'status' => 1,
                    'sort_order' => $index + 1,
                    'updater' => '系统管理员',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // 检查是否已有数据，避免重复插入
        if (DB::table('tech_service_items')->count() == 0) {
            DB::table('tech_service_items')->insert($techServiceItems);
            echo "已插入 " . count($techServiceItems) . " 条科技服务事项数据\n";
        } else {
            echo "科技服务事项表已有数据，跳过插入\n";
        }
    }
}
