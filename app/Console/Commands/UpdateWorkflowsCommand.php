<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateWorkflowsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新工作流数据，将所有流程扩展到8个节点';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始更新工作流数据...');

        try {
            // 清空现有数据
            DB::table('workflows')->truncate();
            $this->info('已清空现有工作流数据');

            // 定义新的工作流数据
            $workflows = [
                [
                    'id' => 1,
                    'name' => '合同流程',
                    'code' => 'CONTRACT_FLOW',
                    'case_type' => '合同',
                    'description' => '合同审批流程，只有第一个节点可选到客户资料记录的业务员的主管审核',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动节点', 'type' => '启动', 'description' => '合同流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '主管审查', 'type' => '审核', 'description' => '主管审核，可配置，可不配置', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程审查', 'type' => '审核', 'description' => '流程审查', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '流程复核', 'type' => '审核', 'description' => '流程复核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '合同流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 2,
                    'name' => '立案流程(商版专)',
                    'code' => 'CASE_SIMPLE_FLOW',
                    'case_type' => '专利',
                    'description' => '立案流程（商版专），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '客服核实', 'type' => '审核', 'description' => '客服核实', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '节点3', 'type' => '审核', 'description' => '节点3', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点4', 'type' => '审核', 'description' => '节点4', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '立案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'id' => 3,
                    'name' => '立案流程（科服）',
                    'code' => 'CASE_TECH_FLOW',
                    'case_type' => '专利',
                    'description' => '立案流程（科服），根据合同的某个状态，使这里有可以开始处理的数据，也可以没有合同，直接发起',
                    'status' => 1,
                    'nodes' => json_encode([
                        ['name' => '启动', 'type' => '启动', 'description' => '立案流程启动', 'assignee' => [], 'timeLimit' => 8, 'required' => false],
                        ['name' => '客服核实', 'type' => '审核', 'description' => '客服核实（固定一个或几个人备选审核，流动转的时候选定一个）', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '科服部审核', 'type' => '审核', 'description' => '科服部审核', 'assignee' => [], 'timeLimit' => 48, 'required' => true],
                        ['name' => '客服再核', 'type' => '审核', 'description' => '客服再核', 'assignee' => [], 'timeLimit' => 24, 'required' => true],
                        ['name' => '节点5', 'type' => '审核', 'description' => '节点5', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点6', 'type' => '审核', 'description' => '节点6', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '节点7', 'type' => '审核', 'description' => '节点7', 'assignee' => [], 'timeLimit' => 24, 'required' => false],
                        ['name' => '结束', 'type' => '结束', 'description' => '立案流程结束', 'assignee' => [], 'timeLimit' => 8, 'required' => false]
                    ]),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            // 插入前3个工作流
            foreach ($workflows as $workflow) {
                DB::table('workflows')->insert($workflow);
            }

            $this->info('已插入前3个工作流数据');
            $this->info('工作流数据更新完成！');
            $this->info('请运行 php artisan workflows:update-remaining 来插入剩余的工作流');

            return 0;
        } catch (\Exception $e) {
            $this->error('更新工作流数据失败: ' . $e->getMessage());
            return 1;
        }
    }
}
