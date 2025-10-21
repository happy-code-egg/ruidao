<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateWorkflowsFinalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:update-final';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '插入最后的财务相关工作流数据（8-14）';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始插入最后的财务相关工作流数据...');

        try {
            // 定义财务相关的工作流数据
            $workflows = [
                [
                    'id' => 8,
                    'name' => '请款',
                    'code' => 'PAYMENT_FLOW',
                    'case_type' => '财务',
                    'description' => '请款流程，涉及，填写请款的时候，如果正在请款中，没有走完流程的，不能再次提交',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '请款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '请款流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 9,
                    'name' => '收款',
                    'code' => 'RECEIVE_FLOW',
                    'case_type' => '财务',
                    'description' => '收款流程',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '收款流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '收款流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '开票流程，没有走完的流程，可以撤回和删除。流程走完以后，要把这个开票与收款单做关联',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '开票流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '财务开票', 'type' => '处理', 'description' => '财务开票', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '发起人确认', 'type' => '确认', 'description' => '发起人确认', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '开票流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '支出流程，没有走完的流程，可以撤回和删除。流程走完，相就出款日期和出款单号记录到费用里面',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '支出流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '财务付款', 'type' => '处理', 'description' => '财务付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '支出流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            // 插入工作流8-11
            foreach ($workflows as $workflow) {
                DB::table('workflows')->insert($workflow);
            }

            $this->info('已插入工作流 8-11');
            $this->info('请运行 php artisan workflows:update-commission 来插入提成相关工作流');

            return 0;
        } catch (\Exception $e) {
            $this->error('插入财务工作流数据失败: ' . $e->getMessage());
            return 1;
        }
    }
}
