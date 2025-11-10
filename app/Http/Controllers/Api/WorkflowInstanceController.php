<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 工作流实例控制器
 * 负责工作流实例的启动、查询、处理和管理
 */
class WorkflowInstanceController extends Controller
{
    protected $workflowService;

    /**
     * 功能: 构造函数，注入工作流服务
     * 请求参数: 无
     * 返回参数: 无
     * 接口: 无接口
     */
    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * 功能: 启动工作流，支持自动选择与预分配处理人
     * 请求参数:
     * - business_type(string, 必填): 业务类型，例如 case
     * - business_id(int, 必填): 业务ID
     * - business_title(string, 必填): 业务标题
     * - workflow_id(int, 可选): 工作流ID，存在于 workflows 表
     * - assignees(array<int>, 可选): 预分配处理人用户ID列表
     * 返回参数:
     * - code(int): 0成功，400参数错误，500服务异常
     * - msg(string): 提示信息
     * - data(object): 工作流实例信息
     * 接口: POST /workflow-instances/start
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_type' => 'required|string|max:50',
            'business_id' => 'required|integer',
            'business_title' => 'required|string|max:255',
            'workflow_id' => 'integer|exists:workflows,id', // 可选，如果不提供会自动选择
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'msg' => '参数验证失败',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            // 步骤说明：参数校验 -> 自动选择工作流（案件类型） -> 启动（可带预分配） -> 更新案件状态 -> 返回结果
            $workflowId = $request->workflow_id;
            
            // 如果没有提供workflow_id，对于case类型自动选择工作流
            if (!$workflowId && $request->business_type === 'case') {
                $workflowId = $this->autoSelectWorkflowForCase($request->business_id);
                if (!$workflowId) {
                    return response()->json([
                        'code' => 400,
                        'msg' => '无法自动选择合适的工作流程',
                        'data' => null
                    ], 400);
                }
            }
            
            $assignees = $request->input('assignees', []);
            
            if (!empty($assignees)) {
                $instance = $this->workflowService->startWorkflowWithAssignees(
                    $request->business_type,
                    $request->business_id,
                    $request->business_title,
                    $workflowId,
                    Auth::id(),
                    $assignees
                );
            } else {
                $instance = $this->workflowService->startWorkflow(
                    $request->business_type,
                    $request->business_id,
                    $request->business_title,
                    $workflowId,
                    Auth::id()
                );
            }

            // 如果是案件类型，更新案件状态为立项中
            if ($request->business_type === 'case') {
                $case = \App\Models\Cases::find($request->business_id);
                if ($case) {
                    $case->update(['case_status' => \App\Models\Cases::STATUS_TO_BE_FILED]);
                    \Log::info('WorkflowInstanceController: 案件状态已更新', [
                        'caseId' => $case->id,
                        'newStatus' => \App\Models\Cases::STATUS_TO_BE_FILED
                    ]);
                }
            }

            return response()->json([
                'code' => 0,
                'msg' => '工作流启动成功',
                'data' => $instance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 获取工作流实例详情
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流实例ID
     * 返回参数:
     * - code(int): 0成功，404不存在
     * - msg(string): 提示信息
     * - data(object): 工作流实例及关联数据（workflow, creator, processes.assignee, processes.processor）
     * 接口: GET /workflow-instances/{id}
     */
    public function show($id)
    {
        try {
            $instance = WorkflowInstance::with([
                'workflow',
                'creator',
                'processes.assignee',
                'processes.processor'
            ])->findOrFail($id);

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $instance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 404,
                'msg' => '工作流实例不存在',
                'data' => null
            ], 404);
        }
    }

    /**
     * 功能: 处理工作流节点，支持审批/驳回/退回
     * 请求参数:
     * - processId(int, 必填): 路径参数，节点ID
     * - action(string, 必填): 操作类型，approve|reject|back
     * - comment(string, 可选): 处理意见
     * - back_to_node_index(int, 条件必填): 退回操作时指定退回节点索引
     * 返回参数:
     * - code(int): 0成功，400参数错误，500服务异常
     * - msg(string): 提示信息
     * - data(object): 处理后的节点信息
     * 接口: POST /workflow-instances/process/{processId}
     */
    public function process(Request $request, $processId)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,back',
            'comment' => 'nullable|string|max:1000',
            'back_to_node_index' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'msg' => '参数验证失败',
                'data' => $validator->errors()
            ], 400);
        }

        // 如果是退回操作，验证退回节点索引
        if ($request->action === 'back' && !$request->has('back_to_node_index')) {
            return response()->json([
                'code' => 400,
                'msg' => '退回操作需要指定退回节点',
                'data' => null
            ], 400);
        }

        try {
            // 步骤说明：校验动作类型与退回节点 -> 调用服务处理节点 -> 返回处理结果
            $process = $this->workflowService->processNode(
                $processId,
                $request->action,
                $request->comment,
                Auth::id(),
                $request->back_to_node_index
            );

            return response()->json([
                'code' => 0,
                'msg' => '处理成功',
                'data' => $process
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 获取当前用户的待处理任务列表
     * 请求参数: 无
     * 返回参数:
     * - code(int): 0成功，500服务异常
     * - msg(string): 提示信息
     * - data(array): 待处理任务列表
     * 接口: GET /workflow-instances/my-tasks
     */
    public function myTasks()
    {
        try {
            $tasks = $this->workflowService->getPendingTasks(Auth::id());

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 查询指定业务的工作流状态
     * 请求参数:
     * - business_type(string, 必填): 业务类型
     * - business_id(int, 必填): 业务ID
     * 返回参数:
     * - code(int): 0成功，400参数错误，404不存在，500服务异常
     * - msg(string): 提示信息
     * - data(object|null): 业务对应的工作流实例状态
     * 接口: GET /workflow-instances/business-status
     */
    public function businessStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_type' => 'required|string|max:50',
            'business_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'msg' => '参数验证失败',
                'data' => $validator->errors()
            ], 400);
        }

        try {
            $instance = $this->workflowService->getBusinessWorkflowStatus(
                $request->business_type,
                $request->business_id
            );

            if (!$instance) {
                return response()->json([
                    'code' => 404,
                    'msg' => '工作流实例不存在',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $instance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 取消指定工作流实例
     * 请求参数:
     * - instanceId(int, 必填): 路径参数，工作流实例ID
     * 返回参数:
     * - code(int): 0成功，500服务异常
     * - msg(string): 提示信息
     * - data(object): 取消后的工作流实例
     * 接口: PUT /workflow-instances/{instanceId}/cancel
     */
    public function cancel($instanceId)
    {
        try {
            $instance = $this->workflowService->cancelWorkflow($instanceId, Auth::id());

            return response()->json([
                'code' => 0,
                'msg' => '工作流已取消',
                'data' => $instance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 获取指定工作流的处理历史
     * 请求参数:
     * - instanceId(int, 必填): 路径参数，工作流实例ID
     * 返回参数:
     * - code(int): 0成功，500服务异常
     * - msg(string): 提示信息
     * - data(array): 处理历史列表
     * 接口: GET /workflow-instances/{instanceId}/history
     */
    public function history($instanceId)
    {
        try {
            $processes = WorkflowProcess::with(['assignee', 'processor'])
                ->where('instance_id', $instanceId)
                ->orderBy('node_index')
                ->get();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $processes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 获取当前节点之前已处理的可退回节点列表
     * 请求参数:
     * - instanceId(int, 必填): 路径参数，工作流实例ID
     * 返回参数:
     * - code(int): 0成功，500服务异常
     * - msg(string): 提示信息
     * - data(array): 可退回节点信息（index, name, type, processed_at, processor）
     * 接口: GET /workflow-instances/{instanceId}/backable-nodes
     */
    public function getBackableNodes($instanceId)
    {
        try {
            $instance = WorkflowInstance::with(['workflow', 'processes'])
                ->findOrFail($instanceId);

            $currentIndex = $instance->current_node_index;
            $nodes = $instance->workflow->nodes;

            // 获取可退回的节点（当前节点之前的已处理节点）
            $backableNodes = [];
            for ($i = 0; $i < $currentIndex; $i++) {
                // 计算可退回节点：遍历当前节点之前的已处理节点，汇总基本信息
                $process = $instance->processes->where('node_index', $i)->first();
                if ($process && $process->isProcessed()) {
                    $backableNodes[] = [
                        'index' => $i,
                        'name' => $nodes[$i]['name'] ?? "节点 " . ($i + 1),
                        'type' => $nodes[$i]['type'] ?? '处理',
                        'processed_at' => $process->processed_at,
                        'processor' => $process->processor ? $process->processor->real_name : null
                    ];
                }
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $backableNodes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 功能: 获取可分配的用户列表（按部门与姓名排序）
     * 请求参数: 无
     * 返回参数:
     * - code(int): 0成功，500服务异常
     * - msg(string): 提示信息
     * - data(array): 用户列表（id, real_name, username, position, department）
     * 接口: GET /workflow-instances/assignable-users
     */
    public function getAssignableUsers()
    {
        try {
            $users = \App\Models\User::where('status', 1)
                ->select('id', 'real_name', 'username', 'position', 'department_id')
                ->with(['department:id,department_name'])
                ->orderBy('department_id')
                ->orderBy('real_name')
                ->get();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * 功能: 根据案件类型自动选择合适的工作流
     * 请求参数:
     * - caseId(int, 必填): 案件ID
     * 返回参数:
     * - workflowId(int|null): 找到的工作流ID，未找到返回null
     * 接口: 无接口
     */
    private function autoSelectWorkflowForCase($caseId)
    {
        // 步骤说明：查询案件 -> 按类型选择工作流代码 -> 查询工作流 -> 返回ID或null
        try {
            $case = \App\Models\Cases::find($caseId);
            if (!$case) {
                \Log::error("autoSelectWorkflowForCase: 案例不存在", ['caseId' => $caseId]);
                return null;
            }
            
            // 根据案例类型选择工作流代码
            $workflowCode = 'CASE_BUSINESS_FLOW'; // 商版专使用 CASE_BUSINESS_FLOW
            if ($case->case_type === 4) { // 科服类型
                $workflowCode = 'CASE_TECH_SERVICE_FLOW'; // 科服使用 CASE_TECH_SERVICE_FLOW
            }
            
            \Log::info("autoSelectWorkflowForCase: 查找工作流", [
                'caseId' => $caseId,
                'caseType' => $case->case_type,
                'workflowCode' => $workflowCode
            ]);
            
            $workflow = \App\Models\Workflow::where('code', $workflowCode)->where('status', 1)->first();
            
            if (!$workflow) {
                \Log::error("autoSelectWorkflowForCase: 工作流不存在", [
                    'workflowCode' => $workflowCode,
                    'availableWorkflows' => \App\Models\Workflow::where('status', 1)->pluck('code', 'id')->toArray()
                ]);
                return null;
            }
            
            \Log::info("autoSelectWorkflowForCase: 找到工作流", [
                'workflowId' => $workflow->id,
                'workflowName' => $workflow->name
            ]);
            
            return $workflow->id;
        } catch (\Exception $e) {
            \Log::error("autoSelectWorkflowForCase: 异常", [
                'caseId' => $caseId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
