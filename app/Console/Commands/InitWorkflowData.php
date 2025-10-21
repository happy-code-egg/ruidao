<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitWorkflowData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化工作流数据';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始初始化工作流数据...');

        $workflows = [
            [
                'id' => 1,
                'name' => '合同流程',
                'code' => 'CONTRACT_FLOW',
                'case_type' => '合同',
                'description' => '只有第一个节点，可选到客户资料记录的业务员的主管审核',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '合同流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审查', 'type' => '审核', 'description' => '可选到客户资料记录的业务员的主管审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审查', 'type' => '审核', 'description' => '后面固定人员审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程复核', 'type' => '审核', 'description' => '最终流程复核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '合同流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => '立案流程(商版专)',
                'code' => 'CASE_BUSINESS_FLOW',
                'case_type' => '专利',
                'description' => '这个是根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '客服核实', 'type' => '审核', 'description' => '客服人员核实相关信息', 'assignee' => [1, 2, 3], 'timeLimit' => 48, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '立案流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => '立案流程(科服)',
                'code' => 'CASE_TECH_SERVICE_FLOW',
                'case_type' => '专利',
                'description' => '科服版立案流程，包含客服核实、科服部审核、客服再核等环节',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '客服核实', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [1, 2, 3], 'timeLimit' => 48, 'required' => true],
                    ['name' => '科服部审核', 'type' => '审核', 'description' => '科服部门专业审核', 'assignee' => [4, 5, 6], 'timeLimit' => 72, 'required' => true],
                    ['name' => '客服再核', 'type' => '审核', 'description' => '客服人员再次核实', 'assignee' => [1, 2], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '立案流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => '配案流程',
                'code' => 'ASSIGN_CASE_FLOW',
                'case_type' => '通用',
                'description' => '严格上讲这里，没有单独启动，是其它地方发起直接到配案人的',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '配案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管分配', 'type' => '分配', 'description' => '主管进行案件分配', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '配案流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => '核稿流程',
                'code' => 'PROOF_FLOW',
                'case_type' => '通用',
                'description' => '启动的时候，要选一个核稿人',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '核稿流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '核稿', 'type' => '审核', 'description' => '核稿人员进行稿件核查', 'assignee' => [], 'timeLimit' => 72, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '核稿流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => '递交流程',
                'code' => 'SUBMIT_FLOW',
                'case_type' => '通用',
                'description' => '涉及到达流程初核这个节点，要记录日期的问题',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '递交流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程初核', 'type' => '审核', 'description' => '到达这个节点，需要向这个处理事项的二次提交和一次提交写日期', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '流程复核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '递交官方', 'type' => '处理', 'description' => '有用户和电脑网卡检查的白名单', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '递交流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => '案件更新',
                'code' => 'CASE_UPDATE_FLOW',
                'case_type' => '通用',
                'description' => '这个涉及到，如果是处理事项改成完成，不要要执行完成规则的问题',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '案件更新流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '更新内容的流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '案件更新完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => '请款',
                'code' => 'PAYMENT_REQUEST_FLOW',
                'case_type' => '财务',
                'description' => '填写请款的时候，如果正在请款中，没有走完流程的，不能再次提交',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '请款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '请款流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '请款流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'name' => '收款',
                'code' => 'RECEIVE_PAYMENT_FLOW',
                'case_type' => '财务',
                'description' => '收款流程管理',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '收款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '收款流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'name' => '开票',
                'code' => 'INVOICE_FLOW',
                'case_type' => '财务',
                'description' => '没有走完的流程，可以撤回和删除。流程走完以后，要把这个开票与收款单做关联',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '开票流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务开票', 'type' => '处理', 'description' => '财务部门开具发票', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '发起人确认', 'type' => '确认', 'description' => '发起人确认开票信息', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '开票流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'name' => '支出',
                'code' => 'EXPENSE_FLOW',
                'case_type' => '财务',
                'description' => '没有走完的流程，可以撤回和删除。流程走完，相就出款日期和出款单号记录到费用里面',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '支出流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务付款', 'type' => '处理', 'description' => '财务部门执行付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '支出流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'name' => '缴费',
                'code' => 'PAY_FEE_FLOW',
                'case_type' => '财务',
                'description' => '缴费流程管理',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '缴费流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '财务付款', 'type' => '处理', 'description' => '财务部门执行付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '流程确认', 'type' => '确认', 'description' => '流程最终确认', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '缴费流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'name' => '运营提成',
                'code' => 'OPERATION_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '没有走完的流程，可以撤回和删除。正在走流程的不能再次提起',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '运营提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程部门审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务部门最终审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '运营提成流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'name' => '商务提成',
                'code' => 'BUSINESS_COMMISSION_FLOW',
                'case_type' => '财务',
                'description' => '没有走完的流程，可以撤回和删除。正在走流程的不能再次提起',
                'status' => 1,
                'nodes' => json_encode([
                    ['name' => '启动', 'type' => '启动', 'description' => '商务提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                    ['name' => '主管审核', 'type' => '审核', 'description' => '固定一个或几个人备选审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '流程审核', 'type' => '审核', 'description' => '流程部门审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                    ['name' => '财务审核', 'type' => '审核', 'description' => '财务部门最终审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                    ['name' => '结束', 'type' => '结束', 'description' => '商务提成流程完成', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                ]),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // 清空现有数据
        DB::table('workflows')->truncate();
        
        // 插入新数据
        foreach ($workflows as $workflow) {
            DB::table('workflows')->insert($workflow);
        }

        $this->info('工作流数据初始化完成！共创建了 ' . count($workflows) . ' 个工作流。');
        return 0;
    }
}
