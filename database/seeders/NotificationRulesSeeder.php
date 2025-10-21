<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationRule;
use App\Models\FileCategories;

class NotificationRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清空现有数据
        NotificationRule::truncate();

        // 获取现有的文件分类数据
        $contractService = FileCategories::where('main_category', '合同文件')
            ->where('sub_category', '服务合同')->first();
        $contractNda = FileCategories::where('main_category', '合同文件')
            ->where('sub_category', '保密协议')->first();
        $techDisclosure = FileCategories::where('main_category', '技术资料')
            ->where('sub_category', '技术交底书')->first();
        $businessQuote = FileCategories::where('main_category', '商务资料')
            ->where('sub_category', '报价单')->first();
        $financeInvoice = FileCategories::where('main_category', '财务资料')
            ->where('sub_category', '发票')->first();

        // 服务合同处理规则
        if ($contractService) {
            NotificationRule::create([
                'name' => '服务合同签署通知规则',
                'description' => '收到服务合同后的处理规则',
                'rule_type' => 'add_process',
                'file_category_id' => $contractService->id,
                'conditions' => [
                    'file_categories' => ['服务合同'],
                    'main_category' => ['合同文件'],
                    'sub_category' => ['服务合同']
                ],
                'actions' => [
                    'create_process' => '合同审核',
                    'update_status' => '待审核',
                    'send_notification' => true
                ],
                'is_config' => 'yes',
                'process_item' => '合同审核',
                'process_status' => 'pending',
                'is_upload' => 'yes',
                'transfer_target' => 'no',
                'attachment_config' => [
                    'fields' => ['case_name', 'customer_name', 'fixed_text'],
                    'format' => '{case_name}-{customer_name}-服务合同'
                ],
                'generated_filename' => '服务合同-客户名称-审核.pdf',
                'processor' => 'case_handler',
                'fixed_personnel' => null,
                'internal_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 3
                ],
                'customer_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 5
                ],
                'official_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 7
                ],
                'is_effective' => 1,
                'status' => 1,
                'priority' => 5,
                'sort_order' => 1,
                'created_by' => 1,
                'updater' => '系统管理员'
            ]);
        }

        // 保密协议处理规则
        if ($contractNda) {
            NotificationRule::create([
                'name' => '保密协议签署通知规则',
                'description' => '收到保密协议后的处理规则',
                'rule_type' => 'update_process',
                'file_category_id' => $contractNda->id,
                'conditions' => [
                    'file_categories' => ['保密协议'],
                    'main_category' => ['合同文件'],
                    'sub_category' => ['保密协议']
                ],
                'actions' => [
                    'update_status' => '已签署',
                    'create_process' => '归档处理',
                    'send_notification' => true
                ],
                'is_config' => 'yes',
                'process_item' => '归档处理',
                'process_status' => 'pending',
                'is_upload' => 'no',
                'transfer_target' => 'yes',
                'processor' => 'case_business',
                'internal_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 1
                ],
                'customer_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 3
                ],
                'is_effective' => 1,
                'status' => 1,
                'priority' => 3,
                'sort_order' => 2,
                'created_by' => 1,
                'updater' => '系统管理员'
            ]);
        }

        // 技术交底书处理规则
        if ($techDisclosure) {
            NotificationRule::create([
                'name' => '技术交底书审核规则',
                'description' => '收到技术交底书后的审核处理规则',
                'rule_type' => 'add_process',
                'file_category_id' => $techDisclosure->id,
                'conditions' => [
                    'file_categories' => ['技术交底书'],
                    'main_category' => ['技术资料'],
                    'sub_category' => ['技术交底书']
                ],
                'actions' => [
                    'create_process' => '技术审核',
                    'update_status' => '技术审核中',
                    'send_notification' => true,
                    'calculate_deadline' => true
                ],
                'is_config' => 'yes',
                'process_item' => '技术审核',
                'process_status' => 'pending',
                'is_upload' => 'yes',
                'transfer_target' => 'no',
                'attachment_config' => [
                    'fields' => ['case_name', 'applicant', 'fixed_text'],
                    'format' => '{case_name}-{applicant}-技术审核报告'
                ],
                'processor' => 'fixed_personnel',
                'fixed_personnel' => '技术审核员',
                'internal_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 5
                ],
                'customer_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 7
                ],
                'official_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 10
                ],
                'internal_priority_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 3
                ],
                'customer_priority_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 5
                ],
                'is_effective' => 1,
                'status' => 1,
                'priority' => 8,
                'sort_order' => 3,
                'created_by' => 1,
                'updater' => '系统管理员'
            ]);
        }

        // 报价单处理规则
        if ($businessQuote) {
            NotificationRule::create([
                'name' => '报价单审批规则',
                'description' => '收到报价单后的审批处理规则',
                'rule_type' => 'update_status',
                'file_category_id' => $businessQuote->id,
                'conditions' => [
                    'file_categories' => ['报价单'],
                    'main_category' => ['商务资料'],
                    'sub_category' => ['报价单']
                ],
                'actions' => [
                    'update_status' => '待审批',
                    'create_process' => '价格审核',
                    'send_notification' => true,
                    'update_case_info' => true
                ],
                'is_config' => 'yes',
                'process_item' => '价格审核',
                'process_status' => 'pending',
                'is_upload' => 'yes',
                'transfer_target' => 'yes',
                'processor' => 'case_handler',
                'internal_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 2
                ],
                'customer_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 4
                ],
                'official_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 7
                ],
                'complete_date' => [
                    'base_date' => 'approval_date',
                    'months' => 0,
                    'days' => 1
                ],
                'is_effective' => 1,
                'status' => 1,
                'priority' => 6,
                'sort_order' => 4,
                'created_by' => 1,
                'updater' => '系统管理员'
            ]);
        }

        // 发票处理规则
        if ($financeInvoice) {
            NotificationRule::create([
                'name' => '发票处理规则',
                'description' => '收到发票后的财务处理规则',
                'rule_type' => 'add_process',
                'file_category_id' => $financeInvoice->id,
                'conditions' => [
                    'file_categories' => ['发票'],
                    'main_category' => ['财务资料'],
                    'sub_category' => ['发票']
                ],
                'actions' => [
                    'create_process' => '财务入账',
                    'update_status' => '财务处理中',
                    'send_notification' => true
                ],
                'is_config' => 'yes',
                'process_item' => '财务入账',
                'process_status' => 'processing',
                'is_upload' => 'no',
                'transfer_target' => 'no',
                'processor' => 'fixed_personnel',
                'fixed_personnel' => '财务人员',
                'internal_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 3
                ],
                'customer_deadline' => [
                    'base_date' => 'document_date',
                    'months' => 0,
                    'days' => 5
                ],
                'is_effective' => 1,
                'status' => 1,
                'priority' => 4,
                'sort_order' => 5,
                'created_by' => 1,
                'updater' => '系统管理员'
            ]);
        }

        // 通用规则（测试用）
        NotificationRule::create([
            'name' => '通用文件处理规则',
            'description' => '适用于所有文件的通用处理规则',
            'rule_type' => 'update_info',
            'file_category_id' => null, // 通用规则不关联特定文件分类
            'conditions' => [
                'file_categories' => ['所有'],
                'main_category' => ['通用'],
                'sub_category' => ['通用']
            ],
            'actions' => [
                'update_case_info' => true,
                'log_activity' => true,
                'send_notification' => false
            ],
            'is_config' => 'no',
            'process_item' => '文件归档',
            'process_status' => 'completed',
            'is_upload' => 'yes',
            'transfer_target' => 'no',
            'processor' => 'fixed_personnel',
            'fixed_personnel' => '文档管理员',
            'internal_deadline' => [
                'base_date' => 'document_date',
                'months' => 0,
                'days' => 1
            ],
            'is_effective' => 1,
            'status' => 1,
            'priority' => 1,
            'sort_order' => 10,
            'created_by' => 1,
            'updater' => '系统管理员'
        ]);

        // 已禁用的规则（测试用）
        NotificationRule::create([
            'name' => '已禁用的规则示例',
            'description' => '这是一个已禁用的规则，用于测试筛选功能',
            'rule_type' => 'add_process',
            'file_category_id' => null,
            'conditions' => [
                'file_categories' => ['测试'],
                'main_category' => ['测试'],
                'sub_category' => ['测试']
            ],
            'actions' => [
                'test_action' => true
            ],
            'is_config' => 'no',
            'process_item' => '测试',
            'process_status' => 'pending',
            'is_upload' => 'no',
            'transfer_target' => 'no',
            'processor' => 'case_handler',
            'is_effective' => 0, // 无效
            'status' => 0, // 禁用
            'priority' => 1,
            'sort_order' => 999,
            'created_by' => 1,
            'updater' => '测试用户'
        ]);

        echo "NotificationRules seeded successfully!\n";
    }
}