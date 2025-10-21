<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Workflow;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InitContractWorkflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:init-contract {--reset : 重置现有配置}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化合同工作流配置';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始初始化合同工作流配置...');

        try {
            DB::beginTransaction();

            // 检查是否需要重置
            if ($this->option('reset')) {
                $this->info('重置现有工作流配置...');
                Workflow::where('case_type', '合同')->delete();
            }

            // 检查是否已存在合同工作流
            $existingWorkflow = Workflow::where('case_type', '合同')->first();
            if ($existingWorkflow && !$this->option('reset')) {
                $this->warn('合同工作流已存在，使用 --reset 选项重置配置');
                return 0;
            }

            // 获取可用用户
            $users = User::where('status', 1)->get();
            if ($users->count() < 5) {
                $this->error('系统中可用用户不足，请先创建足够的用户');
                return 1;
            }

            // 创建合同工作流配置
            $workflow = Workflow::create([
                'name' => '合同流程',
                'code' => 'CONTRACT_FLOW',
                'case_type' => '合同',
                'description' => '合同审批流程，包含主管审查、法务审查、财务审查等环节',
                'status' => 1,
                'nodes' => [
                    [
                        'name' => '启动节点',
                        'type' => '启动',
                        'description' => '合同流程启动',
                        'assignee' => [],
                        'timeLimit' => 8,
                        'required' => false
                    ],
                    [
                        'name' => '主管审查',
                        'type' => '审核',
                        'description' => '主管审核合同内容和条款',
                        'assignee' => $users->take(2)->pluck('id')->toArray(),
                        'timeLimit' => 48,
                        'required' => true
                    ],
                    [
                        'name' => '法务审查',
                        'type' => '审核',
                        'description' => '法务部门审查合同条款',
                        'assignee' => $users->skip(2)->take(2)->pluck('id')->toArray(),
                        'timeLimit' => 48,
                        'required' => true
                    ],
                    [
                        'name' => '财务审查',
                        'type' => '审核',
                        'description' => '财务部门审查费用和付款条款',
                        'assignee' => $users->skip(4)->take(2)->pluck('id')->toArray(),
                        'timeLimit' => 48,
                        'required' => true
                    ],
                    [
                        'name' => '最终审批',
                        'type' => '审核',
                        'description' => '高级管理层最终审批',
                        'assignee' => $users->take(1)->pluck('id')->toArray(),
                        'timeLimit' => 24,
                        'required' => true
                    ],
                    [
                        'name' => '合同归档',
                        'type' => '处理',
                        'description' => '合同归档和备案',
                        'assignee' => $users->skip(6)->take(1)->pluck('id')->toArray(),
                        'timeLimit' => 24,
                        'required' => false
                    ],
                    [
                        'name' => '客户通知',
                        'type' => '处理',
                        'description' => '通知客户合同审批结果',
                        'assignee' => $users->skip(7)->take(1)->pluck('id')->toArray(),
                        'timeLimit' => 24,
                        'required' => false
                    ],
                    [
                        'name' => '结束',
                        'type' => '结束',
                        'description' => '合同流程结束',
                        'assignee' => [],
                        'timeLimit' => 8,
                        'required' => false
                    ]
                ],
                'created_by' => 1
            ]);

            DB::commit();

            $this->info('合同工作流配置初始化完成！');
            $this->info("工作流ID: {$workflow->id}");
            $this->info("工作流名称: {$workflow->name}");
            $this->info("节点数量: " . count($workflow->nodes));

            // 显示节点配置信息
            $this->table(
                ['节点', '类型', '描述', '处理人数量', '是否必需'],
                collect($workflow->nodes)->map(function ($node, $index) {
                    return [
                        $index + 1 . '. ' . $node['name'],
                        $node['type'],
                        $node['description'],
                        count($node['assignee']),
                        $node['required'] ? '是' : '否'
                    ];
                })
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('初始化失败: ' . $e->getMessage());
            return 1;
        }
    }
}
