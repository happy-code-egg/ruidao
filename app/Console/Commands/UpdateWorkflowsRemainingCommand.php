<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateWorkflowsRemainingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:update-remaining';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '插入剩余的工作流数据（4-14）';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始插入剩余的工作流数据...');

        try {
            // 定义剩余的工作流数据
            $workflows = [
                [
                    'id' => 4,
                    'name' => '配案流程',
                    'code' => 'ASSIGN_FLOW',
                    'case_type' => '通用',
                    'description' => '配案流程，流动后，就给到某个人分配，分配出去以后就达到某个人的待处理界面',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '配案流程启动（严格上讲这里，没有单独启动，是其它地方发起直接到配案人的）', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管分配', 'type' => '分配', 'description' => '主管分配', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '配案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 5,
                    'name' => '核稿流程',
                    'code' => 'CHECK_FLOW',
                    'case_type' => '通用',
                    'description' => '核稿流程，启动的时候，要选一个核稿人',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '核稿流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '核稿', 'type' => '检查', 'description' => '核稿', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '核稿流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '递交流程，涉及到达流程初核这个节点，要记录日期的问题',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '递交流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审核', 'type' => '审核', 'description' => '主管审核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程初核', 'type' => '审核', 'description' => '流程初核（到达这个节点，需要向这个处理事项的二次提交和一次提交写日期）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程复核', 'type' => '审核', 'description' => '流程复核（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '递交官方', 'type' => '处理', 'description' => '递交官方（有用户和电脑网卡检查的白名单）', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '递交流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
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
                    'description' => '案件更新流程，涉及到，如果是处理事项改成完成，不要要执行完成规则的问题',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '案件更新启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '流程审核', 'type' => '审核', 'description' => '流程审核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '案件更新结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            // 插入工作流4-7
            foreach ($workflows as $workflow) {
                DB::table('workflows')->insert($workflow);
            }

            $this->info('已插入工作流 4-7');
            $this->info('请运行 php artisan workflows:update-final 来插入最后的财务相关工作流');

            return 0;
        } catch (\Exception $e) {
            $this->error('插入剩余工作流数据失败: ' . $e->getMessage());
            return 1;
        }
    }
}
