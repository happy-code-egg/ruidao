<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use App\Models\Workflow;

class ReviewProgressController extends Controller
{
    /**
     * 获取合同流程列表
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

    private function getCaseName($businessId)
    {
        // 这里应该根据实际的业务表查询案例名称
        // 例如：从cases表或process_items表查询
        return "案例名称_{$businessId}";
    }

    private function getCaseType($businessId)
    {
        // 这里应该根据实际的业务表查询案例类型
        return "专利";
    }

    private function getCustomerName($businessId)
    {
        // 这里应该根据实际的业务表查询客户名称
        return "客户名称_{$businessId}";
    }

    private function getCreatedBy($businessId)
    {
        // 这里应该根据实际的业务表查询创建人
        return "创建人_{$businessId}";
    }

    private function getFileName($businessId)
    {
        // 这里应该根据实际的核稿业务表查询文件名称
        return "文件名称_{$businessId}";
    }

    private function getFileType($businessId)
    {
        // 这里应该根据实际的核稿业务表查询文件类型
        return "专利";
    }

    private function getCustomerNameForReview($businessId)
    {
        // 这里应该根据实际的核稿业务表查询客户名称
        return "客户名称_{$businessId}";
    }

    private function getCreatedByForReview($businessId)
    {
        // 这里应该根据实际的核稿业务表查询创建人
        return "创建人_{$businessId}";
    }

    // 立项流程相关的辅助方法
    private function getCaseNameForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询案例名称
        return "案例名称_{$businessId}";
    }

    private function getCaseTypeForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询案例类型
        return "专利";
    }

    private function getCustomerNameForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询客户名称
        return "客户名称_{$businessId}";
    }

    private function getCreatedByForRegister($businessId)
    {
        // 这里应该根据实际的案例表查询创建人
        return "创建人_{$businessId}";
    }
}