<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        $processRules = [
            // 专利相关规则
            [
                'name' => '专利申请审查规则',
                'description' => '专利申请提交后自动生成审查处理事项',
                'rule_type' => 'add_process',
                'process_item_id' => 1, // 关联到"专利-中国-新申请"处理事项
                'case_type' => '专利',
                'business_type' => '申请',
                'application_type' => '发明专利',
                'country' => '中国',
                'process_item_type' => '审查处理',
                'conditions' => json_encode([
                    'trigger_event' => '申请提交',
                    'case_status' => '已受理',
                    'application_type' => '发明专利'
                ]),
                'actions' => json_encode([
                    'create_process' => '申请审查',
                    'assign_to' => '审查部门',
                    'set_deadline' => true
                ]),
                'generate_or_complete' => 'generate',
                'processor' => 'fixed',
                'fixed_personnel' => '1', // 用户ID
                'is_assign_case' => true,
                'internal_deadline' => json_encode([
                    'base_date' => 'receive_date',
                    'months' => 2,
                    'days' => 0
                ]),
                'customer_deadline' => json_encode([
                    'base_date' => 'issue_date',
                    'months' => 3,
                    'days' => 0
                ]),
                'official_deadline' => json_encode([
                    'base_date' => 'priority_date',
                    'months' => 18,
                    'days' => 0
                ]),
                'complete_date' => json_encode([
                    'base_date' => 'process_start',
                    'months' => 0,
                    'days' => 30
                ]),
                'status' => 1,
                'priority' => 1,
                'is_effective' => true,
                'sort_order' => 1,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '专利答复审查意见规则',
                'description' => '收到审查意见通知书后生成答复处理事项',
                'rule_type' => 'add_process',
                'process_item_id' => 2, // 关联到"专利-中国-补正"处理事项
                'case_type' => '专利',
                'business_type' => '申请',
                'application_type' => '发明专利',
                'country' => '中国',
                'process_item_type' => '答复处理',
                'conditions' => json_encode([
                    'trigger_event' => '收到审查意见',
                    'document_type' => '审查意见通知书'
                ]),
                'actions' => json_encode([
                    'create_process' => '答复审查意见',
                    'assign_to' => '代理师',
                    'notify_client' => true
                ]),
                'generate_or_complete' => 'generate',
                'processor' => '代理师',
                'fixed_personnel' => '李四',
                'is_assign_case' => true,
                'internal_deadline' => json_encode([
                    'base_date' => 'notice_date',
                    'months' => 2,
                    'days' => 0
                ]),
                'customer_deadline' => json_encode([
                    'base_date' => 'notice_date',
                    'months' => 3,
                    'days' => 0
                ]),
                'official_deadline' => json_encode([
                    'base_date' => 'notice_date',
                    'months' => 4,
                    'days' => 0
                ]),
                'complete_date' => null,
                'status' => 1,
                'priority' => 2,
                'is_effective' => true,
                'sort_order' => 2,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // 商标相关规则
            [
                'name' => '商标注册申请规则',
                'description' => '商标注册申请提交后的处理流程',
                'rule_type' => 'add_process',
                'process_item_id' => 4, // 关联到"商标-中国-新申请"处理事项
                'case_type' => '商标',
                'business_type' => '申请',
                'application_type' => '商标注册',
                'country' => '中国',
                'process_item_type' => '申请处理',
                'conditions' => json_encode([
                    'trigger_event' => '申请提交',
                    'application_type' => '商标注册'
                ]),
                'actions' => json_encode([
                    'create_process' => '商标申请审查',
                    'assign_to' => '商标部门'
                ]),
                'generate_or_complete' => 'generate',
                'processor' => '商标代理师',
                'fixed_personnel' => '王五',
                'is_assign_case' => true,
                'internal_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 1,
                    'days' => 0
                ]),
                'customer_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 2,
                    'days' => 0
                ]),
                'official_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 9,
                    'days' => 0
                ]),
                'complete_date' => null,
                'status' => 1,
                'priority' => 1,
                'is_effective' => true,
                'sort_order' => 3,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // 美国专利规则
            [
                'name' => '美国专利IDS提交规则',
                'description' => '美国专利申请需要提交IDS文件',
                'rule_type' => 'add_process',
                'process_item_id' => 3, // 关联到"专利-美国-提交IDS"处理事项
                'case_type' => '专利',
                'business_type' => '申请',
                'application_type' => '发明专利',
                'country' => '美国',
                'process_item_type' => '文件提交',
                'conditions' => json_encode([
                    'trigger_event' => '申请提交',
                    'country' => '美国',
                    'application_type' => '发明专利'
                ]),
                'actions' => json_encode([
                    'create_process' => '提交IDS',
                    'assign_to' => '美国部门'
                ]),
                'generate_or_complete' => 'generate',
                'processor' => '美国代理师',
                'fixed_personnel' => '赵六',
                'is_assign_case' => true,
                'internal_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 3,
                    'days' => 0
                ]),
                'customer_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 4,
                    'days' => 0
                ]),
                'official_deadline' => json_encode([
                    'base_date' => 'application_date',
                    'months' => 6,
                    'days' => 0
                ]),
                'complete_date' => null,
                'status' => 1,
                'priority' => 2,
                'is_effective' => true,
                'sort_order' => 4,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // 状态更新规则
            [
                'name' => '专利授权状态更新规则',
                'description' => '专利获得授权后更新项目状态',
                'rule_type' => 'update_status',
                'process_item_id' => 1, // 关联到"专利-中国-新申请"处理事项
                'case_type' => '专利',
                'business_type' => '申请',
                'application_type' => '发明专利',
                'country' => '中国',
                'process_item_type' => '状态更新',
                'conditions' => json_encode([
                    'trigger_event' => '收到授权通知',
                    'document_type' => '授权通知书'
                ]),
                'actions' => json_encode([
                    'update_status' => '已授权',
                    'notify_client' => true,
                    'create_process' => '缴纳授权费'
                ]),
                'generate_or_complete' => 'complete',
                'processor' => '项目经理',
                'fixed_personnel' => '孙七',
                'is_assign_case' => false,
                'internal_deadline' => null,
                'customer_deadline' => null,
                'official_deadline' => null,
                'complete_date' => json_encode([
                    'base_date' => 'notice_date',
                    'months' => 0,
                    'days' => 1
                ]),
                'status' => 1,
                'priority' => 3,
                'is_effective' => true,
                'sort_order' => 5,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // 无效规则（用于测试）
            [
                'name' => '已废弃的规则',
                'description' => '这是一个已经废弃的规则，用于测试筛选功能',
                'rule_type' => 'add_process',
                'process_item_id' => null, // 故意保留为null作为测试数据
                'case_type' => '专利',
                'business_type' => '申请',
                'application_type' => '实用新型',
                'country' => '中国',
                'process_item_type' => '废弃处理',
                'conditions' => json_encode([
                    'trigger_event' => '测试事件'
                ]),
                'actions' => json_encode([
                    'create_process' => '测试处理'
                ]),
                'generate_or_complete' => 'generate',
                'processor' => '测试人员',
                'fixed_personnel' => '测试员',
                'is_assign_case' => false,
                'internal_deadline' => null,
                'customer_deadline' => null,
                'official_deadline' => null,
                'complete_date' => null,
                'status' => 0,
                'priority' => 99,
                'is_effective' => false,
                'sort_order' => 99,
                'updated_by' => '系统管理员',
                'created_by' => 1,
                'updated_by_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('process_rules')->insert($processRules);
        
        if ($this->command) {
            $this->command->info('处理事项规则数据插入成功！共插入 ' . count($processRules) . ' 条记录。');
        }
    }
}
