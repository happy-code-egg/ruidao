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
     * 
     * 功能说明：
     * - 支持多种状态过滤（草稿、待处理、立项中、已立项等）
     * - 支持项目类型过滤（专利、商标、版权、科服）
     * - 支持业务类型过滤（发明专利、商标注册等子分类）
     * - 支持多种搜索条件（客户名称、案件编号、合同编号）
     * - 支持分页查询
     * - 考虑用户权限和工作流状态
     * 
     * @param Request $request 请求参数
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // 构建基础查询，预加载相关模型以避免N+1查询问题
            $query = Cases::with([
                'customer',        // 客户信息
                'contract',        // 合同信息
                'product',         // 产品信息
                'businessPerson',  // 业务负责人
                'agent',          // 代理人
                'assistant',      // 助理
                'creator'         // 创建者
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
                        // 草稿状态：自己创建的且还没开始审批流程的案件
                        // 兼容没有工作流程的旧数据
                        $query->where('case_status', Cases::STATUS_DRAFT);
                        if ($currentUserId) {
                            $query->where('created_by', $currentUserId);
                        }
                        break;
                        
                    case 'to-be-pending':
                        // 待处理状态：只显示当前用户有待办任务的案件
                        // 需要检查工作流实例和流程节点
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
                        // 立项中状态：我创建的所有正在审批流程中的案件
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
                        // 已立项状态：已经走完审批流程且是自己创建的案件
                        // 或者是旧的已完成数据
                        $query->where('case_status', Cases::STATUS_COMPLETED);
                        if ($currentUserId) {
                            $query->where('created_by', $currentUserId);
                        }
                        break;
                        
                    // 保留其他状态以防万一
                    case 'pending':
                        // 待立项状态：状态为待立项但还没有进入工作流的案件
                        $query->where('case_status', Cases::STATUS_TO_BE_FILED)
                              ->whereDoesntHave('workflowInstance', function($wq) {
                                  $wq->where('status', 'pending');
                              });
                        break;
                    case 'processing':
                        // 处理中状态
                        $query->where('case_status', Cases::STATUS_PROCESSING);
                        break;
                    case 'completed':
                        // 已完成状态
                        $query->where('case_status', Cases::STATUS_COMPLETED);
                        break;
                }
            }

            // 根据项目类型过滤（主要分类）
            if ($request->filled('case_type') && $request->case_type !== 'all') {
                $caseType = $request->case_type;
                switch ($caseType) {
                    case 'patent':
                        // 专利类型
                        $query->where('case_type', Cases::TYPE_PATENT);
                        break;
                    case 'trademark':
                        // 商标类型
                        $query->where('case_type', Cases::TYPE_TRADEMARK);
                        break;
                    case 'copyright':
                        // 版权类型
                        $query->where('case_type', Cases::TYPE_COPYRIGHT);
                        break;
                    case 'project':
                        // 科技服务类型
                        $query->where('case_type', Cases::TYPE_TECH_SERVICE);
                        break;
                }
            }

            // 根据业务类型过滤（子分类）
            // 如发明专利、商标注册等具体业务类型
            if ($request->filled('business_type') && $request->business_type !== 'all') {
                $query->where('case_subtype', $request->business_type);
            }

            // 搜索条件 - 客户名称模糊搜索
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // 搜索条件 - 案件编号模糊搜索
            if ($request->filled('case_number')) {
                $query->where('case_code', 'like', '%' . $request->case_number . '%');
            }

            // 搜索条件 - 合同编号模糊搜索
            if ($request->filled('contract_code')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('contract_code', 'like', '%' . $request->contract_code . '%');
                });
            }

            // 分页参数处理
            $perPage = $request->input('page_size', 10);  // 每页数量，默认10条
            $page = $request->input('page', 1);           // 当前页码，默认第1页
            
            // 获取总数（用于分页计算）
            $total = $query->count();
            
            // 执行分页查询，按创建时间倒序排列
            $cases = $query->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 格式化数据，转换为前端需要的格式
            $formattedCases = $cases->map(function ($case) {
                return [
                    'id' => $case->id,
                    'customerName' => $case->customer->customer_name ?? '',           // 客户名称
                    'contractCode' => $case->contract->contract_code ?? '',          // 合同编号
                    'caseType' => $case->getTypeTextAttribute(),                     // 项目类型：专利、商标、版权、科服
                    'businessType' => $case->case_subtype ?? '',                     // 业务类型：发明专利、商标注册等
                    'applyType' => $case->application_type ?? '',                    // 申请类型
                    'caseNumber' => $case->case_code ?? '',                          // 案件编号
                    'caseName' => $case->case_name ?? '',                            // 案件名称
                    'technicalDirector' => $case->businessPerson->name ?? '',       // 技术负责人
                    'amount' => $case->estimated_cost ?? 0,                          // 预估费用
                    'serviceFee' => $case->service_fee ?? 0,                         // 服务费
                    'officialFee' => $case->official_fee ?? 0,                       // 官费
                    'creator' => $case->creator->name ?? '',                         // 创建者
                    'processingTime' => $case->updated_at ? $case->updated_at->format('Y-m-d H:i:s') : '', // 处理时间
                    'preSales' => $case->presale_support ? '是' : '否',              // 售前支持
                    'caseHandler' => $case->agent->name ?? '',                       // 案件处理人
                    'trademarkType' => $case->trademark_category ?? '',              // 商标类型
                    'company' => $case->contract->partyBCompany->company_name ?? '', // 公司名称
                    'agencyStructure' => $case->contract->partyBCompany->company_name ?? '', // 代理机构
                    'remark' => $case->remarks ?? '',                               // 备注
                    'status' => $case->getStatusTextAttribute(),                     // 状态文本
                    'case_status' => $case->case_status,                             // 状态码
                    'case_type' => $case->case_type                                  // 类型码
                ];
            });

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $formattedCases,                    // 数据列表
                    'total' => $total,                            // 总记录数
                    'page_size' => $perPage,                      // 每页数量
                    'current_page' => $page,                      // 当前页码
                    'last_page' => ceil($total / $perPage),       // 最后一页页码
                ]
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误信息
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目详情
     * 
     * 功能说明：
     * - 根据项目ID获取完整的项目详细信息
     * - 预加载所有相关联的模型数据，避免N+1查询问题
     * - 包含客户、合同、人员、费用、附件等完整信息
     * - 支持异常处理，记录不存在时返回404状态码
     * 
     * @param int|string $id 项目ID
     * @return \Illuminate\Http\JsonResponse 包含项目详情的JSON响应
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当项目不存在时
     */
    public function show($id)
    {
        try {
            // 查询项目详情，预加载所有相关联的模型数据
            $case = Cases::with([
                'customer',      // 客户信息
                'contract',      // 合同信息
                'businessPerson', // 业务负责人
                'techLeader',    // 技术负责人
                'agent',         // 代理人
                'assistant',     // 助理
                'creator',       // 创建者
                'serviceFees',   // 服务费用记录
                'officialFees',  // 官方费用记录
                'attachments'    // 附件信息
            ])->findOrFail($id); // 如果找不到记录会抛出ModelNotFoundException

            // 返回成功响应，包含完整的项目数据
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $case  // 包含所有预加载关联数据的项目对象
            ]);

        } catch (\Exception $e) {
            // 异常处理：记录不存在或其他数据库错误
            // 统一返回404状态码和友好的错误信息
            return response()->json([
                'success' => false,
                'message' => '记录不存在'
            ], 404);
        }
    }

    /**
     * 创建项目
     * 
     * 功能说明：
     * - 创建新的合同项目记录
     * - 自动生成项目编号
     * - 设置默认状态为草稿
     * - 从合同中获取客户信息
     * - 更新合同的项目计数
     * - 使用数据库事务确保数据一致性
     * 
     * @param Request $request 包含项目创建数据的请求对象
     * @return \Illuminate\Http\JsonResponse 创建结果的JSON响应
     */
    public function store(Request $request)
    {
        try {
            // 数据验证规则（已注释，可根据需要启用）
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

            // 验证失败处理（已注释，可根据需要启用）
            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => '验证失败',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // 开启数据库事务，确保数据操作的原子性
            DB::beginTransaction();

            // 获取请求中的所有数据
            $data = $request->all();
            
            // 生成项目编号（根据项目类型自动生成唯一编号）
            $data['case_code'] = $this->generateCaseCode($data['case_type']);
            
            // 设置默认状态为草稿（新创建的项目默认为草稿状态）
            $data['case_status'] = Cases::STATUS_DRAFT;
            
            // 设置创建人和更新人为当前登录用户
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // 从合同获取客户ID（项目必须关联到合同的客户）
            $contract = Contract::find($data['contract_id']);
            $data['customer_id'] = $contract->customer_id;

            // 创建项目记录
            $case = Cases::create($data);

            // 更新合同的项目数量计数器
            $contract->increment('case_count');

            // 提交事务
            DB::commit();

            // 返回成功响应，包含创建的项目数据及其关联的客户和合同信息
            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $case->load(['customer', 'contract']) // 预加载关联数据
            ]);

        } catch (\Exception $e) {
            // 发生异常时回滚事务
            DB::rollBack();
            
            // 返回错误响应，包含具体的错误信息
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新项目（支持专利立项）
     * 
     * 更新现有案件的信息，支持通用案件字段和专利立项特殊字段的更新
     * 
     * @param Request $request 包含更新数据的HTTP请求对象
     * @param int $id 要更新的案件ID
     * @return \Illuminate\Http\JsonResponse 返回更新结果的JSON响应
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当案件不存在时抛出异常
     */
    public function update(Request $request, $id)
    {
        try {
            // 根据ID查找案件，如果不存在则抛出异常
            $case = Cases::findOrFail($id);

            // 数据验证规则（已注释掉，保留以备后续启用）
            // 包含通用字段验证和专利立项特殊字段验证
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

            // 验证失败处理（已注释掉）
            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => '验证失败',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // 获取所有请求数据
            $data = $request->all();

            // 状态字段处理：将字符串状态转换为整数
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            // 设置更新者ID为当前认证用户
            $data['updated_by'] = auth()->id();

            // 专利立项特殊字段映射处理
            // 将提案号映射为案件编码
            if ($request->has('proposal_no')) {
                $data['case_code'] = $request->proposal_no;
            }

            // 根据客户名称查找并设置客户ID
            if ($request->has('customer_name')) {
                // 通过客户名称查找客户记录
                $customer = Customer::where('customer_name', $request->customer_name)->first();
                if ($customer) {
                    $data['customer_id'] = $customer->id;
                }
            }

            // 执行案件数据更新
            $case->update($data);

            // 返回成功响应，包含更新后的案件数据及关联的客户和合同信息
            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $case->load(['customer', 'contract']) // 预加载关联数据
            ]);

        } catch (\Exception $e) {
            // 异常处理：捕获所有异常并返回错误响应
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除项目
     * 
     * 删除指定的案件项目，同时更新关联合同的项目计数
     * 使用数据库事务确保数据一致性
     * 
     * @param int $id 要删除的案件ID
     * @return \Illuminate\Http\JsonResponse 返回删除结果的JSON响应
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当案件不存在时抛出异常
     */
    public function destroy($id)
    {
        try {
            // 根据ID查找案件，如果不存在则抛出异常
            $case = Cases::findOrFail($id);

            // 开启数据库事务，确保删除操作的原子性
            DB::beginTransaction();

            // 业务逻辑：更新关联合同的项目数量
            // 如果案件关联了合同，则将合同的项目计数减1
            if ($case->contract) {
                $case->contract->decrement('case_count');
            }

            // 执行案件删除操作
            $case->delete();

            // 提交事务，确认所有操作成功
            DB::commit();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理：回滚事务，撤销所有已执行的数据库操作
            DB::rollBack();
            
            // 返回错误响应，包含具体的错误信息
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目类型选项
     * 
     * 返回系统支持的所有项目类型选项，用于前端下拉选择框或其他选择组件
     * 包含专利、商标、版权、科服四种主要业务类型
     * 
     * @return \Illuminate\Http\JsonResponse 返回包含项目类型选项的JSON响应
     * 
     * 响应数据结构：
     * {
     *   "success": true,
     *   "data": [
     *     {"label": "专利", "value": 1},
     *     {"label": "商标", "value": 2},
     *     {"label": "版权", "value": 3},
     *     {"label": "科服", "value": 4}
     *   ]
     * }
     */
    public function getCaseTypeOptions()
    {
        // 返回标准化的项目类型选项数据
        return response()->json([
            'success' => true,
            'data' => [
                ['label' => '专利', 'value' => Cases::TYPE_PATENT],        // 专利类型
                ['label' => '商标', 'value' => Cases::TYPE_TRADEMARK],     // 商标类型
                ['label' => '版权', 'value' => Cases::TYPE_COPYRIGHT],     // 版权类型
                ['label' => '科服', 'value' => Cases::TYPE_TECH_SERVICE],  // 科技服务类型
            ]
        ]);
    }

    /**
     * 获取项目状态选项
     * 
     * 返回系统支持的所有项目状态选项，用于前端下拉选择框或其他选择组件
     * 包含草稿、已提交、处理中、已授权、已驳回、已完成等状态
     * 
     * @return \Illuminate\Http\JsonResponse 返回包含项目状态选项的JSON响应
     * 
     * 响应数据结构：
     * {
     *   "success": true,
     *   "data": [
     *     {"label": "草稿", "value": 1},
     *     {"label": "已提交", "value": 2},
     *     ...
     *   ]
     * }
     */
    public function getCaseStatusOptions()
    {
        // 返回标准化的项目状态选项数据
        return response()->json([
            'success' => true,
            'data' => [
                ['label' => '草稿', 'value' => Cases::STATUS_DRAFT],           // 草稿状态
                ['label' => '已提交', 'value' => Cases::STATUS_SUBMITTED],     // 已提交状态
                ['label' => '处理中', 'value' => Cases::STATUS_PROCESSING],    // 处理中状态
                ['label' => '已授权', 'value' => Cases::STATUS_AUTHORIZED],    // 已授权状态
                ['label' => '已驳回', 'value' => Cases::STATUS_REJECTED],      // 已驳回状态
                ['label' => '已完成', 'value' => Cases::STATUS_COMPLETED],     // 已完成状态
            ]
        ]);
    }

    /**
     * 生成项目编号
     * 
     * 根据项目类型自动生成唯一的项目编号
     * 编号格式：前缀 + 年月 + 4位序号（如：ZL202401001）
     * 
     * @param int $caseType 项目类型（1=专利，2=商标，3=版权，4=科服）
     * @return string 生成的项目编号
     * 
     * 前缀规则：
     * - 专利：ZL
     * - 商标：TM  
     * - 版权：SR
     * - 科服：PJ
     */
    private function generateCaseCode($caseType)
    {
        // 根据项目类型确定编号前缀
        $prefix = '';
        switch ($caseType) {
            case Cases::TYPE_PATENT:
                $prefix = 'ZL';  // 专利前缀
                break;
            case Cases::TYPE_TRADEMARK:
                $prefix = 'TM';  // 商标前缀
                break;
            case Cases::TYPE_COPYRIGHT:
                $prefix = 'SR';  // 版权前缀
                break;
            case Cases::TYPE_TECH_SERVICE:
                $prefix = 'PJ';  // 科服前缀
                break;
        }

        // 获取当前年月（YYYYMM格式）
        $yearMonth = date('Ym');
        
        // 查找当前年月下该类型的最新编号
        $latest = Cases::where('case_code', 'like', $prefix . $yearMonth . '%')
                     ->orderBy('case_code', 'desc')
                     ->first();

        // 计算下一个序号
        if ($latest) {
            // 从最新编号中提取序号并加1
            $number = intval(substr($latest->case_code, -4)) + 1;
        } else {
            // 如果没有找到，从1开始
            $number = 1;
        }

        // 返回完整的项目编号：前缀 + 年月 + 4位序号
        return $prefix . $yearMonth . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 转换状态字符串为数值
     * 
     * 将前端传递的状态字符串转换为数据库中对应的数值
     * 支持多种状态字符串格式的映射
     * 
     * @param string|int $status 状态字符串或数值
     * @return int 对应的状态数值
     */
    private function convertStatusToInt($status)
    {
        // 状态字符串到数值的映射表
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

        // 返回映射的数值，如果找不到则尝试转换为整数
        return $statusMap[$status] ?? (int)$status;
    }

    /**
     * 启动立项审批流程
     * 
     * 为指定的案件启动工作流审批流程
     * 支持选择特定的处理人员，并自动更新案件状态
     * 
     * @param Request $request HTTP请求对象，可包含assignees参数指定处理人
     * @param int $id 案件ID
     * @return \Illuminate\Http\JsonResponse 返回启动结果的JSON响应
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当案件不存在时抛出异常
     */
    public function startWorkflow(Request $request, $id)
    {
        try {
            // 根据ID查找案件
            $case = Cases::findOrFail($id);
            
            // 检查是否已有进行中的工作流程实例
            $existingInstance = $case->workflowInstance;
            if ($existingInstance && $existingInstance->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => '该立项已有进行中的审批流程，无需重复启动'
                ], 400);
            }
            
            // 如果已有工作流实例但状态为已完成或已驳回，则记录日志
            if ($existingInstance && in_array($existingInstance->status, ['completed', 'rejected', 'cancelled'])) {
                \Log::info('ContractCaseController: 发现旧的工作流实例，状态为: ' . $existingInstance->status);
                // 不需要删除，只需要创建新的实例即可，系统会自动关联到最新的实例
            }

            // 根据项目类型选择工作流程
            $caseTypeText = $this->getCaseTypeText($case->case_type);
            $workflowCode = $this->getWorkflowCodeByCaseType($case->case_type);
            
            // 先按case_type文本查找工作流，如果找不到再按code查找
            $workflow = Workflow::where('case_type', $caseTypeText)->where('status', 1)->first();
            
            if (!$workflow) {
                $workflow = Workflow::where('code', $workflowCode)->where('status', 1)->first();
            }
            
            // 如果仍然找不到工作流配置，返回错误
            if (!$workflow) {
                return response()->json([
                    'success' => false,
                    'message' => "未找到对应的工作流程配置。项目类型：{$case->case_type}，查找的工作流程代码：{$workflowCode}"
                ], 400);
            }

            // 获取选择的处理人信息
            $selectedAssignees = $request->input('assignees', []);
            
            // 启动工作流程服务
            $workflowService = app(\App\Services\WorkflowService::class);
            
            // 根据是否有指定处理人选择不同的启动方式
            if (!empty($selectedAssignees)) {
                // 启动工作流程并指定处理人
                $instance = $workflowService->startWorkflowWithAssignees(
                    'case',                    // $businessType - 业务类型
                    $case->id,                // $businessId - 业务ID
                    $case->case_name,         // $businessTitle - 业务标题
                    $workflow->id,            // $workflowId - 工作流ID
                    auth()->id(),             // $createdBy - 创建者ID
                    $selectedAssignees        // $selectedAssignees - 指定的处理人
                );
            } else {
                // 启动工作流程（使用默认处理人）
                $instance = $workflowService->startWorkflow(
                    'case',                    // $businessType - 业务类型
                    $case->id,                // $businessId - 业务ID
                    $case->case_name,         // $businessTitle - 业务标题
                    $workflow->id,            // $workflowId - 工作流ID
                    auth()->id()              // $createdBy - 创建者ID
                );
            }

            // 更新项目状态为立项中
            $case->update(['case_status' => Cases::STATUS_TO_BE_FILED]);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'data' => [
                    'workflow_instance_id' => $instance->id,
                    'workflow_name' => $workflow->name
                ],
                'message' => '审批流程启动成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理：返回错误信息
            return response()->json([
                'success' => false,
                'message' => '启动流程失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取立项工作流信息
     * 
     * 获取指定案件的工作流实例信息和流程节点详情
     * 包含工作流状态、当前节点、处理记录等完整信息
     * 
     * @param int $id 案件ID
     * @return \Illuminate\Http\JsonResponse 返回工作流信息的JSON响应
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当案件不存在时抛出异常
     */
    public function getWorkflow($id)
    {
        try {
            // 根据ID查找案件
            $case = Cases::findOrFail($id);
            
            // 获取工作流实例，预加载工作流和创建者信息
            $instance = WorkflowInstance::where('business_type', 'case')
                ->where('business_id', $case->id)
                ->with(['workflow', 'creator'])
                ->first();

            // 如果没有工作流实例，返回空数据
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

            // 获取流程节点信息，预加载处理人和执行人信息
            $processes = WorkflowProcess::where('instance_id', $instance->id)
                ->with(['assignee', 'processor'])
                ->orderBy('node_index')
                ->get();

            // 返回完整的工作流信息
            return response()->json([
                'success' => true,
                'data' => [
                    // 工作流实例信息
                    'instance' => [
                        'id' => $instance->id,
                        'workflow_name' => $instance->workflow->name,
                        'status' => $instance->status,
                        'current_node_index' => $instance->current_node_index,
                        'created_at' => $instance->created_at,
                        'creator' => $instance->creator ? $instance->creator->real_name : ''
                    ],
                    // 流程节点信息
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
                    // 案件基本信息
                    'case' => $case
                ]
            ]);

        } catch (\Exception $e) {
            // 异常处理：返回错误信息
            return response()->json([
                'success' => false,
                'message' => '获取工作流信息失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 调试：获取待处理数据的详细信息
     * 
     * 用于调试工作流系统，获取当前用户的待处理案件详细信息
     * 包含案件状态、工作流状态、待处理节点等调试数据
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse 返回调试信息的JSON响应
     */
    public function debugPendingData(Request $request)
    {
        try {
            // 获取当前认证用户ID
            $currentUserId = auth()->id();
            
            // 获取所有有工作流实例的案件，预加载工作流实例和流程信息
            $casesWithWorkflow = Cases::with(['workflowInstance.processes'])
                ->whereHas('workflowInstance')
                ->get();
            
            $debugInfo = [];
            
            // 遍历每个案件，收集调试信息
            foreach ($casesWithWorkflow as $case) {
                $instance = $case->workflowInstance;
                
                // 查找当前用户的待处理流程
                $pendingProcesses = $instance->processes()
                    ->where('assignee_id', $currentUserId)
                    ->where('action', 'pending')
                    ->get();
                
                // 构建调试信息数组
                $debugInfo[] = [
                    'case_id' => $case->id,
                    'case_name' => $case->case_name,
                    'case_status' => $case->case_status,
                    'created_by' => $case->created_by,
                    'current_user_id' => $currentUserId,
                    'workflow_status' => $instance->status,
                    'current_node_index' => $instance->current_node_index,
                    // 当前用户的待处理流程
                    'pending_processes_for_user' => $pendingProcesses->map(function($p) {
                        return [
                            'id' => $p->id,
                            'node_name' => $p->node_name,
                            'assignee_id' => $p->assignee_id,
                            'action' => $p->action
                        ];
                    }),
                    // 所有流程节点信息
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
            
            // 返回调试信息
            return response()->json([
                'success' => true,
                'data' => $debugInfo
            ]);
            
        } catch (\Exception $e) {
            // 异常处理：返回错误信息
            return response()->json([
                'success' => false,
                'message' => '调试失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 根据项目类型获取工作流程代码
     * 
     * 根据案件类型返回对应的工作流程代码
     * 用于查找匹配的工作流配置
     * 
     * @param int $caseType 项目类型
     * @return string 工作流程代码
     */
    private function getWorkflowCodeByCaseType($caseType)
    {
        // 先将数字类型转换为文本类型
        $caseTypeText = $this->getCaseTypeText($caseType);
        
        // 根据项目类型返回对应的工作流程代码
        switch ($caseType) {
            case Cases::TYPE_PATENT:        // 专利
            case Cases::TYPE_TRADEMARK:     // 商标
            case Cases::TYPE_COPYRIGHT:     // 版权
                return 'CASE_BUSINESS_FLOW'; // 立案流程(商版专)
            case Cases::TYPE_TECH_SERVICE:  // 科服
                return 'CASE_TECH_SERVICE_FLOW'; // 立案流程(科服)
            default:
                return 'CASE_BUSINESS_FLOW'; // 默认使用商版专流程
        }
    }

    /**
     * 根据案例类型数字获取对应的文本
     * 
     * 将数字类型的案件类型转换为对应的中文文本
     * 用于显示和工作流匹配
     * 
     * @param int $caseType 案件类型数字
     * @return string 对应的中文文本
     */
    private function getCaseTypeText($caseType)
    {
        // 案件类型数字到文本的映射
        $types = [
            Cases::TYPE_PATENT => '专利',      // 1 -> 专利
            Cases::TYPE_TRADEMARK => '商标',   // 2 -> 商标
            Cases::TYPE_COPYRIGHT => '版权',   // 3 -> 版权
            Cases::TYPE_TECH_SERVICE => '科服', // 4 -> 科服
        ];

        // 返回对应的文本，如果找不到则默认返回'专利'
        return $types[$caseType] ?? '专利';
    }
}
