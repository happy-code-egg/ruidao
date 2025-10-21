<?php

namespace App\Console\Commands\Config;

use App\Models\Workflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WorkflowsCommand extends Command
{
    protected $signature = 'config:workflows';
    protected $description = '导入流程配置数据（硬编码方式）';

    public function handle()
    {
        $this->info('开始导入流程配置数据...');

        try {
            // 1. 清空当前表
            $this->info('清空workflows表...');
            DB::table('workflows')->truncate();

            // 2. 插入硬编码的流程数据
            $workflows = $this->getWorkflowData();
            
            foreach ($workflows as $workflow) {
                Workflow::create($workflow);
                $this->info("已创建流程: {$workflow['name']}");
            }

            $this->info("流程配置导入完成！共导入 " . count($workflows) . " 个流程");
            return 0;

        } catch (\Exception $e) {
            $this->error("导入失败: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * 获取硬编码的流程数据
     */
    private function getWorkflowData(): array
    {
        return [
            [
                'name' => '合同流程',
                'code' => 'CONTRACT_FLOW',
                'description' => '合同审核流程',
                'case_type' => 'contract',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', '主管审查', '流程审查', '流程复核', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '立案流程(商版专)',
                'code' => 'CASE_BUSINESS_FLOW',
                'description' => '商标版权专利立案流程',
                'case_type' => 'trademark',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动（这个是根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起）', 
                    '客服核实', '', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '立案流程（科服）',
                'code' => 'CASE_TECH_SERVICE_FLOW',
                'description' => '科服立案流程',
                'case_type' => 'tech_service',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动（这个是根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起）', 
                    '客服核实（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '科服部审核', '客服再核', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '配案流程',
                'code' => 'ASSIGN_CASE_FLOW',
                'description' => '配案流程',
                'case_type' => 'assignment',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动（严格上讲这里，没有单独启动，是其它地方发起直接到配案人的）', 
                    '主管分配', '', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '核稿流程',
                'code' => 'PROOF_FLOW',
                'description' => '核稿流程',
                'case_type' => 'review',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', '核稿', '', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '递交流程',
                'code' => 'SUBMIT_FLOW',
                'description' => '递交流程',
                'case_type' => 'submission',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '流程初核（到达这个节点，需要向这个处理事项的二次提交和一次提交写日期）', 
                    '流程复核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '递交官方（有用户和电脑网卡检查的白名单）', 
                    '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '案件更新',
                'code' => 'CASE_UPDATE_FLOW',
                'description' => '案件更新流程',
                'case_type' => 'update',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', '流程审核', '', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '请款',
                'code' => 'PAYMENT_REQUEST_FLOW',
                'description' => '请款流程',
                'case_type' => 'payment',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '流程审核', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '收款',
                'code' => 'RECEIVE_PAYMENT_FLOW',
                'description' => '收款流程',
                'case_type' => 'payment',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '开票',
                'code' => 'INVOICE_FLOW',
                'description' => '开票流程',
                'case_type' => 'finance',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '财务开票', '发起人确认', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '支出',
                'code' => 'EXPENSE_FLOW',
                'description' => '支出流程',
                'case_type' => 'finance',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '财务付款', '', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '缴费',
                'code' => 'PAY_FEE_FLOW',
                'description' => '缴费流程',
                'case_type' => 'finance',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '财务付款', '流程确认', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '运营提成',
                'code' => 'OPERATION_COMMISSION_FLOW',
                'description' => '运营提成流程',
                'case_type' => 'commission',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '流程审核', '财务审核', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
            [
                'name' => '商务提成',
                'code' => 'BUSINESS_COMMISSION_FLOW',
                'description' => '商务提成流程',
                'case_type' => 'commission',
                'status' => 1,
                'nodes' => $this->createNodes([
                    '启动', 
                    '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 
                    '流程审核', '财务审核', '', '', '', '结束'
                ]),
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ],
        ];
    }

    /**
     * 创建标准的8节点结构
     */
    private function createNodes(array $nodeNames): array
    {
        $nodes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $nodeName = $nodeNames[$i] ?? '';
            $nodeType = $this->getNodeType($i, $nodeName);
            $assigneeUsers = $this->getNodeAssignees($nodeName);
            
            $nodes[] = [
                'id' => $i + 1,
                'name' => $nodeName,
                'type' => $nodeType,
                'auto_pass' => $nodeType === 'auto', // 启动和结束节点自动通过
                'assignee_users' => $assigneeUsers,
                'sort_order' => $i + 1,
            ];
        }
        
        return $nodes;
    }

    /**
     * 获取节点类型
     */
    private function getNodeType(int $index, string $nodeName): string
    {
        if ($index === 0 || $index === 7) {
            return 'auto'; // 启动和结束节点
        }
        
        if (empty($nodeName)) {
            return 'auto'; // 空节点自动通过
        }
        
        return 'manual'; // 需要人工处理
    }

    /**
     * 获取节点分配的用户（示例数据，实际应根据需求调整）
     */
    private function getNodeAssignees(string $nodeName): array
    {
        // 根据节点名称中的描述分配默认用户
        if (strpos($nodeName, '主管') !== false) {
            return [2, 3]; // 主管用户ID示例
        }
        
        if (strpos($nodeName, '客服') !== false) {
            return [4, 5]; // 客服用户ID示例
        }
        
        if (strpos($nodeName, '财务') !== false) {
            return [6, 7]; // 财务用户ID示例
        }
        
        if (strpos($nodeName, '科服') !== false) {
            return [8, 9]; // 科服用户ID示例
        }
        
        if (strpos($nodeName, '核稿') !== false) {
            return [10, 11]; // 核稿用户ID示例
        }
        
        return [1]; // 默认管理员
    }
}

/*
硬编码流程配置说明:
- 所有流程都采用固定8节点结构
- 启动节点(索引0)和结束节点(索引7)自动通过
- 空节点自动通过
- 节点人员分配根据节点名称智能匹配:
  * 主管类: 用户ID 2,3
  * 客服类: 用户ID 4,5  
  * 财务类: 用户ID 6,7
  * 科服类: 用户ID 8,9
  * 核稿类: 用户ID 10,11
  * 默认: 管理员(用户ID 1)

节点结构:
- id: 节点ID (1-8)
- name: 节点名称
- type: 节点类型 (auto/manual)
- auto_pass: 是否自动通过
- assignee_users: 分配用户ID数组
- sort_order: 排序

使用方法:
php artisan config:workflows
*/
