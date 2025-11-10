<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use App\Models\Workflow;

/**
 * 审核进度查询控制器
 *
 * 功能:
 * - 提供合同、立项、配案、核稿等流程的列表与详情查询接口
 * - 统一返回结构，支持分页与基础筛选
 * - 提供状态文本映射及演示用的占位数据获取方法
 *
 * 路由前缀: `/api`
 * 相关接口:
 * - GET `/api/review-progress/contract-flows` 合同流程列表（name: api.review.progress.contract.flows）
 * - GET `/api/review-progress/contract-flows/{id}` 合同流程详情（name: api.review.progress.contract.flows.detail）
 * - GET `/api/review-progress/register-flows` 立项流程列表
 * - GET `/api/review-progress/register-flows/{id}` 立项流程详情
 * - GET `/api/review-progress/assign-flows` 配案流程列表（name: api.review.progress.assign.flows）
 * - GET `/api/review-progress/review-flows` 核稿流程列表（name: api.review.progress.review.flows）
 *
 * 内部说明:
 * - 依赖 `workflow_instances`、`workflows`、`workflow_processes`、`users` 等表
 * - 部分业务字段（合同名称/类型、客户名称、创建人、文件名/类型等）为演示占位，需按实际业务表结构调整
 */
class ReviewProgressController extends Controller
{
    /**
     * 获取合同流程列表
     *
     * 功能:
     * - 查询业务类型为合同（contract）的工作流实例最新进度，支持基础筛选与分页
     *
     * 接口:
     * - GET `/api/review-progress/contract-flows` （name: api.review.progress.contract.flows）
     *
     * 请求参数:
     * - `page` int 当前页，默认 1
     * - `page_size` int 每页条数，默认 20
     * - `contract_name` string 合同名称（模糊匹配）
     * - `contract_type` string 合同类型（精确匹配）
     * - `customer_name` string 客户名称（模糊匹配）
     * - `created_by` string 创建人（模糊匹配）
     * - `workflow_status` string 流程状态（pending/completed/rejected/cancelled）
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.data` array 列表数据，包含：
     *   - `id` 业务ID
     *   - `workflowInstanceId` 工作流实例ID
     *   - `contract_name` 合同名称（占位字段）
     *   - `contract_type` 合同类型（占位字段）
     *   - `contract_status` 合同状态（由工作流状态映射）
     *   - `customer_name` 客户名称（占位字段）
     *   - `total_amount` 合同总额（占位字段）
     *   - `created_by` 创建人（占位字段）
     *   - `created_at` 创建时间
     *   - `workflow_name` 流程名称
     *   - `workflow_status` 流程状态文本
     *   - `current_node` 当前节点名称
     *   - `current_handler` 当前处理人
     *   - `last_update_time` 最后更新时间
     *   - `stop_days` 停留天数
     * - `data.total` int 总条数
     * - `data.current_page` int 当前页
     * - `data.per_page` int 每页条数
     *
     * 内部说明:
     * - 合同相关业务字段目前使用 `DB::raw` 占位，需按实际合同表结构替换
     */
    public function getContractFlows(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 20);
            $contractName = $request->get('contract_name', '');
            $contractType = $request->get('contract_type', '');
            $customerName = $request->get('customer_name', '');
            $createdBy = $request->get('created_by', '');
            $workflowStatus = $request->get('workflow_status', '');

            // 构建查询
            $query = DB::table('workflow_instances as wi')
                ->leftJoin('workflows as w', 'wi.workflow_id', '=', 'w.id')
                ->leftJoin('workflow_processes as wp', function($join) {
                    $join->on('wi.id', '=', 'wp.instance_id')
                         ->whereRaw('wp.id = (SELECT MAX(id) FROM workflow_processes WHERE instance_id = wi.id)');
                })
                ->leftJoin('users as u', 'wp.assignee_id', '=', 'u.id')
                ->where('wi.business_type', 'contract')
                ->select([
                    'wi.id as workflow_instance_id',
                    'wi.business_id',
                    'wi.status as workflow_status',
                    'wi.created_at',
                    'wi.updated_at',
                    'w.name as workflow_name',
                    'wp.node_name as current_node',
                    'u.real_name as current_handler',
                    'wp.updated_at as last_update_time',
                    // 这里需要根据实际的合同表结构来调整
                    DB::raw("'合同名称' as contract_name"),
                    DB::raw("'合同类型' as contract_type"),
                    DB::raw("'客户名称' as customer_name"),
                    DB::raw("'创建人' as created_by"),
                    DB::raw("'0' as total_amount"),
                    DB::raw("EXTRACT(DAY FROM (NOW() - COALESCE(wp.updated_at, wi.created_at))) as stop_days")
                ]);

            // 应用搜索条件
            if (!empty($contractName)) {
                $query->where('contract_name', 'like', "%{$contractName}%");
            }
            if (!empty($contractType)) {
                $query->where('contract_type', $contractType);
            }
            if (!empty($customerName)) {
                $query->where('customer_name', 'like', "%{$customerName}%");
            }
            if (!empty($createdBy)) {
                $query->where('created_by', 'like', "%{$createdBy}%");
            }
            if (!empty($workflowStatus)) {
                $query->where('wi.status', $workflowStatus);
            }

            // 分页查询
            $total = $query->count();
            $data = $query->orderBy('wi.created_at', 'desc')
                          ->offset(($page - 1) * $pageSize)
                          ->limit($pageSize)
                          ->get();

            // 格式化数据
            $data = $data->map(function($item) {
                return [
                    'id' => $item->business_id,
                    'workflowInstanceId' => $item->workflow_instance_id,
                    'contract_name' => $item->contract_name,
                    'contract_type' => $item->contract_type,
                    'contract_status' => $this->getContractStatus($item->workflow_status),
                    'customer_name' => $item->customer_name,
                    'total_amount' => $item->total_amount,
                    'created_by' => $item->created_by,
                    'created_at' => $item->created_at,
                    'workflow_name' => $item->workflow_name,
                    'workflow_status' => $this->getWorkflowStatusText($item->workflow_status),
                    'current_node' => $item->current_node,
                    'current_handler' => $item->current_handler,
                    'last_update_time' => $item->last_update_time,
                    'stop_days' => $item->stop_days ?? 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取合同流程数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取立项流程列表
     *
     * 功能:
     * - 查询业务类型为立项（case）的工作流实例最新进度，支持基础筛选与分页
     *
     * 接口:
     * - GET `/api/review-progress/register-flows`
     *
     * 请求参数:
     * - `page` int 当前页，默认 1
     * - `pageSize` int 每页条数，默认 20（注意与其他接口不同，使用驼峰 `pageSize`）
     * - `flowName` string 流程名称（模糊匹配）
     * - `caseName` string 案例名称（预留参数，当前未用于筛选）
     * - `caseType` string 案例类型（预留参数，当前未用于筛选）
     * - `customerName` string 客户名称（预留参数，当前未用于筛选）
     * - `flowStatus` string 流程状态（pending/completed/rejected/cancelled）
     * - `handler` string 当前处理人（模糊匹配）
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.data` array 列表数据，包含：
     *   - `id` 工作流实例ID
     *   - `flowName` 流程名称
     *   - `caseName` 案例名称（占位方法获取）
     *   - `caseType` 案例类型（占位方法获取）
     *   - `customerName` 客户名称（占位方法获取）
     *   - `createdBy` 创建人（占位方法获取）
     *   - `createdTime` 创建时间
     *   - `flowStatus` 流程状态文本
     *   - `handler` 当前处理人
     *   - `lastUpdateTime` 最后更新时间
     *   - `stopCount` 停留天数
     *   - `workflowCode` 流程编码（CASE_BUSINESS_FLOW/CASE_TECH_SERVICE_FLOW）
     * - `data.total` int 总条数
     * - `data.current_page` int 当前页
     * - `data.per_page` int 每页条数
     *
     * 内部说明:
     * - 仅对 `flowName`、`flowStatus`、`handler` 进行筛选；其他参数为预留
     */
    public function getRegisterFlows(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 20); // 注意这里用的是pageSize而不是page_size
            $flowName = $request->get('flowName', '');
            $caseName = $request->get('caseName', '');
            $caseType = $request->get('caseType', '');
            $customerName = $request->get('customerName', '');
            $flowStatus = $request->get('flowStatus', '');
            $handler = $request->get('handler', '');

            // 构建查询 - 查询立项相关的工作流实例
            $query = DB::table('workflow_instances as wi')
                ->leftJoin('workflows as w', 'wi.workflow_id', '=', 'w.id')
                ->leftJoin('workflow_processes as wp', function($join) {
                    $join->on('wi.id', '=', 'wp.instance_id')
                         ->whereRaw('wp.id = (SELECT MAX(id) FROM workflow_processes WHERE instance_id = wi.id)');
                })
                ->leftJoin('users as u', 'wp.assignee_id', '=', 'u.id')
                ->where('wi.business_type', 'case')
                ->whereIn('w.code', ['CASE_BUSINESS_FLOW', 'CASE_TECH_SERVICE_FLOW'])
                ->select([
                    'wi.id as workflow_instance_id',
                    'wi.business_id',
                    'wi.status as workflow_status',
                    'wi.created_at',
                    'wi.updated_at',
                    'w.name as workflow_name',
                    'w.code as workflow_code',
                    'wp.node_name as current_node',
                    'u.real_name as current_handler',
                    'wp.updated_at as last_update_time',
                    DB::raw("EXTRACT(DAY FROM (NOW() - COALESCE(wp.updated_at, wi.created_at))) as stop_days")
                ]);

            // 应用搜索条件
            if (!empty($flowName)) {
                $query->where('w.name', 'like', "%{$flowName}%");
            }
            if (!empty($flowStatus)) {
                $query->where('wi.status', $flowStatus);
            }
            if (!empty($handler)) {
                $query->where('u.real_name', 'like', "%{$handler}%");
            }

            // 分页查询
            $total = $query->count();
            $data = $query->orderBy('wi.created_at', 'desc')
                          ->offset(($page - 1) * $pageSize)
                          ->limit($pageSize)
                          ->get();

            // 格式化数据
            $data = $data->map(function($item) {
                return [
                    'id' => $item->workflow_instance_id,
                    'flowName' => $item->workflow_name ?: '立项流程',
                    'caseName' => $this->getCaseNameForRegister($item->business_id),
                    'caseType' => $this->getCaseTypeForRegister($item->business_id),
                    'customerName' => $this->getCustomerNameForRegister($item->business_id),
                    'createdBy' => $this->getCreatedByForRegister($item->business_id),
                    'createdTime' => $item->created_at,
                    'flowStatus' => $this->getWorkflowStatusText($item->workflow_status),
                    'handler' => $item->current_handler,
                    'lastUpdateTime' => $item->last_update_time,
                    'stopCount' => $item->stop_days ?? 0,
                    'workflowCode' => $item->workflow_code
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取立项流程数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取立项流程详情
     *
     * 功能:
     * - 获取指定工作流实例的详情及流程节点处理记录（含处理人）
     *
     * 接口:
     * - GET `/api/review-progress/register-flows/{id}`
     *
     * 请求参数:
     * - `workflowInstanceId` int 路径参数，工作流实例ID
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.workflow_instance` object 工作流实例及关联 `workflow`
     * - `data.processes` array 流程节点列表，包含：
     *   - `id` 节点ID
     *   - `node_name` 节点名称
     *   - `action` 节点处理动作
     *   - `assignee` 处理人信息（id、real_name、username）
     *   - `comment` 处理意见
     *   - `processed_at` 处理时间
     *   - `created_at` 创建时间
     *   - `updated_at` 更新时间
     */
    public function getRegisterFlowDetail($workflowInstanceId)
    {
        try {
            $workflowInstance = WorkflowInstance::with(['workflow', 'processes.assignee'])
                ->find($workflowInstanceId);

            if (!$workflowInstance) {
                return response()->json([
                    'success' => false,
                    'message' => '工作流实例不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow_instance' => $workflowInstance,
                    'processes' => $workflowInstance->processes->map(function($process) {
                        return [
                            'id' => $process->id,
                            'node_name' => $process->node_name,
                            'action' => $process->action,
                            'assignee' => $process->assignee ? [
                                'id' => $process->assignee->id,
                                'real_name' => $process->assignee->real_name,
                                'username' => $process->assignee->username
                            ] : null,
                            'comment' => $process->comment,
                            'processed_at' => $process->processed_at,
                            'created_at' => $process->created_at,
                            'updated_at' => $process->updated_at
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取立项流程详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取配案流程列表
     *
     * 功能:
     * - 查询业务类型为配案（assignment）的工作流实例最新进度，支持基础筛选与分页
     *
     * 接口:
     * - GET `/api/review-progress/assign-flows` （name: api.review.progress.assign.flows）
     *
     * 请求参数:
     * - `page` int 当前页，默认 1
     * - `page_size` int 每页条数，默认 20
     * - `flowName` string 流程名称（模糊匹配）
     * - `caseName` string 案例名称（预留参数，当前未用于筛选）
     * - `caseType` string 案例类型（预留参数，当前未用于筛选）
     * - `customerName` string 客户名称（预留参数，当前未用于筛选）
     * - `flowStatus` string 流程状态（pending/completed/rejected/cancelled）
     * - `handler` string 当前处理人（模糊匹配）
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.data` array 列表数据，包含：
     *   - `id` 工作流实例ID（注意：当前代码使用 instance_id，占位）
     *   - `flowName` 流程名称
     *   - `caseName` 案例名称（占位方法获取）
     *   - `caseType` 案例类型（占位方法获取）
     *   - `customerName` 客户名称（占位方法获取）
     *   - `createdBy` 创建人（占位方法获取）
     *   - `createdTime` 创建时间
     *   - `flowStatus` 流程状态文本
     *   - `handler` 当前处理人
     *   - `lastUpdateTime` 最后更新时间
     *   - `stopCount` 停留天数
     * - `data.total` int 总条数
     * - `data.current_page` int 当前页
     * - `data.per_page` int 每页条数
     */
    public function getAssignFlows(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 20);
            $flowName = $request->get('flowName', '');
            $caseName = $request->get('caseName', '');
            $caseType = $request->get('caseType', '');
            $customerName = $request->get('customerName', '');
            $flowStatus = $request->get('flowStatus', '');
            $handler = $request->get('handler', '');

            // 构建查询 - 查询配案相关的工作流实例
            $query = DB::table('workflow_instances as wi')
                ->leftJoin('workflows as w', 'wi.workflow_id', '=', 'w.id')
                ->leftJoin('workflow_processes as wp', function($join) {
                    $join->on('wi.id', '=', 'wp.instance_id')
                         ->whereRaw('wp.id = (SELECT MAX(id) FROM workflow_processes WHERE instance_id = wi.id)');
                })
                ->leftJoin('users as u', 'wp.assignee_id', '=', 'u.id')
                ->where('wi.business_type', 'assignment')
                ->where('w.code', 'ASSIGN_CASE_FLOW')
                ->select([
                    'wi.id as workflow_instance_id',
                    'wi.business_id',
                    'wi.status as workflow_status',
                    'wi.created_at',
                    'wi.updated_at',
                    'w.name as workflow_name',
                    'wp.node_name as current_node',
                    'u.real_name as current_handler',
                    'wp.updated_at as last_update_time',
                    DB::raw("EXTRACT(DAY FROM (NOW() - COALESCE(wp.updated_at, wi.created_at))) as stop_days")
                ]);

            // 应用搜索条件
            if (!empty($flowName)) {
                $query->where('w.name', 'like', "%{$flowName}%");
            }
            if (!empty($flowStatus)) {
                $query->where('wi.status', $flowStatus);
            }
            if (!empty($handler)) {
                $query->where('u.real_name', 'like', "%{$handler}%");
            }

            // 分页查询
            $total = $query->count();
            $data = $query->orderBy('wi.created_at', 'desc')
                          ->offset(($page - 1) * $pageSize)
                          ->limit($pageSize)
                          ->get();

            // 格式化数据
            $data = $data->map(function($item) {
                return [
                    'id' => $item->instance_id,
                    'flowName' => $item->workflow_name ?: '配案流程',
                    'caseName' => $this->getCaseName($item->business_id),
                    'caseType' => $this->getCaseType($item->business_id),
                    'customerName' => $this->getCustomerName($item->business_id),
                    'createdBy' => $this->getCreatedBy($item->business_id),
                    'createdTime' => $item->created_at,
                    'flowStatus' => $this->getWorkflowStatusText($item->workflow_status),
                    'handler' => $item->current_handler,
                    'lastUpdateTime' => $item->last_update_time,
                    'stopCount' => $item->stop_days ?? 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取配案流程数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取核稿流程列表
     *
     * 功能:
     * - 查询业务类型为核稿（review）的工作流实例最新进度，支持基础筛选与分页
     *
     * 接口:
     * - GET `/api/review-progress/review-flows` （name: api.review.progress.review.flows）
     *
     * 请求参数:
     * - `page` int 当前页，默认 1
     * - `page_size` int 每页条数，默认 20
     * - `flowName` string 流程名称（模糊匹配）
     * - `fileName` string 文件名称（预留参数，当前未用于筛选）
     * - `fileType` string 文件类型（预留参数，当前未用于筛选）
     * - `customerName` string 客户名称（预留参数，当前未用于筛选）
     * - `flowStatus` string 流程状态（pending/completed/rejected/cancelled）
     * - `handler` string 当前处理人（模糊匹配）
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.data` array 列表数据，包含：
     *   - `id` 工作流实例ID（注意：当前代码使用 instance_id，占位）
     *   - `flowName` 流程名称
     *   - `fileName` 文件名称（占位方法获取）
     *   - `fileType` 文件类型（占位方法获取）
     *   - `customerName` 客户名称（占位方法获取）
     *   - `createdBy` 创建人（占位方法获取）
     *   - `createdTime` 创建时间
     *   - `flowStatus` 流程状态文本
     *   - `handler` 当前处理人
     *   - `lastUpdateTime` 最后更新时间
     *   - `stopCount` 停留天数
     * - `data.total` int 总条数
     * - `data.current_page` int 当前页
     * - `data.per_page` int 每页条数
     */
    public function getReviewFlows(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 20);
            $flowName = $request->get('flowName', '');
            $fileName = $request->get('fileName', '');
            $fileType = $request->get('fileType', '');
            $customerName = $request->get('customerName', '');
            $flowStatus = $request->get('flowStatus', '');
            $handler = $request->get('handler', '');

            // 构建查询 - 查询核稿相关的工作流实例
            $query = DB::table('workflow_instances as wi')
                ->leftJoin('workflows as w', 'wi.workflow_id', '=', 'w.id')
                ->leftJoin('workflow_processes as wp', function($join) {
                    $join->on('wi.id', '=', 'wp.instance_id')
                         ->whereRaw('wp.id = (SELECT MAX(id) FROM workflow_processes WHERE instance_id = wi.id)');
                })
                ->leftJoin('users as u', 'wp.assignee_id', '=', 'u.id')
                ->where('wi.business_type', 'review')
                ->where('w.code', 'PROOF_FLOW')
                ->select([
                    'wi.id as workflow_instance_id',
                    'wi.business_id',
                    'wi.status as workflow_status',
                    'wi.created_at',
                    'wi.updated_at',
                    'w.name as workflow_name',
                    'wp.node_name as current_node',
                    'u.real_name as current_handler',
                    'wp.updated_at as last_update_time',
                    DB::raw("EXTRACT(DAY FROM (NOW() - COALESCE(wp.updated_at, wi.created_at))) as stop_days")
                ]);

            // 应用搜索条件
            if (!empty($flowName)) {
                $query->where('w.name', 'like', "%{$flowName}%");
            }
            if (!empty($flowStatus)) {
                $query->where('wi.status', $flowStatus);
            }
            if (!empty($handler)) {
                $query->where('u.real_name', 'like', "%{$handler}%");
            }

            // 分页查询
            $total = $query->count();
            $data = $query->orderBy('wi.created_at', 'desc')
                          ->offset(($page - 1) * $pageSize)
                          ->limit($pageSize)
                          ->get();

            // 格式化数据
            $data = $data->map(function($item) {
                return [
                    'id' => $item->instance_id,
                    'flowName' => $item->workflow_name ?: '核稿流程',
                    'fileName' => $this->getFileName($item->business_id),
                    'fileType' => $this->getFileType($item->business_id),
                    'customerName' => $this->getCustomerNameForReview($item->business_id),
                    'createdBy' => $this->getCreatedByForReview($item->business_id),
                    'createdTime' => $item->created_at,
                    'flowStatus' => $this->getWorkflowStatusText($item->workflow_status),
                    'handler' => $item->current_handler,
                    'lastUpdateTime' => $item->last_update_time,
                    'stopCount' => $item->stop_days ?? 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取核稿流程数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同流程详情
     *
     * 功能:
     * - 获取指定合同工作流实例的详情及流程节点处理记录（含处理人）
     *
     * 接口:
     * - GET `/api/review-progress/contract-flows/{id}` （name: api.review.progress.contract.flows.detail）
     *
     * 请求参数:
     * - `workflowInstanceId` int 路径参数，工作流实例ID
     *
     * 返回参数:
     * - `success` bool 是否成功
     * - `data.workflow_instance` object 工作流实例及关联 `workflow`
     * - `data.processes` array 流程节点列表，包含：
     *   - `id` 节点ID
     *   - `node_name` 节点名称
     *   - `status` 节点状态
     *   - `assignee` 处理人信息（id、real_name、username）
     *   - `comment` 处理意见
     *   - `processed_at` 处理时间
     *   - `created_at` 创建时间
     *   - `updated_at` 更新时间
     */
    public function getContractFlowDetail($workflowInstanceId)
    {
        try {
            $workflowInstance = WorkflowInstance::with(['workflow', 'processes.assignee'])
                ->find($workflowInstanceId);

            if (!$workflowInstance) {
                return response()->json([
                    'success' => false,
                    'message' => '工作流实例不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow_instance' => $workflowInstance,
                    'processes' => $workflowInstance->processes->map(function($process) {
                        return [
                            'id' => $process->id,
                            'node_name' => $process->node_name,
                            'status' => $process->status,
                            'assignee' => $process->assignee ? [
                                'id' => $process->assignee->id,
                                'real_name' => $process->assignee->real_name,
                                'username' => $process->assignee->username
                            ] : null,
                            'comment' => $process->comment,
                            'processed_at' => $process->processed_at,
                            'created_at' => $process->created_at,
                            'updated_at' => $process->updated_at
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取流程详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    // 辅助方法
    /**
     * 将工作流状态映射为合同状态文本
     *
     * 功能:
     * - 根据工作流状态返回合同状态中文说明
     *
     * 请求参数:
     * - `workflowStatus` string 工作流状态（pending/completed/rejected/cancelled）
     *
     * 返回参数:
     * - string 合同状态文本
     */
    private function getContractStatus($workflowStatus)
    {
        $statusMap = [
            'pending' => '审核中',
            'completed' => '已完成',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];
        return $statusMap[$workflowStatus] ?? '未知';
    }

    /**
     * 将工作流状态映射为通用状态文本
     *
     * 功能:
     * - 根据工作流状态返回中文说明；默认返回“未发起”
     *
     * 请求参数:
     * - `status` string 工作流状态
     *
     * 返回参数:
     * - string 状态文本
     */
    private function getWorkflowStatusText($status)
    {
        $statusMap = [
            'pending' => '进行中',
            'completed' => '已完成',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];
        return $statusMap[$status] ?? '未发起';
    }

    /**
     * 获取配案/合同相关案例名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 案例名称占位文本
     *
     * 内部说明: 需替换为实际业务表查询
     */
    private function getCaseName($businessId)
    {
        // 这里应该根据实际的业务表查询案例名称
        // 例如：从cases表或process_items表查询
        return "案例名称_{$businessId}";
    }

    /**
     * 获取配案/合同相关案例类型（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 案例类型占位文本
     *
     * 内部说明: 需替换为实际业务表查询
     */
    private function getCaseType($businessId)
    {
        // 这里应该根据实际的业务表查询案例类型
        return "专利";
    }

    /**
     * 获取配案/合同相关客户名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 客户名称占位文本
     *
     * 内部说明: 需替换为实际业务表查询
     */
    private function getCustomerName($businessId)
    {
        // 这里应该根据实际的业务表查询客户名称
        return "客户名称_{$businessId}";
    }

    /**
     * 获取配案/合同相关创建人（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 创建人占位文本
     *
     * 内部说明: 需替换为实际业务表查询
     */
    private function getCreatedBy($businessId)
    {
        // 这里应该根据实际的业务表查询创建人
        return "创建人_{$businessId}";
    }

    /**
     * 获取核稿相关文件名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 文件名称占位文本
     *
     * 内部说明: 需替换为实际核稿业务表查询
     */
    private function getFileName($businessId)
    {
        // 这里应该根据实际的核稿业务表查询文件名称
        return "文件名称_{$businessId}";
    }

    /**
     * 获取核稿相关文件类型（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 文件类型占位文本
     *
     * 内部说明: 需替换为实际核稿业务表查询
     */
    private function getFileType($businessId)
    {
        // 这里应该根据实际的核稿业务表查询文件类型
        return "专利";
    }

    /**
     * 获取核稿相关客户名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 客户名称占位文本
     *
     * 内部说明: 需替换为实际核稿业务表查询
     */
    private function getCustomerNameForReview($businessId)
    {
        // 这里应该根据实际的核稿业务表查询客户名称
        return "客户名称_{$businessId}";
    }

    /**
     * 获取核稿相关创建人（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 创建人占位文本
     *
     * 内部说明: 需替换为实际核稿业务表查询
     */
    private function getCreatedByForReview($businessId)
    {
        // 这里应该根据实际的核稿业务表查询创建人
        return "创建人_{$businessId}";
    }

    // 立项流程相关的辅助方法
    /**
     * 获取立项相关案例名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 案例名称占位文本
     *
     * 内部说明: 需替换为实际案例表查询
     */
    private function getCaseNameForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询案例名称
        return "案例名称_{$businessId}";
    }

    /**
     * 获取立项相关案例类型（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 案例类型占位文本
     *
     * 内部说明: 需替换为实际案例表查询
     */
    private function getCaseTypeForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询案例类型
        return "专利";
    }

    /**
     * 获取立项相关客户名称（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 客户名称占位文本
     *
     * 内部说明: 需替换为实际案例表查询
     */
    private function getCustomerNameForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询客户名称
        return "客户名称_{$businessId}";
    }

    /**
     * 获取立项相关创建人（占位）
     *
     * 请求参数:
     * - `businessId` int 业务ID
     *
     * 返回参数:
     * - string 创建人占位文本
     *
     * 内部说明: 需替换为实际案例表查询
     */
    private function getCreatedByForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询创建人
        return "创建人_{$businessId}";
    }
}