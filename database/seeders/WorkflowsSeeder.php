<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 工作流配置种子数据 - 插入前端定义的预设工作流
     * @return void
     */
    public function run()
    {
        $workflows = [
            [
                'name' => '合同流程',
                'code' => 'CONTRACT_FLOW',
                'case_type' => '合同',
                'description' => '合同审批流程，包含三个固定审批节点',
                'status' => 1,
                'nodes' => [
                    ['name' => '节点1', 'type' => '启动', 'description' => '合同发起审核', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '节点2', 'type' => '审核', 'description' => '合同内容审核', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '节点3', 'type' => '结束', 'description' => '合同审批完成', 'assignee' => [], 'timeLimit' => 24]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '立案流程(简版本)',
                'code' => 'CASE_SIMPLE_FLOW',
                'case_type' => '专利',
                'description' => '简化版立案流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '立案申请启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '老版换新', 'type' => '处理', 'description' => '版本信息更新处理', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '立案核实', 'type' => '审核', 'description' => '立案信息核实', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '立案完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '立案流程(科版)',
                'code' => 'CASE_TECH_FLOW',
                'case_type' => '专利',
                'description' => '技术部门立案流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '技术立案启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '老版换新', 'type' => '处理', 'description' => '技术版本更新', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '老版核实', 'type' => '审核', 'description' => '版本信息核实', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '利限部门核', 'type' => '审核', 'description' => '专利权限部门核查', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '审批申核', 'type' => '审核', 'description' => '最终审批核查', 'assignee' => [], 'timeLimit' => 72],
                    ['name' => '结案', 'type' => '结束', 'description' => '技术立案完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '配案流程',
                'code' => 'ASSIGN_FLOW',
                'case_type' => '通用',
                'description' => '项目分配流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '项目分配启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主官分配', 'type' => '分配', 'description' => '主管进行项目分配', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '结案', 'type' => '结束', 'description' => '分配完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '核检流程',
                'code' => 'CHECK_FLOW',
                'case_type' => '通用',
                'description' => '项目核检流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '核检流程启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '核检', 'type' => '检查', 'description' => '项目信息核查', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '核检完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '溪义流程',
                'code' => 'STREAM_FLOW',
                'case_type' => '通用',
                'description' => '溪义处理流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '溪义流程启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核，可指定备选人员', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '溪程定核', 'type' => '审核', 'description' => '溪程最终确认', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '结案', 'type' => '结束', 'description' => '溪义流程完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '项目更新',
                'code' => 'CASE_UPDATE_FLOW',
                'case_type' => '通用',
                'description' => '项目信息更新流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '项目更新启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '更新内容审核', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '结案', 'type' => '结束', 'description' => '更新完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '请款',
                'code' => 'PAYMENT_FLOW',
                'case_type' => '财务',
                'description' => '款项清算流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '请款流程启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管初审，可指定备选人员', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '流程定核', 'type' => '审核', 'description' => '流程确认核查', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管终审，可指定备选人员', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '请款完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '开票',
                'code' => 'INVOICE_FLOW',
                'case_type' => '财务',
                'description' => '发票开具流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '开票申请启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核开票申请', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '财务开票', 'type' => '处理', 'description' => '财务部门开具发票', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '开票完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '支出',
                'code' => 'EXPENSE_FLOW',
                'case_type' => '财务',
                'description' => '费用支出流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '支出申请启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管初审支出申请', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管复审支出申请', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '财务付款', 'type' => '处理', 'description' => '财务部门执行付款', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '结案', 'type' => '结束', 'description' => '支出完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '收费',
                'code' => 'RECEIVE_FLOW',
                'case_type' => '财务',
                'description' => '费用收取流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '收费流程启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核收费标准', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '财务收款', 'type' => '处理', 'description' => '财务部门确认收款', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '结案', 'type' => '结束', 'description' => '收费完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '运营提成',
                'code' => 'OPERATION_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '运营人员提成计算流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '运营提成计算启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核提成标准', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程部门核查', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务部门最终审核', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '运营提成完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => '商务提成',
                'code' => 'BUSINESS_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '商务人员提成计算流程',
                'status' => 1,
                'nodes' => [
                    ['name' => '启动', 'type' => '启动', 'description' => '商务提成计算启动', 'assignee' => [], 'timeLimit' => 8],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核提成标准', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程部门核查', 'assignee' => [], 'timeLimit' => 24],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务部门最终审核', 'assignee' => [], 'timeLimit' => 48],
                    ['name' => '结案', 'type' => '结束', 'description' => '商务提成完成', 'assignee' => [], 'timeLimit' => 8]
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        // 清空现有数据（可选）
        DB::table('workflows')->truncate();

        // 插入工作流数据
        foreach ($workflows as $workflow) {
            try {
                $inserted = DB::table('workflows')->insert([
                    'name' => $workflow['name'],
                    'code' => $workflow['code'],
                    'case_type' => $workflow['case_type'],
                    'description' => $workflow['description'],
                    'status' => $workflow['status'],
                    'nodes' => json_encode($workflow['nodes']),
                    'created_by' => $workflow['created_by'],
                    'updated_by' => $workflow['updated_by'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if ($this->command) {
                    $this->command->line("插入工作流: {$workflow['name']} - " . ($inserted ? 'Success' : 'Failed'));
                }
            } catch (\Exception $e) {
                if ($this->command) {
                    $this->command->error("插入工作流 {$workflow['name']} 失败: " . $e->getMessage());
                }
            }
        }

        if ($this->command) {
            $this->command->info('工作流种子数据插入完成，共插入 ' . count($workflows) . ' 条记录');
        }
    }
}
