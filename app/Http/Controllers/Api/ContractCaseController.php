<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractCaseController extends Controller
{
    /**
     * 获取合同项目列表
     */
    public function index(Request $request)
    {
        try {
            $query = Cases::with([
                'customer',
                'contract',
                'product',
                'businessPerson',
                'agent',
                'assistant',
                'creator'
            ]);

            // 根据状态过滤，考虑所属权和流程节点控制
            if ($request->filled('case_status') || $request->filled('status')) {
                $status = $request->case_status ?: $request->status;
                $currentUserId = auth()->id();
                
                // 如果没有认证用户，临时跳过所属权检查（调试用）
                if (!$currentUserId) {
                    Log::warning('No authenticated user found for case filtering');
                }
                
                switch ($status) {
                    case 'draft':
                        // 草稿：自己创建的且还没开始审批流程的（兼容没有工作流程的旧数据）
                        $query->where('case_status', Cases::STATUS_DRAFT);
                        if ($currentUserId) {
                            $query->where('created_by', $currentUserId);
                        }
                        break;
                        
                    case 'to-be-pending':
                        // 待处理：只显示当前用户有待办任务的案件
                        if ($currentUserId) {
                            $query->whereHas('workflowInstance', function($wq) use ($currentUserId) {
                                $wq->where('status', 'pending')
                                  ->whereHas('processes', function($p) use ($currentUserId) {
                                      $p->where('assignee_id', $currentUserId)
                                        ->where('action', 'pending');
                                  });
                            });
                        } else {
                            // 如果没有当前用户，返回空结果
                            $query->whereRaw('1 = 0');
                        }
                        break;
                        
                    case 'to-be-filed':
                        // 立项中：我创建的所有正在审批流程中的案件
                        // 包括状态2（待立项）和状态4（处理中）
                        $query->whereIn('case_status', [Cases::STATUS_TO_BE_FILED, Cases::STATUS_PROCESSING]);
                        
                        if ($currentUserId) {
                            $query->where('created_by', $currentUserId)
                                  ->where(function($q) use ($currentUserId) {
                                      // 情况1：有工作流程实例且在进行中
                                      $q->whereHas('workflowInstance', function($wq) {
                                          $wq->where('status', 'pending');
                                      })
                                      // 情况2：没有工作流程实例（旧数据兼容）
                                      ->orWhereDoesntHave('workflowInstance');
                                  });
                        }
                        break;
                        
                    case 'filed':
                        // 已立项：已经走完审批流程且是自己创建的，或者是旧的已完成数据
                        $query->where('case_status', Cases::STATUS_COMPLETED);
                        if ($currentUserId) {
                            $query->where('created_by', $currentUserId);
                        }
                        break;
                        
                    // 保留其他状态以防万一
                    case 'pending':
                        // 待立项：状态为待立项但还没有进入工作流的案件
                        $query->where('case_status', Cases::STATUS_TO_BE_FILED)
                              ->whereDoesntHave('workflowInstance', function($wq) {
                                  $wq->where('status', 'pending');
                              });
                        break;
                    case 'processing':
                        $query->where('case_status', Cases::STATUS_PROCESSING);
                        break;
                    case 'completed':
                        $query->where('case_status', Cases::STATUS_COMPLETED);
                        break;
                }
            }

            // 根据项目类型过滤（主要分类）
            if ($request->filled('case_type') && $request->case_type !== 'all') {
                $caseType = $request->case_type;
                switch ($caseType) {
                    case 'patent':
                        $query->where('case_type', Cases::TYPE_PATENT);
                        break;
                    case 'trademark':
                        $query->where('case_type', Cases::TYPE_TRADEMARK);
                        break;
                    case 'copyright':
                        $query->where('case_type', Cases::TYPE_COPYRIGHT);
                        break;
                    case 'project':
                        $query->where('case_type', Cases::TYPE_TECH_SERVICE);
                        break;
                }
            }

            // 根据业务类型过滤（子分类）
            if ($request->filled('business_type') && $request->business_type !== 'all') {
                $query->where('case_subtype', $request->business_type);
            }

            // 搜索条件
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('case_number')) {
                $query->where('case_code', 'like', '%' . $request->case_number . '%');
            }

            if ($request->filled('contract_code')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('contract_code', 'like', '%' . $request->contract_code . '%');
                });
            }

            // 分页
            $perPage = $request->input('page_size', 10);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $cases = $query->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 格式化数据
            $formattedCases = $cases->map(function ($case) {
                return [
                    'id' => $case->id,
                    'customerName' => $case->customer->customer_name ?? '',
                    'contractCode' => $case->contract->contract_code ?? '',
                    'caseType' => $case->getTypeTextAttribute(), // 项目类型：专利、商标、版权、科服
                    'businessType' => $case->case_subtype ?? '', // 业务类型：发明专利、商标注册等
                    'applyType' => $case->application_type ?? '',
                    'caseNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'technicalDirector' => $case->businessPerson->name ?? '',
                    'amount' => $case->estimated_cost ?? 0,
                    'serviceFee' => $case->service_fee ?? 0,
                    'officialFee' => $case->official_fee ?? 0,
                    'creator' => $case->creator->name ?? '',
                    'processingTime' => $case->updated_at ? $case->updated_at->format('Y-m-d H:i:s') : '',
                    'preSales' => $case->presale_support ? '是' : '否',
                    'caseHandler' => $case->agent->name ?? '',
                    'trademarkType' => $case->trademark_category ?? '',
                    'company' => $case->contract->partyBCompany->company_name ?? '',
                    'agencyStructure' => $case->contract->partyBCompany->company_name ?? '',
                    'remark' => $case->remarks ?? '',
                    'status' => $case->getStatusTextAttribute(),
                    'case_status' => $case->case_status,
                    'case_type' => $case->case_type
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $formattedCases,
                    'total' => $total,
                    'page_size' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目详情
     */
    public function show($id)
    {
        try {
            $case = Cases::with([
                'customer',
                'contract',
                'businessPerson',
                'techLeader',
                'agent',
                'assistant',
                'creator',
                'serviceFees',
                'officialFees',
                'attachments'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $case
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '记录不存在'
            ], 404);
        }
    }

    /**
     * 创建项目
     */
    public function store(Request $request)
    {
        try {
            // $validator = Validator::make($request->all(), [
            //     'contract_id' => 'required|exists:contracts,id',
            //     'case_name' => 'required|string|max:200',
            //     'case_type' => 'required|integer|in:1,2,3,4',
            //     'case_subtype' => 'nullable|string|max:100',
            //     'application_type' => 'nullable|string|max:100',
            //     'case_description' => 'nullable|string',
            //     'estimated_cost' => 'nullable|numeric|min:0',
            //     'service_fee' => 'nullable|numeric|min:0',
            //     'official_fee' => 'nullable|numeric|min:0',
            //     'business_person_id' => 'nullable|exists:users,id',
            //     'agent_id' => 'nullable|exists:users,id',
            //     'assistant_id' => 'nullable|exists:users,id',
            //     'trademark_category' => 'nullable|string|max:100',
            //     'tech_service_name' => 'nullable|string|max:200',
            //     'remarks' => 'nullable|string',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => '验证失败',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            DB::beginTransaction();

            $data = $request->all();
            
            // 生成项目编号
            $data['case_code'] = $this->generateCaseCode($data['case_type']);
            
            // 设置默认状态为草稿
            $data['case_status'] = Cases::STATUS_DRAFT;
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // 从合同获取客户ID
            $contract = Contract::find($data['contract_id']);
            $data['customer_id'] = $contract->customer_id;

            $case = Cases::create($data);

            // 更新合同的项目数量
            $contract->increment('case_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $case->load(['customer', 'contract'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新项目（支持专利立项）
     */
    public function update(Request $request, $id)
    {
        try {
            $case = Cases::findOrFail($id);

            // $validator = Validator::make($request->all(), [
            //     'case_name' => 'required|string|max:200',
            //     'case_type' => 'required|integer|in:1,2,3,4',
            //     'case_subtype' => 'nullable|string|max:100',
            //     'application_type' => 'nullable|string|max:100',
            //     'case_description' => 'nullable|string',
            //     'estimated_cost' => 'nullable|numeric|min:0',
            //     'service_fee' => 'nullable|numeric|min:0',
            //     'official_fee' => 'nullable|numeric|min:0',
            //     'business_person_id' => 'nullable|exists:users,id',
            //     'agent_id' => 'nullable|exists:users,id',
            //     'assistant_id' => 'nullable|exists:users,id',
            //     'trademark_category' => 'nullable|string|max:100',
            //     'tech_service_name' => 'nullable|string|max:200',
            //     'remarks' => 'nullable|string',
            //     'case_status' => 'nullable|integer|in:1,2,3,4,5,6',

            //     // 专利立项特殊字段
            //     'proposal_no' => 'nullable|string|max:100',
            //     'customer_file_number' => 'nullable|string|max:100',
            //     'customer_name' => 'nullable|string|max:200',
            //     'company' => 'nullable|string|max:200',
            //     'business_type' => 'nullable|string|max:100',
            //     'apply_type' => 'nullable|string|max:100',
            //     'presale_support' => 'nullable|string|max:100',
            //     'tech_leader' => 'nullable|string|max:100',
            //     'initial_stage' => 'nullable|string|max:100',
            //     'country_code' => 'nullable|string|max:10',
            //     'annual_fee_stage' => 'nullable|string|max:100',
            //     'case_direction' => 'nullable|string|max:100',
            //     'contract_number' => 'nullable|string|max:100',
            //     'application_no' => 'nullable|string|max:100',
            //     'application_date' => 'nullable|date',
            //     'publication_date' => 'nullable|date',
            //     'prosecution_review' => 'nullable|string|max:10',
            //     'application_method' => 'nullable|string|max:100',
            //     'preliminary_case' => 'nullable|boolean',
            //     'early_publication' => 'nullable|boolean',
            //     'confidential_application' => 'nullable|boolean',
            //     'substantive_examination' => 'nullable|boolean',
            //     'fast_track_case' => 'nullable|boolean',
            //     'priority_examination' => 'nullable|boolean',
            //     'applicant_info' => 'nullable|array',
            //     'process_items' => 'nullable|array',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => '验证失败',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            $data = $request->all();

            // 转换状态字符串为数值
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            $data['updated_by'] = auth()->id();

            // 处理专利立项特殊字段映射
            if ($request->has('proposal_no')) {
                $data['case_code'] = $request->proposal_no;
            }

            if ($request->has('customer_name')) {
                // 这里可以根据客户名称查找客户ID
                $customer = Customer::where('customer_name', $request->customer_name)->first();
                if ($customer) {
                    $data['customer_id'] = $customer->id;
                }
            }

            $case->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $case->load(['customer', 'contract'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除项目
     */
    public function destroy($id)
    {
        try {
            $case = Cases::findOrFail($id);

            DB::beginTransaction();

            // 更新合同的项目数量
            if ($case->contract) {
                $case->contract->decrement('case_count');
            }

            // 删除项目
            $case->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目类型选项
     */
    public function getCaseTypeOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['label' => '专利', 'value' => Cases::TYPE_PATENT],
                ['label' => '商标', 'value' => Cases::TYPE_TRADEMARK],
                ['label' => '版权', 'value' => Cases::TYPE_COPYRIGHT],
                ['label' => '科服', 'value' => Cases::TYPE_TECH_SERVICE],
            ]
        ]);
    }

    /**
     * 获取项目状态选项
     */
    public function getCaseStatusOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['label' => '草稿', 'value' => Cases::STATUS_DRAFT],
                ['label' => '已提交', 'value' => Cases::STATUS_SUBMITTED],
                ['label' => '处理中', 'value' => Cases::STATUS_PROCESSING],
                ['label' => '已授权', 'value' => Cases::STATUS_AUTHORIZED],
                ['label' => '已驳回', 'value' => Cases::STATUS_REJECTED],
                ['label' => '已完成', 'value' => Cases::STATUS_COMPLETED],
            ]
        ]);
    }

    /**
     * 生成项目编号
     */
    private function generateCaseCode($caseType)
    {
        $prefix = '';
        switch ($caseType) {
            case Cases::TYPE_PATENT:
                $prefix = 'ZL';
                break;
            case Cases::TYPE_TRADEMARK:
                $prefix = 'TM';
                break;
            case Cases::TYPE_COPYRIGHT:
                $prefix = 'SR';
                break;
            case Cases::TYPE_TECH_SERVICE:
                $prefix = 'PJ';
                break;
        }

        $yearMonth = date('Ym');
        $latest = Cases::where('case_code', 'like', $prefix . $yearMonth . '%')
                     ->orderBy('case_code', 'desc')
                     ->first();

        if ($latest) {
            $number = intval(substr($latest->case_code, -4)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . $yearMonth . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 转换状态字符串为数值
     */
    private function convertStatusToInt($status)
    {
        $statusMap = [
            'draft' => Cases::STATUS_DRAFT,                    // 1 - 草稿
            'to-be-filed' => Cases::STATUS_TO_BE_FILED,        // 2 - 立项中（提交后）
            'to-be-pending' => Cases::STATUS_TO_BE_FILED,      // 2 - 待处理（同立项中，给审核人员看）
            'filed' => Cases::STATUS_COMPLETED,                // 7 - 已立项
            'completed' => Cases::STATUS_COMPLETED,            // 7 - 已完成
            // 保留其他状态以防万一
            'submitted' => Cases::STATUS_TO_BE_FILED,          // 2 - 已提交（等同立项中）
            'processing' => Cases::STATUS_PROCESSING,          // 4 - 处理中
            'authorized' => Cases::STATUS_AUTHORIZED,          // 5 - 已授权
            'rejected' => Cases::STATUS_REJECTED,              // 6 - 已驳回
        ];

        return $statusMap[$status] ?? (int)$status;
    }

    /**
     * 启动立项审批流程
     */
    public function startWorkflow(Request $request, $id)
    {
        try {
            $case = Cases::findOrFail($id);
            
            // 检查是否已有进行中的工作流程实例
            $existingInstance = $case->workflowInstance;
            if ($existingInstance && $existingInstance->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => '该立项已有进行中的审批流程，无需重复启动'
                ], 400);
            }
            
            // 如果已有工作流实例但状态为已完成或已驳回，则取消旧实例
            if ($existingInstance && in_array($existingInstance->status, ['completed', 'rejected', 'cancelled'])) {
                \Log::info('ContractCaseController: 发现旧的工作流实例，状态为: ' . $existingInstance->status);
                // 不需要删除，只需要创建新的实例即可，系统会自动关联到最新的实例
            }

            // 根据项目类型选择工作流程
            $caseTypeText = $this->getCaseTypeText($case->case_type);
            $workflowCode = $this->getWorkflowCodeByCaseType($case->case_type);
            
            // 先按case_type文本查找，如果找不到再按code查找
            $workflow = Workflow::where('case_type', $caseTypeText)->where('status', 1)->first();
            
            if (!$workflow) {
                $workflow = Workflow::where('code', $workflowCode)->where('status', 1)->first();
            }
            
            if (!$workflow) {
                return response()->json([
                    'success' => false,
                    'message' => "未找到对应的工作流程配置。项目类型：{$case->case_type}，查找的工作流程代码：{$workflowCode}"
                ], 400);
            }

            // 获取选择的处理人信息
            $selectedAssignees = $request->input('assignees', []);
            
            // 启动工作流程（带选人）
            $workflowService = app(\App\Services\WorkflowService::class);
            
            if (!empty($selectedAssignees)) {
                $instance = $workflowService->startWorkflowWithAssignees(
                    'case',                    // $businessType
                    $case->id,                // $businessId  
                    $case->case_name,         // $businessTitle
                    $workflow->id,            // $workflowId
                    auth()->id(),             // $createdBy
                    $selectedAssignees        // $selectedAssignees
                );
            } else {
                $instance = $workflowService->startWorkflow(
                    'case',                    // $businessType
                    $case->id,                // $businessId  
                    $case->case_name,         // $businessTitle
                    $workflow->id,            // $workflowId
                    auth()->id()              // $createdBy
                );
            }

            // 更新项目状态为立项中
            $case->update(['case_status' => Cases::STATUS_TO_BE_FILED]);

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow_instance_id' => $instance->id,
                    'workflow_name' => $workflow->name
                ],
                'message' => '审批流程启动成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '启动流程失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取立项工作流信息
     */
    public function getWorkflow($id)
    {
        try {
            $case = Cases::findOrFail($id);
            
            // 获取工作流实例
            $instance = WorkflowInstance::where('business_type', 'case')
                ->where('business_id', $case->id)
                ->with(['workflow', 'creator'])
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'instance' => null,
                        'processes' => [],
                        'case' => $case
                    ]
                ]);
            }

            // 获取流程节点信息
            $processes = WorkflowProcess::where('instance_id', $instance->id)
                ->with(['assignee', 'processor'])
                ->orderBy('node_index')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'instance' => [
                        'id' => $instance->id,
                        'workflow_name' => $instance->workflow->name,
                        'status' => $instance->status,
                        'current_node_index' => $instance->current_node_index,
                        'created_at' => $instance->created_at,
                        'creator' => $instance->creator ? $instance->creator->real_name : ''
                    ],
                    'processes' => $processes->map(function ($process) {
                        return [
                            'id' => $process->id,
                            'node_index' => $process->node_index,
                            'node_name' => $process->node_name,
                            'action' => $process->action,
                            'comment' => $process->comment,
                            'created_at' => $process->created_at,
                            'processed_at' => $process->processed_at,
                            'assignee' => $process->assignee ? [
                                'id' => $process->assignee->id,
                                'real_name' => $process->assignee->real_name
                            ] : null,
                            'processor' => $process->processor ? [
                                'id' => $process->processor->id,
                                'real_name' => $process->processor->real_name
                            ] : null
                        ];
                    }),
                    'case' => $case
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取工作流信息失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 调试：获取待处理数据的详细信息
     */
    public function debugPendingData(Request $request)
    {
        try {
            $currentUserId = auth()->id();
            
            // 获取所有有工作流实例的案件
            $casesWithWorkflow = Cases::with(['workflowInstance.processes'])
                ->whereHas('workflowInstance')
                ->get();
            
            $debugInfo = [];
            
            foreach ($casesWithWorkflow as $case) {
                $instance = $case->workflowInstance;
                $pendingProcesses = $instance->processes()
                    ->where('assignee_id', $currentUserId)
                    ->where('action', 'pending')
                    ->get();
                
                $debugInfo[] = [
                    'case_id' => $case->id,
                    'case_name' => $case->case_name,
                    'case_status' => $case->case_status,
                    'created_by' => $case->created_by,
                    'current_user_id' => $currentUserId,
                    'workflow_status' => $instance->status,
                    'current_node_index' => $instance->current_node_index,
                    'pending_processes_for_user' => $pendingProcesses->map(function($p) {
                        return [
                            'id' => $p->id,
                            'node_name' => $p->node_name,
                            'assignee_id' => $p->assignee_id,
                            'action' => $p->action
                        ];
                    }),
                    'all_processes' => $instance->processes->map(function($p) {
                        return [
                            'id' => $p->id,
                            'node_name' => $p->node_name,
                            'assignee_id' => $p->assignee_id,
                            'action' => $p->action,
                            'node_index' => $p->node_index
                        ];
                    })
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $debugInfo
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '调试失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 根据项目类型获取工作流程代码
     */
    private function getWorkflowCodeByCaseType($caseType)
    {
        // 先将数字类型转换为文本类型
        $caseTypeText = $this->getCaseTypeText($caseType);
        
        switch ($caseType) {
            case Cases::TYPE_PATENT:
            case Cases::TYPE_TRADEMARK:
            case Cases::TYPE_COPYRIGHT:
                return 'CASE_BUSINESS_FLOW'; // 立案流程(商版专)
            case Cases::TYPE_TECH_SERVICE:
                return 'CASE_TECH_SERVICE_FLOW'; // 立案流程(科服)
            default:
                return 'CASE_BUSINESS_FLOW';
        }
    }

    /**
     * 根据案例类型数字获取对应的文本
     */
    private function getCaseTypeText($caseType)
    {
        $types = [
            Cases::TYPE_PATENT => '专利',
            Cases::TYPE_TRADEMARK => '商标',
            Cases::TYPE_COPYRIGHT => '版权',
            Cases::TYPE_TECH_SERVICE => '科服',
        ];

        return $types[$caseType] ?? '专利';
    }
}
