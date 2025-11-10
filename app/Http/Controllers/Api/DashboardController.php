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
 *
 * 功能描述：获取系统首页的综合统计数据，包括合同统计、待办任务统计和工作流统计
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 统计数据对象
 *   - contract_stats (object): 合同统计数据
 *     - total_contracts (int): 合同总数
 *     - month_new_contracts (int): 本月新增合同数
 *     - pending_approval_contracts (int): 待审批合同数
 *     - completed_contracts (int): 已完成合同数
 *   - my_tasks_count (int): 当前用户待办任务数
 *   - workflow_stats (object): 工作流统计数据
 *     - total_instances (int): 工作流实例总数
 *     - pending_instances (int): 待处理工作流实例数
 *     - completed_instances (int): 已完成工作流实例数
 *     - rejected_instances (int): 已拒绝工作流实例数
 *     - total_pending_tasks (int): 总待办任务数
 *
 * 错误响应：
 * - code (int): 状态码，500表示服务器错误
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function statistics()
{
    try {
        // 获取当前认证用户的ID
        $userId = Auth::id();

        // 基础业务统计：获取合同相关统计数据
        $contractStats = $this->getContractStatistics();

        // 我的待办任务统计：通过工作流服务获取当前用户的待办任务数量
        $myTasksCount = $this->workflowService->getPendingTasks($userId)->count();

        // 工作流统计：获取工作流相关统计数据
        $workflowStats = $this->getWorkflowStatistics();

        // 返回成功响应，包含各类统计数据
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
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'code' => 500,
            'msg' => '获取统计数据失败：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}


  /**
 * 获取我的待办任务 (myTasks)
 *
 * 功能描述：获取当前认证用户的待办任务列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 格式化后的待办任务列表
 *   - id (int): 任务ID
 *   - instance_id (int): 工作流实例ID
 *   - node_name (string): 节点名称
 *   - business_title (string): 业务标题
 *   - business_type (string): 业务类型
 *   - business_type_text (string): 业务类型文本描述
 *   - created_at (string): 创建时间（格式：Y-m-d H:i:s）
 *   - created_at_human (string): 人性化的时间显示（如"2小时前"）
 *   - workflow_name (string): 工作流名称
 *   - creator_name (string): 创建者姓名
 */
public function myTasks()
{
    try {
        // 获取当前认证用户的ID
        $userId = Auth::id();

        // 使用工作流服务获取当前用户的待办任务
        $tasks = $this->workflowService->getPendingTasks($userId);

        // 格式化任务数据，将任务对象转换为数组格式
        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,                              // 任务ID
                'instance_id' => $task->instance_id,            // 工作流实例ID
                'node_name' => $task->node_name,                // 节点名称
                'business_title' => $task->instance->business_title,                    // 业务标题
                'business_type' => $task->instance->business_type,                      // 业务类型
                'business_type_text' => $task->instance->getBusinessTypeText(),         // 业务类型文本描述
                'created_at' => $task->created_at->format('Y-m-d H:i:s'),               // 创建时间
                'created_at_human' => $task->created_at->diffForHumans(),               // 人性化时间显示
                'workflow_name' => $task->instance->workflow->name ?? '',               // 工作流名称
                'creator_name' => $task->instance->creator->real_name ?? $task->instance->creator->username, // 创建者姓名
            ];
        });

        // 返回成功响应，包含格式化后的任务数据
        return response()->json([
            'code' => 0,
            'msg' => '获取成功',
            'data' => $formattedTasks
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'code' => 500,
            'msg' => '获取待办任务失败：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}


  /**
 * 获取最新动态 (recentActivities)
 *
 * 功能描述：获取系统中最近的工作流处理动态记录
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 最新动态列表
 *   - id (int): 处理记录ID
 *   - type (string): 动态类型（固定为'workflow_process'）
 *   - title (string): 业务标题
 *   - description (string): 描述信息（节点名称 + 操作文本）
 *   - processor_name (string): 处理人姓名
 *   - processed_at (string): 处理时间（格式：Y-m-d H:i:s）
 *   - processed_at_human (string): 人性化的时间显示（如"2小时前"）
 *   - business_type (string): 业务类型
 *   - business_type_text (string): 业务类型文本描述
 */
public function recentActivities()
{
    try {
        // 获取最近的工作流处理记录，排除待处理状态的记录
        $recentProcesses = WorkflowProcess::with([
            'instance.workflow',    // 预加载工作流实例和工作流信息
            'processor'             // 预加载处理人信息
        ])
        ->where('action', '!=', 'pending')     // 排除待处理的记录
        ->orderBy('processed_at', 'desc')      // 按处理时间倒序排列
        ->limit(10)                            // 限制返回10条记录
        ->get();

        // 格式化动态数据，将处理记录对象转换为数组格式
        $activities = $recentProcesses->map(function ($process) {
            return [
                'id' => $process->id,                           // 处理记录ID
                'type' => 'workflow_process',                   // 动态类型
                'title' => $process->instance->business_title,  // 业务标题
                'description' => $process->node_name . ' - ' . $process->getActionText(), // 描述信息（节点名称 + 操作文本）
                'processor_name' => $process->processor->real_name ?? $process->processor->username, // 处理人姓名
                'processed_at' => $process->processed_at->format('Y-m-d H:i:s'),         // 处理时间
                'processed_at_human' => $process->processed_at->diffForHumans(),         // 人性化时间显示
                'business_type' => $process->instance->business_type,                   // 业务类型
                'business_type_text' => $process->instance->getBusinessTypeText(),      // 业务类型文本描述
            ];
        });

        // 返回成功响应，包含格式化后的动态数据
        return response()->json([
            'code' => 0,
            'msg' => '获取成功',
            'data' => $activities
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'code' => 500,
            'msg' => '获取最新动态失败：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}


   /**
 * 获取合同统计数据
 *
 * 功能描述：统计系统中合同的相关数据，包括总合同数、本月新增合同数、待审批合同数和已完成合同数
 *
 * 传入参数：无
 *
 * 输出参数：
 * - total_contracts (int): 合同总数
 * - month_new_contracts (int): 本月新增合同数
 * - pending_approval_contracts (int): 待审批合同数
 * - completed_contracts (int): 已完成合同数
 */
private function getContractStatistics()
{
    // 获取当前年月，用于统计本月新增合同
    $currentMonth = now()->format('Y-m');

    return [
        // 统计合同总数
        'total_contracts' => Contract::count(),

        // 统计本月新增合同数
        'month_new_contracts' => Contract::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth])->count(),

        // 统计待审批的合同数量（通过工作流实例状态为pending来判断）
        'pending_approval_contracts' => WorkflowInstance::where('business_type', 'contract')
            ->where('status', 'pending')
            ->count(),

        // 统计已完成的合同数量（通过工作流实例状态为completed来判断）
        'completed_contracts' => WorkflowInstance::where('business_type', 'contract')
            ->where('status', 'completed')
            ->count(),
    ];
}


   /**
 * 获取工作流统计数据
 *
 * 功能描述：统计系统中工作流的相关数据，包括实例统计和任务统计
 *
 * 传入参数：无
 *
 * 输出参数：
 * - total_instances (int): 工作流实例总数
 * - pending_instances (int): 待处理工作流实例数
 * - completed_instances (int): 已完成工作流实例数
 * - rejected_instances (int): 已拒绝工作流实例数
 * - total_pending_tasks (int): 总待办任务数
 */
private function getWorkflowStatistics()
{
    return [
        // 统计工作流实例总数
        'total_instances' => WorkflowInstance::count(),

        // 统计待处理的工作流实例数（状态为pending）
        'pending_instances' => WorkflowInstance::where('status', 'pending')->count(),

        // 统计已完成的工作流实例数（状态为completed）
        'completed_instances' => WorkflowInstance::where('status', 'completed')->count(),

        // 统计已拒绝的工作流实例数（状态为rejected）
        'rejected_instances' => WorkflowInstance::where('status', 'rejected')->count(),

        // 统计总的待办任务数（工作流处理记录中状态为pending的任务）
        'total_pending_tasks' => WorkflowProcess::where('action', 'pending')->count(),
    ];
}


   /**
 * 获取系统通知 (notifications)
 *
 * 功能描述：获取系统发送给当前用户的通知消息列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 通知列表
 *   - id (int): 通知ID
 *   - type (string): 通知类型（如system系统通知、feature功能通知等）
 *   - title (string): 通知标题
 *   - content (string): 通知内容
 *   - created_at (string): 创建时间（格式：Y-m-d H:i:s）
 *   - is_read (bool): 是否已读
 *
 * 错误响应：
 * - code (int): 状态码，500表示服务器错误
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function notifications()
{
    try {
        // 这里可以添加系统通知逻辑
        // 目前使用模拟数据，实际应从数据库获取用户通知
        $notifications = [
            [
                'id' => 1,                                    // 通知ID
                'type' => 'system',                           // 通知类型：系统通知
                'title' => '系统维护通知',                       // 通知标题
                'content' => '系统将于今晚22:00进行维护，预计维护时间1小时', // 通知内容
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'), // 创建时间
                'is_read' => false,                           // 是否已读
            ],
            [
                'id' => 2,                                    // 通知ID
                'type' => 'feature',                          // 通知类型：功能通知
                'title' => '新功能上线',                        // 通知标题
                'content' => '工作流管理功能已上线，支持合同审批流程',     // 通知内容
                'created_at' => now()->subDays(1)->format('Y-m-d H:i:s'), // 创建时间
                'is_read' => false,                           // 是否已读
            ],
        ];

        // 返回成功响应，包含通知数据
        return response()->json([
            'code' => 0,
            'msg' => '获取成功',
            'data' => $notifications
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'code' => 500,
            'msg' => '获取通知失败：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}


   /**
 * 获取快速操作数据 (quickActions)
 *
 * 功能描述：获取首页展示的快速操作按钮列表，方便用户快速访问常用功能
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 快速操作列表
 *   - name (string): 操作名称
 *   - icon (string): 图标类名
 *   - route (string): 路由路径
 *   - permission (string|null): 所需权限标识，null表示无需权限验证
 *
 * 错误响应：
 * - code (int): 状态码，500表示服务器错误
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function quickActions()
{
    try {
        // 定义快速操作列表数据
        $actions = [
            [
                'name' => '新建合同',                           // 操作名称
                'icon' => 'el-icon-document-add',              // 图标类名（Element UI图标）
                'route' => '/contracts/create',                // 路由路径
                'permission' => 'contract.create',             // 所需权限标识
            ],
            [
                'name' => '我的待办',                           // 操作名称
                'icon' => 'el-icon-bell',                      // 图标类名（Element UI图标）
                'route' => '/dashboard/tasks',                 // 路由路径
                'permission' => null,                          // 无需权限验证
            ],
            [
                'name' => '合同列表',                           // 操作名称
                'icon' => 'el-icon-document',                  // 图标类名（Element UI图标）
                'route' => '/contracts',                       // 路由路径
                'permission' => 'contract.view',               // 所需权限标识
            ],
            [
                'name' => '工作流配置',                         // 操作名称
                'icon' => 'el-icon-setting',                   // 图标类名（Element UI图标）
                'route' => '/config/system/workflow',          // 路由路径
                'permission' => 'workflow.config',             // 所需权限标识
            ],
        ];

        // 返回成功响应，包含快速操作数据
        return response()->json([
            'code' => 0,
            'msg' => '获取成功',
            'data' => $actions
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'code' => 500,
            'msg' => '获取快速操作失败：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}
}
