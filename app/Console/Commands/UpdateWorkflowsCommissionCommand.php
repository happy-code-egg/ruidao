<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateWorkflowsCommissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:update-commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '插入缴费和提成相关工作流数据（12-14）';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始插入缴费和提成相关工作流数据...');

        try {
            // 定义缴费和提成相关的工作流数据
            $workflows = [
                [
                    'id' => 12,
                    'name' => '缴费',
                    'code' => 'PAY_FEE_FLOW',
                    'case_type' => '财务',
                    'description' => '缴费流程',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '缴费流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '财务付款', 'type' => '处理', 'description' => '财务付款', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '流程确认', 'type' => '确认', 'description' => '流程确认', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '缴费流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '运营提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '运营提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '财务审核', 'type' => '审核', 'description' => '财务审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '运营提成流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '商务提成流程，没有走完的流程，可以撤回和删除。流程走完以后要添加提成的相应字段内容',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '商务提成流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '财务审核', 'type' => '审核', 'description' => '财务审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '商务提成流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            // 插入工作流12-14
            foreach ($workflows as $workflow) {
                DB::table('workflows')->insert($workflow);
            }

            $this->info('已插入工作流 12-14');
            $this->info('所有工作流数据更新完成！');
            $this->info('共创建了 14 个工作流，每个流程包含 8 个节点');

            // 显示统计信息
            $count = DB::table('workflows')->count();
            $this->info("数据库中现在共有 {$count} 个工作流");

            return 0;
        } catch (\Exception $e) {
            $this->error('插入提成工作流数据失败: ' . $e->getMessage());
            return 1;
        }
    }
}
