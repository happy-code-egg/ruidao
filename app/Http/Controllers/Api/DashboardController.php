<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use App\Models\Contract;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * 获取首页统计数据
     */
    public function statistics()
    {
        try {
            $userId = Auth::id();
            
            // 基础业务统计
            $contractStats = $this->getContractStatistics();
            
            // 我的待办任务统计
            $myTasksCount = $this->workflowService->getPendingTasks($userId)->count();
            
            // 工作流统计
            $workflowStats = $this->getWorkflowStatistics();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'contract_stats' => $contractStats,
                    'my_tasks_count' => $myTasksCount,
                    'workflow_stats' => $workflowStats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取统计数据失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取我的待办任务
     */
    public function myTasks()
    {
        try {
            $userId = Auth::id();
            $tasks = $this->workflowService->getPendingTasks($userId);

            // 格式化任务数据
            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'instance_id' => $task->instance_id,
                    'node_name' => $task->node_name,
                    'business_title' => $task->instance->business_title,
                    'business_type' => $task->instance->business_type,
                    'business_type_text' => $task->instance->getBusinessTypeText(),
                    'created_at' => $task->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $task->created_at->diffForHumans(),
                    'workflow_name' => $task->instance->workflow->name ?? '',
                    'creator_name' => $task->instance->creator->real_name ?? $task->instance->creator->username,
                ];
            });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $formattedTasks
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取待办任务失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取最新动态
     */
    public function recentActivities()
    {
        try {
            // 获取最近的工作流处理记录
            $recentProcesses = WorkflowProcess::with([
                'instance.workflow',
                'processor'
            ])
            ->where('action', '!=', 'pending')
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get();

            $activities = $recentProcesses->map(function ($process) {
                return [
                    'id' => $process->id,
                    'type' => 'workflow_process',
                    'title' => $process->instance->business_title,
                    'description' => $process->node_name . ' - ' . $process->getActionText(),
                    'processor_name' => $process->processor->real_name ?? $process->processor->username,
                    'processed_at' => $process->processed_at->format('Y-m-d H:i:s'),
                    'processed_at_human' => $process->processed_at->diffForHumans(),
                    'business_type' => $process->instance->business_type,
                    'business_type_text' => $process->instance->getBusinessTypeText(),
                ];
            });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取最新动态失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取合同统计数据
     */
    private function getContractStatistics()
    {
        $currentMonth = now()->format('Y-m');
        
        return [
            'total_contracts' => Contract::count(),
            'month_new_contracts' => Contract::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth])->count(),
            'pending_approval_contracts' => WorkflowInstance::where('business_type', 'contract')
                ->where('status', 'pending')
                ->count(),
            'completed_contracts' => WorkflowInstance::where('business_type', 'contract')
                ->where('status', 'completed')
                ->count(),
        ];
    }

    /**
     * 获取工作流统计数据
     */
    private function getWorkflowStatistics()
    {
        return [
            'total_instances' => WorkflowInstance::count(),
            'pending_instances' => WorkflowInstance::where('status', 'pending')->count(),
            'completed_instances' => WorkflowInstance::where('status', 'completed')->count(),
            'rejected_instances' => WorkflowInstance::where('status', 'rejected')->count(),
            'total_pending_tasks' => WorkflowProcess::where('action', 'pending')->count(),
        ];
    }

    /**
     * 获取系统通知
     */
    public function notifications()
    {
        try {
            // 这里可以添加系统通知逻辑
            $notifications = [
                [
                    'id' => 1,
                    'type' => 'system',
                    'title' => '系统维护通知',
                    'content' => '系统将于今晚22:00进行维护，预计维护时间1小时',
                    'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                    'is_read' => false,
                ],
                [
                    'id' => 2,
                    'type' => 'feature',
                    'title' => '新功能上线',
                    'content' => '工作流管理功能已上线，支持合同审批流程',
                    'created_at' => now()->subDays(1)->format('Y-m-d H:i:s'),
                    'is_read' => false,
                ],
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $notifications
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取通知失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取快速操作数据
     */
    public function quickActions()
    {
        try {
            $actions = [
                [
                    'name' => '新建合同',
                    'icon' => 'el-icon-document-add',
                    'route' => '/contracts/create',
                    'permission' => 'contract.create',
                ],
                [
                    'name' => '我的待办',
                    'icon' => 'el-icon-bell',
                    'route' => '/dashboard/tasks',
                    'permission' => null,
                ],
                [
                    'name' => '合同列表',
                    'icon' => 'el-icon-document',
                    'route' => '/contracts',
                    'permission' => 'contract.view',
                ],
                [
                    'name' => '工作流配置',
                    'icon' => 'el-icon-setting',
                    'route' => '/config/system/workflow',
                    'permission' => 'workflow.config',
                ],
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $actions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取快速操作失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
