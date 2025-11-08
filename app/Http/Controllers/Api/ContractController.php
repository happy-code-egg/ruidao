<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractService;
use App\Models\ContractAttachment;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * 合同控制器
 * 负责合同的增删改查、服务管理、附件管理和工作流相关操作
 */
class ContractController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * 获取合同列表
     * 
     * 提供合同数据的分页查询功能，支持多种搜索条件和过滤选项
     * 包括客户信息、合同基本信息、业务人员、技术主导、金额范围、日期范围等
     * 
     * @param Request $request HTTP请求对象，包含查询参数
     * 
     * 查询参数说明：
     * - my_contracts_only: bool 是否只查询当前用户创建的合同
     * - my_pending_only: bool 是否只查询当前用户有待处理任务的合同
     * - customer_id: int 客户ID
     * - customer_name: string 客户名称（模糊搜索）
     * - contract_no: string 合同编号（模糊搜索）
     * - contract_name: string 合同名称（模糊搜索）
     * - contract_code: string 合同代码（模糊搜索）
     * - service_type: string 服务类型（支持JSON数组和字符串搜索）
     * - contract_type: string 合同类型
     * - status: string 合同状态
     * - paper_status: string 纸质合同状态
     * - party_b_company_id: int 乙方公司ID
     * - business_person_id: int 业务人员ID
     * - salesman: string 业务人员姓名（模糊搜索）
     * - technical_director_id: int 技术主导ID
     * - technical_director: string 技术主导姓名（模糊搜索）
     * - party_a_contact: string 甲方联系人（模糊搜索）
     * - party_a_phone: string 甲方电话（模糊搜索）
     * - party_b_signer: string 乙方签约人（模糊搜索）
     * - min_service_fee: decimal 最小服务费
     * - max_service_fee: decimal 最大服务费
     * - min_amount: decimal 最小总金额
     * - max_amount: decimal 最大总金额
     * - min_case_count: int 最小项目数量
     * - max_case_count: int 最大项目数量
     * - sign_date_start: date 签约开始日期
     * - sign_date_end: date 签约结束日期
     * - creator: string 创建人姓名（模糊搜索）
     * - updater: string 修改人姓名（模糊搜索）
     * - sort_field: string 排序字段，默认created_at
     * - sort_order: string 排序方向，默认desc
     * - page_size: int 每页数量，默认15
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应结构：
     * {
     *   "success": true,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "contract_no": "HT202401001",
     *         "contract_name": "软件开发合同",
     *         "contract_code": "SW001",
     *         "contract_type": "standard",
     *         "service_type": ["development", "testing"],
     *         "status": "active",
     *         "paper_status": "signed",
     *         "service_fee": 100000.00,
     *         "total_amount": 120000.00,
     *         "case_count": 5,
     *         "signing_date": "2024-01-15",
     *         "customer": {...},
     *         "businessPerson": {...},
     *         "technicalDirector": {...},
     *         "partyAContact": {...},
     *         "partyBSigner": {...},
     *         "partyBCompany": {...},
     *         "creator": {...},
     *         "updater": {...}
     *       }
     *     ],
     *     "first_page_url": "...",
     *     "from": 1,
     *     "last_page": 10,
     *     "last_page_url": "...",
     *     "next_page_url": "...",
     *     "path": "...",
     *     "per_page": 15,
     *     "prev_page_url": null,
     *     "to": 15,
     *     "total": 150
     *   },
     *   "message": "获取成功"
     * }
     * 
     * 错误响应结构：
     * {
     *   "success": false,
     *   "message": "获取失败：错误详情"
     * }
     */
    public function index(Request $request)
    {
        try {
            // 记录调试信息，用于问题排查
            \Log::info('Contract index called with parameters:', $request->all());
            
            // 构建基础查询，预加载所有相关联的数据以避免N+1查询问题
            $query = Contract::with([
                'customer',           // 客户信息
                'businessPerson',     // 业务人员信息
                'technicalDirector',  // 技术主导信息
                'partyAContact',      // 甲方联系人信息
                'partyBSigner',       // 乙方签约人信息
                'partyBCompany',      // 乙方公司信息
                'creator',            // 创建人信息
                'updater'             // 更新人信息
            ]);

            // 用户权限过滤：只获取当前用户创建的合同
            if ($request->input('my_contracts_only', false)) {
                $user = $request->user();
                if ($user) {
                    $query->where('created_by', $user->id);
                }
            }

            // 基础搜索条件：客户ID精确匹配
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // 客户名称模糊搜索：通过关联查询客户表
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // 合同编号模糊搜索
            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%');
            }

            // 合同名称模糊搜索
            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%');
            }

            // 合同代码模糊搜索
            if ($request->filled('contract_code')) {
                $query->where('contract_code', 'like', '%' . $request->contract_code . '%');
            }

            // 服务类型搜索：支持JSON数组和字符串两种格式
            if ($request->filled('service_type')) {
                $serviceType = $request->service_type;
                $query->where(function ($q) use ($serviceType) {
                    // 直接字符串匹配或JSON数组中包含该值
                    $q->where('service_type', $serviceType)
                      ->orWhere('service_type', 'like', '%"' . $serviceType . '"%');
                });
            }

            // 合同类型精确匹配
            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type);
            }

            // 合同状态精确匹配
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 待处理合同查询：查询当前用户有待处理工作流任务的合同
            if ($request->input('my_pending_only', false)) {
                $user = $request->user();
                if ($user) {
                    // 通过工作流实例查询待处理的合同
                    $query->whereHas('workflowInstance', function ($q) use ($user) {
                        $q->where('status', \App\Models\WorkflowInstance::STATUS_PENDING)
                          ->whereHas('processes', function ($processQuery) use ($user) {
                              $processQuery->where('assignee_id', $user->id)
                                          ->where('action', \App\Models\WorkflowProcess::ACTION_PENDING);
                          });
                    });
                }
            }

            // 纸质合同状态精确匹配
            if ($request->filled('paper_status')) {
                $query->where('paper_status', $request->paper_status);
            }

            // 乙方公司ID精确匹配
            if ($request->filled('party_b_company_id')) {
                $query->where('party_b_company_id', $request->party_b_company_id);
            }

            // 业务人员ID精确匹配
            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id);
            }

            // 业务人员姓名模糊搜索：通过关联查询用户表
            if ($request->filled('salesman')) {
                $query->whereHas('businessPerson', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->salesman . '%');
                });
            }

            // 技术主导ID精确匹配
            if ($request->filled('technical_director_id')) {
                $query->where('technical_director_id', $request->technical_director_id);
            }

            // 甲方联系人姓名模糊搜索：通过关联查询联系人表
            if ($request->filled('party_a_contact')) {
                $query->whereHas('partyAContact', function ($q) use ($request) {
                    $q->where('contact_name', 'like', '%' . $request->party_a_contact . '%');
                });
            }

            // 甲方电话模糊搜索
            if ($request->filled('party_a_phone')) {
                $query->where('party_a_phone', 'like', '%' . $request->party_a_phone . '%');
            }

            // 服务费范围搜索：最小值过滤
            if ($request->filled('min_service_fee')) {
                $query->where('service_fee', '>=', $request->min_service_fee);
            }

            // 服务费范围搜索：最大值过滤
            if ($request->filled('max_service_fee')) {
                $query->where('service_fee', '<=', $request->max_service_fee);
            }

            // 总金额范围搜索：最小值过滤
            if ($request->filled('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }

            // 总金额范围搜索：最大值过滤
            if ($request->filled('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }

            // 项目数量范围搜索：最小值过滤
            if ($request->filled('min_case_count')) {
                $query->where('case_count', '>=', $request->min_case_count);
            }
            
            // 项目数量范围搜索：最大值过滤
            if ($request->filled('max_case_count')) {
                $query->where('case_count', '<=', $request->max_case_count);
            }

            // 技术主导姓名模糊搜索：通过关联查询用户表
            if ($request->filled('technical_director')) {
                $query->whereHas('technicalDirector', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->technical_director . '%');
                });
            }

            // 乙方签约人姓名模糊搜索：通过关联查询用户表
            if ($request->filled('party_b_signer')) {
                $query->whereHas('partyBSigner', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->party_b_signer . '%');
                });
            }

            // 签约日期范围搜索：开始日期过滤
            if ($request->filled('sign_date_start')) {
                $startDate = $request->sign_date_start;
                \Log::info('Processing sign_date_start:', ['value' => $startDate]);
                
                // 验证并解析日期格式，确保数据安全性
                try {
                    $date = \Carbon\Carbon::parse($startDate);
                    $query->where('signing_date', '>=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_start format:', ['value' => $startDate, 'error' => $e->getMessage()]);
                    // 跳过无效的日期，不影响其他查询条件
                }
            }

            // 签约日期范围搜索：结束日期过滤
            if ($request->filled('sign_date_end')) {
                $endDate = $request->sign_date_end;
                \Log::info('Processing sign_date_end:', ['value' => $endDate]);
                
                // 验证并解析日期格式，确保数据安全性
                try {
                    $date = \Carbon\Carbon::parse($endDate);
                    $query->where('signing_date', '<=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_end format:', ['value' => $endDate, 'error' => $e->getMessage()]);
                    // 跳过无效的日期，不影响其他查询条件
                }
            }

            // 创建人姓名模糊搜索：通过关联查询用户表
            if ($request->filled('creator')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->creator . '%');
                });
            }

            // 修改人姓名模糊搜索：通过关联查询用户表
            if ($request->filled('updater')) {
                $query->whereHas('updater', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->updater . '%');
                });
            }

            // 结果排序：支持自定义排序字段和方向
            $sortField = $request->get('sort_field', 'created_at');  // 默认按创建时间排序
            $sortOrder = $request->get('sort_order', 'desc');        // 默认降序排列
            $query->orderBy($sortField, $sortOrder);

            // 分页处理：支持自定义每页数量
            $pageSize = $request->get('page_size', 15);  // 默认每页15条记录
            $contracts = $query->paginate($pageSize);

            // 返回成功响应，包含分页数据
            return response()->json([
                'success' => true,
                'data' => $contracts,
                'message' => '获取成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理：记录错误日志并返回错误响应
            \Log::error('Contract index error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同详情
     * 
     * 根据合同ID获取单个合同的完整信息，包括所有关联数据
     * 用于合同详情页面的数据展示和编辑功能
     * 
     * @param int $id 合同ID
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应结构：
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "contract_no": "HT202401001",
     *     "contract_name": "软件开发合同",
     *     "contract_code": "SW001",
     *     "contract_type": "standard",
     *     "service_type": ["development", "testing"],
     *     "status": "active",
     *     "paper_status": "signed",
     *     "service_fee": 100000.00,
     *     "total_amount": 120000.00,
     *     "case_count": 5,
     *     "signing_date": "2024-01-15",
     *     "customer": {
     *       "id": 1,
     *       "customer_name": "ABC科技有限公司",
     *       "contact_person": "张三",
     *       "phone": "13800138000"
     *     },
     *     "businessPerson": {
     *       "id": 1,
     *       "name": "李四",
     *       "email": "lisi@example.com"
     *     },
     *     "technicalDirector": {
     *       "id": 2,
     *       "name": "王五",
     *       "email": "wangwu@example.com"
     *     },
     *     "partyAContact": {
     *       "id": 1,
     *       "contact_name": "张三",
     *       "phone": "13800138000",
     *       "email": "zhangsan@abc.com"
     *     },
     *     "partyBSigner": {
     *       "id": 3,
     *       "name": "赵六",
     *       "position": "总经理"
     *     },
     *     "partyBCompany": {
     *       "id": 1,
     *       "company_name": "睿道科技有限公司",
     *       "address": "北京市朝阳区xxx"
     *     },
     *     "services": [
     *       {
     *         "id": 1,
     *         "service_name": "软件开发",
     *         "service_description": "定制软件开发服务",
     *         "service_fee": 80000.00
     *       }
     *     ],
     *     "attachments": [
     *       {
     *         "id": 1,
     *         "file_name": "合同附件.pdf",
     *         "file_path": "/uploads/contracts/xxx.pdf",
     *         "file_size": 1024000,
     *         "uploader": {
     *           "id": 1,
     *           "name": "上传者姓名"
     *         }
     *       }
     *     ],
     *     "creator": {
     *       "id": 1,
     *       "name": "创建者姓名"
     *     },
     *     "updater": {
     *       "id": 2,
     *       "name": "更新者姓名"
     *     }
     *   },
     *   "message": "获取成功"
     * }
     * 
     * 错误响应结构：
     * {
     *   "success": false,
     *   "message": "获取失败：合同不存在"
     * }
     */
    public function show($id)
    {
        try {
            // 查询合同并预加载所有相关联的数据，避免N+1查询问题
            $contract = Contract::with([
                'customer',                // 客户信息
                'businessPerson',          // 业务人员信息
                'technicalDirector',       // 技术主导信息
                'partyAContact',           // 甲方联系人信息
                'partyBSigner',            // 乙方签约人信息
                'partyBCompany',           // 乙方公司信息
                'services',                // 合同服务项目列表
                'attachments.uploader',    // 合同附件及上传者信息
                'creator',                 // 创建人信息
                'updater'                  // 最后更新人信息
            ])->findOrFail($id);  // 如果找不到记录会抛出ModelNotFoundException

            // 返回成功响应，包含完整的合同数据
            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => '获取成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理：记录错误日志并返回404错误响应
            \Log::error('Contract show error:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 创建合同
     * 
     * 创建新的合同记录，包括数据验证、服务明细创建、金额计算等功能
     * 支持标准合同和非标合同两种类型，自动生成合同编号
     * 
     * @param Request $request HTTP请求对象，包含合同创建数据
     * 
     * 请求参数说明：
     * - customer_id: int 客户ID（必填，必须存在于customers表）
     * - contract_name: string 合同名称（必填，最大200字符）
     * - contract_type: string 合同类型（必填，standard|non-standard）
     * - service_type: string|array 服务类型（标准合同为字符串，非标合同为数组）
     * - business_person_id: int 业务人员ID（可选，必须存在于users表）
     * - technical_director_id: int 技术主导ID（可选，必须存在于users表）
     * - party_a_contact_id: int 甲方联系人ID（可选，必须存在于customer_contacts表）
     * - party_a_address: string 甲方地址（可选）
     * - party_b_signer_id: int 乙方签约人ID（可选，必须存在于users表）
     * - party_b_company_id: int 乙方公司ID（可选，必须存在于our_companies表）
     * - signing_date: date 签约日期（可选）
     * - validity_start_date: date 有效期开始日期（可选）
     * - validity_end_date: date 有效期结束日期（可选）
     * - service_fee: decimal 服务费（可选，最小值0）
     * - official_fee: decimal 官费（可选，最小值0）
     * - channel_fee: decimal 渠道费（可选，最小值0）
     * - services: array 服务明细列表（可选）
     *   - service_name: string 服务名称（必填）
     *   - service_description: string 服务描述（可选）
     *   - amount: decimal 服务金额（可选，最小值0）
     *   - official_fee: decimal 服务官费（可选，最小值0）
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应结构：
     * {
     *   "success": true,
     *   "message": "创建成功",
     *   "data": {
     *     "contract": {
     *       "id": 1,
     *       "contract_no": "HT202401001",
     *       "contract_name": "软件开发合同",
     *       "contract_type": "standard",
     *       "service_type": "development",
     *       "total_service_fee": 100000.00,
     *       "total_amount": 120000.00,
     *       "created_by": 1,
     *       "services": [...],
     *       "workflowInstance": null
     *     },
     *     "workflow_config": {
     *       "id": 1,
     *       "name": "合同审批流程",
     *       "nodes": [...]
     *     }
     *   }
     * }
     * 
     * 验证失败响应结构：
     * {
     *   "success": false,
     *   "message": "验证失败",
     *   "errors": {
     *     "customer_id": ["客户ID字段是必填的"]
     *   }
     * }
     * 
     * 错误响应结构：
     * {
     *   "success": false,
     *   "message": "创建失败：错误详情"
     * }
     */
    public function store(Request $request)
    {
        try {
            // 定义数据验证规则
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'contract_name' => 'required|string|max:200',
                'contract_type' => 'required|string|in:standard,non-standard',
                'service_type' => [
                    'required',
                    // 自定义验证规则：根据合同类型验证服务类型格式
                    function ($attribute, $value, $fail) use ($request) {
                        $contractType = $request->input('contract_type');
                        if ($contractType === 'standard') {
                            // 标准合同：服务类型必须为字符串
                            if (!is_string($value)) {
                                $fail('标准合同的服务类型必须为单个值');
                            }
                        } elseif ($contractType === 'non-standard') {
                            // 非标合同：服务类型必须为非空数组
                            if (!is_array($value) || empty($value)) {
                                $fail('非标合同的服务类型必须为非空数组');
                            }
                        }
                    }
                ],
                'business_person_id' => 'nullable|integer|exists:users,id',
                'technical_director_id' => 'nullable|integer|exists:users,id',
                'party_a_contact_id' => 'nullable|integer|exists:customer_contacts,id',
                'party_a_address' => 'nullable|string',
                'party_b_signer_id' => 'nullable|integer|exists:users,id',
                'party_b_company_id' => 'nullable|integer|exists:our_companies,id',
                'signing_date' => 'nullable|date',
                'validity_start_date' => 'nullable|date',
                'validity_end_date' => 'nullable|date',
                'service_fee' => 'nullable|numeric|min:0',
                'official_fee' => 'nullable|numeric|min:0',
                'channel_fee' => 'nullable|numeric|min:0',
                'services' => 'nullable|array',
                'services.*.service_name' => 'required_with:services|string|max:200',
                'services.*.service_description' => 'nullable|string',
                'services.*.amount' => 'nullable|numeric|min:0',
                'services.*.official_fee' => 'nullable|numeric|min:0',
            ]);

            // 验证失败时返回错误响应
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 开始数据库事务
            DB::beginTransaction();

            $data = $request->all();
            
            // 生成合同编号（自动生成唯一编号）
            $data['contract_no'] = Contract::generateContractNo();
            
            // 处理服务类型格式（确保数据格式正确）
            if ($data['contract_type'] === 'standard' && is_array($data['service_type'])) {
                // 如果前端错误地发送了数组，取第一个元素
                $data['service_type'] = is_array($data['service_type']) ? $data['service_type'][0] : $data['service_type'];
            } elseif ($data['contract_type'] === 'non-standard' && is_string($data['service_type'])) {
                // 如果前端错误地发送了字符串，转换为数组
                $data['service_type'] = [$data['service_type']];
            }
            
            // 计算总金额（服务费 + 官费 + 渠道费）
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $officialFee = floatval($data['official_fee'] ?? 0);
            $channelFee = floatval($data['channel_fee'] ?? 0);
            
            // 总服务费 = 服务费 + 渠道费
            $data['total_service_fee'] = $serviceFee + $channelFee;
            // 总金额 = 总服务费 + 官费
            $data['total_amount'] = $data['total_service_fee'] + $officialFee;
            
            // 处理有效期范围（兼容前端数组格式）
            if ($request->filled('validity') && is_array($request->validity)) {
                $data['validity_start_date'] = $request->validity[0] ?? null;
                $data['validity_end_date'] = $request->validity[1] ?? null;
            }
            
            // 设置创建人和更新人为当前登录用户
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // 创建合同记录
            $contract = Contract::create($data);

            // 创建服务明细（如果有提供服务明细数据）
            if (!empty($data['services'])) {
                foreach ($data['services'] as $index => $serviceData) {
                    // 设置服务明细的合同ID和排序顺序
                    $serviceData['contract_id'] = $contract->id;
                    $serviceData['sort_order'] = $index + 1; // 从1开始排序
                    ContractService::create($serviceData);
                }
            }

            // 获取合同工作流配置信息（不自动启动工作流）
            $contractWorkflow = null;
            try {
                // 查找启用状态的合同工作流配置
                $contractWorkflow = Workflow::where('case_type', '合同')
                    ->where('status', 1)
                    ->first();
            } catch (\Exception $e) {
                // 记录工作流配置获取失败的警告日志
                \Log::warning('获取合同工作流配置失败', [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage()
                ]);
            }

            // 提交数据库事务
            DB::commit();

            // 准备返回数据，包含合同信息和工作流配置
            $responseData = [
                'contract' => $contract->load(['services', 'workflowInstance']),
                'workflow_config' => null
            ];

            // 如果找到工作流配置，添加到返回数据中
            if ($contractWorkflow) {
                $responseData['workflow_config'] = [
                    'id' => $contractWorkflow->id,
                    'name' => $contractWorkflow->name,
                    'nodes' => $contractWorkflow->nodes
                ];
            }

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            // 回滚数据库事务
            DB::rollBack();
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新合同
     * 
     * 更新指定ID的合同记录，包括数据验证、服务明细更新、金额重新计算等功能
     * 支持标准合同和非标合同两种类型的更新，自动处理服务类型格式
     * 
     * @param Request $request HTTP请求对象，包含合同更新数据
     * @param int $id 合同ID
     * 
     * 请求参数说明：
     * - customer_id: int 客户ID（必填，必须存在于customers表）
     * - contract_name: string 合同名称（必填，最大200字符）
     * - contract_type: string 合同类型（必填，standard|non-standard）
     * - service_type: string|array 服务类型（标准合同为字符串，非标合同为数组）
     * - business_person_id: int 业务人员ID（可选，必须存在于users表）
     * - technical_director_id: int 技术主导ID（可选，必须存在于users表）
     * - party_a_contact_id: int 甲方联系人ID（可选，必须存在于customer_contacts表）
     * - party_a_address: string 甲方地址（可选）
     * - party_b_signer_id: int 乙方签约人ID（可选，必须存在于users表）
     * - party_b_company_id: int 乙方公司ID（可选，必须存在于our_companies表）
     * - signing_date: date 签约日期（可选）
     * - validity_start_date: date 有效期开始日期（可选）
     * - validity_end_date: date 有效期结束日期（可选）
     * - service_fee: decimal 服务费（可选，最小值0）
     * - official_fee: decimal 官费（可选，最小值0）
     * - channel_fee: decimal 渠道费（可选，最小值0）
     * - services: array 服务明细列表（可选）
     *   - service_name: string 服务名称（必填）
     *   - service_description: string 服务描述（可选）
     *   - amount: decimal 服务金额（可选，最小值0）
     *   - official_fee: decimal 服务官费（可选，最小值0）
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应结构：
     * {
     *   "success": true,
     *   "message": "更新成功",
     *   "data": {
     *     "id": 1,
     *     "contract_no": "HT202401001",
     *     "contract_name": "软件开发合同",
     *     "contract_type": "standard",
     *     "service_type": "development",
     *     "total_service_fee": 100000.00,
     *     "total_amount": 120000.00,
     *     "updated_by": 1,
     *     "services": [...]
     *   }
     * }
     * 
     * 验证失败响应结构：
     * {
     *   "success": false,
     *   "message": "验证失败",
     *   "errors": {
     *     "customer_id": ["客户ID字段是必填的"]
     *   }
     * }
     * 
     * 合同不存在响应结构：
     * {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * 错误响应结构：
     * {
     *   "success": false,
     *   "message": "更新失败：错误详情"
     * }
     */
    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'contract_name' => 'required|string|max:200',
                'contract_type' => 'required|string|in:standard,non-standard',
                'service_type' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $contractType = $request->input('contract_type');
                        if ($contractType === 'standard') {
                            if (!is_string($value)) {
                                $fail('标准合同的服务类型必须为单个值');
                            }
                        } elseif ($contractType === 'non-standard') {
                            if (!is_array($value) || empty($value)) {
                                $fail('非标合同的服务类型必须为非空数组');
                            }
                        }
                    }
                ],
                'business_person_id' => 'nullable|integer|exists:users,id',
                'technical_director_id' => 'nullable|integer|exists:users,id',
                'party_a_contact_id' => 'nullable|integer|exists:customer_contacts,id',
                'party_a_address' => 'nullable|string',
                'party_b_signer_id' => 'nullable|integer|exists:users,id',
                'party_b_company_id' => 'nullable|integer|exists:our_companies,id',
                'signing_date' => 'nullable|date',
                'validity_start_date' => 'nullable|date',
                'validity_end_date' => 'nullable|date',
                'service_fee' => 'nullable|numeric|min:0',
                'official_fee' => 'nullable|numeric|min:0',
                'channel_fee' => 'nullable|numeric|min:0',
                'services' => 'nullable|array',
                'services.*.service_name' => 'required_with:services|string|max:200',
                'services.*.service_description' => 'nullable|string',
                'services.*.amount' => 'nullable|numeric|min:0',
                'services.*.official_fee' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();
            
            // 处理服务类型格式
            if ($data['contract_type'] === 'standard' && is_array($data['service_type'])) {
                // 如果前端错误地发送了数组，取第一个元素
                $data['service_type'] = is_array($data['service_type']) ? $data['service_type'][0] : $data['service_type'];
            } elseif ($data['contract_type'] === 'non-standard' && is_string($data['service_type'])) {
                // 如果前端错误地发送了字符串，转换为数组
                $data['service_type'] = [$data['service_type']];
            }
            
            // 计算总金额
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $officialFee = floatval($data['official_fee'] ?? 0);
            $channelFee = floatval($data['channel_fee'] ?? 0);
            
            $data['total_service_fee'] = $serviceFee + $channelFee;
            $data['total_amount'] = $data['total_service_fee'] + $officialFee;
            
            // 处理有效期范围
            if ($request->filled('validity') && is_array($request->validity)) {
                $data['validity_start_date'] = $request->validity[0] ?? null;
                $data['validity_end_date'] = $request->validity[1] ?? null;
            }
            
            // 设置更新人为当前登录用户
            $data['updated_by'] = auth()->id();

            // 更新合同记录
            $contract->update($data);

            // 更新服务明细（如果请求中包含services字段）
            if (array_key_exists('services', $data)) {
                // 删除原有的所有服务明细记录
                $contract->services()->delete();
                
                // 创建新的服务明细记录
                if (!empty($data['services'])) {
                    foreach ($data['services'] as $index => $serviceData) {
                        // 设置服务明细的合同ID和排序顺序
                        $serviceData['contract_id'] = $contract->id;
                        $serviceData['sort_order'] = $index + 1; // 从1开始排序
                        ContractService::create($serviceData);
                    }
                }
            }

            // 提交数据库事务
            DB::commit();

            // 返回成功响应，包含更新后的合同数据和服务明细
            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $contract->load(['services'])
            ]);

        } catch (\Exception $e) {
            // 回滚数据库事务
            DB::rollBack();
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同
     * 
     * 删除指定ID的合同记录，包括相关的服务明细和附件
     * 删除前会检查合同是否有关联的项目，如果有则不允许删除
     * 
     * @param int $id 合同ID
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应结构：
     * {
     *   "success": true,
     *   "message": "删除成功"
     * }
     * 
     * 业务规则限制响应结构：
     * {
     *   "success": false,
     *   "message": "该合同下存在项目，无法删除"
     * }
     * 
     * 合同不存在响应结构：
     * {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * 错误响应结构：
     * {
     *   "success": false,
     *   "message": "删除失败：错误详情"
     * }
     */
    public function destroy($id)
    {
        try {
            // 查找指定ID的合同记录，如果不存在则抛出404异常
            $contract = Contract::findOrFail($id);

            // 检查业务规则：合同下是否有关联的项目
            if ($contract->cases()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同下存在项目，无法删除'
                ], 400);
            }

            // 开始数据库事务
            DB::beginTransaction();

            // 删除合同的服务明细记录
            $contract->services()->delete();

            // 删除合同的附件记录
            $contract->attachments()->delete();

            // 删除合同主记录
            $contract->delete();

            // 提交数据库事务
            DB::commit();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            // 回滚数据库事务
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传合同附件
     * 
     * @param Request $request 请求对象
     * @param int $id 合同ID
     * 
     * @bodyParam file file required 上传的文件，最大50MB
     * @bodyParam file_type string required 文件类型，最大50字符
     * @bodyParam file_sub_type string 文件子类型，最大50字符
     * @bodyParam file_desc string 文件描述
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "上传成功",
     *   "data": {
     *     "id": 1,
     *     "contract_id": 1,
     *     "file_type": "合同文件",
     *     "file_sub_type": "主合同",
     *     "file_name": "contract.pdf",
     *     "file_path": "contracts/1/1234567890_contract.pdf",
     *     "file_size": 1024000,
     *     "file_extension": "pdf",
     *     "mime_type": "application/pdf",
     *     "uploader_id": 1,
     *     "upload_time": "2024-01-01 12:00:00",
     *     "uploader": {
     *       "id": 1,
     *       "name": "张三"
     *     }
     *   }
     * }
     * 
     * @response 422 {
     *   "success": false,
     *   "message": "验证失败",
     *   "errors": {
     *     "file": ["文件字段是必需的"],
     *     "file_type": ["文件类型字段是必需的"]
     *   }
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "上传失败：具体错误信息"
     * }
     */
    public function uploadAttachment(Request $request, $id)
    {
        try {
            // 查找指定ID的合同记录，如果不存在则抛出404异常
            $contract = Contract::findOrFail($id);

            // 定义文件上传的验证规则
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:51200', // 文件必需，最大50MB
                'file_type' => 'required|string|max:50', // 文件类型必需，最大50字符
                'file_sub_type' => 'nullable|string|max:50', // 文件子类型可选，最大50字符
                'file_desc' => 'nullable|string', // 文件描述可选
            ]);

            // 验证失败时返回错误响应
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 获取上传的文件对象
            $file = $request->file('file');
            // 生成唯一的文件名（时间戳 + 原文件名）
            $fileName = time() . '_' . $file->getClientOriginalName();
            // 将文件存储到指定目录（contracts/合同ID/文件名）
            $filePath = $file->storeAs('contracts/' . $contract->id, $fileName, 'public');

            // 创建合同附件记录
            $attachment = ContractAttachment::create([
                'contract_id' => $contract->id, // 关联的合同ID
                'file_type' => $request->file_type, // 文件类型
                'file_sub_type' => $request->file_sub_type, // 文件子类型
                'file_name' => $file->getClientOriginalName(), // 原始文件名
                'file_path' => $filePath, // 存储路径
                'file_size' => $file->getSize(), // 文件大小（字节）
                'file_extension' => $file->getClientOriginalExtension(), // 文件扩展名
                'mime_type' => $file->getMimeType(), // MIME类型
                'uploader_id' => auth()->id(), // 上传者ID
                'upload_time' => now(), // 上传时间
            ]);

            // 返回成功响应，包含附件信息和上传者信息
            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => $attachment->load('uploader')
            ]);

        } catch (\Exception $e) {
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同附件
     * 
     * @param int $id 合同ID
     * @param int $attachmentId 附件ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "删除成功"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "附件不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "删除失败：具体错误信息"
     * }
     */
    public function deleteAttachment($id, $attachmentId)
    {
        try {
            // 查找指定ID的合同记录，如果不存在则抛出404异常
            $contract = Contract::findOrFail($id);
            // 查找合同下指定ID的附件记录，如果不存在则抛出404异常
            $attachment = $contract->attachments()->findOrFail($attachmentId);

            // 删除物理文件（如果文件路径存在且文件确实存在）
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            // 删除数据库记录
            $attachment->delete();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下载合同附件
     * 
     * @param int $id 合同ID
     * @param int $attachmentId 附件ID
     * 
     * @response 200 文件下载流
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "附件不存在"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "文件不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "下载失败：具体错误信息"
     * }
     */
    public function downloadAttachment($id, $attachmentId)
    {
        try {
            // 查找指定ID的合同记录，如果不存在则抛出404异常
            $contract = Contract::findOrFail($id);
            // 查找合同下指定ID的附件记录，如果不存在则抛出404异常
            $attachment = $contract->attachments()->findOrFail($attachmentId);

            // 检查文件是否存在
            if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在'
                ], 404);
            }

            // 构建文件的完整路径
            $filePath = storage_path('app/public/' . $attachment->file_path);
            // 返回文件下载响应，使用原始文件名
            return response()->download($filePath, $attachment->file_name);

        } catch (\Exception $e) {
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '下载失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 导出合同列表为Excel
     * 
     * @param Request $request 请求对象
     * 
     * @queryParam my_contracts_only boolean 是否只导出当前用户创建的合同
     * @queryParam customer_name string 客户名称（模糊搜索）
     * @queryParam contract_no string 合同流水号（模糊搜索）
     * @queryParam contract_code string 合同代码（模糊搜索）
     * @queryParam service_type string 服务类型
     * @queryParam status string 合同状态
     * @queryParam paper_status string 纸件状态
     * @queryParam party_b_company_id int 乙方公司ID
     * @queryParam min_service_fee decimal 最小服务费
     * @queryParam max_service_fee decimal 最大服务费
     * @queryParam min_amount decimal 最小总金额
     * @queryParam max_amount decimal 最大总金额
     * @queryParam sign_date_start date 签约开始日期
     * @queryParam sign_date_end date 签约结束日期
     * @queryParam selected_fields string 选择导出的字段（JSON格式）
     * 
     * @response 200 Excel文件下载流
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "导出失败：具体错误信息"
     * }
     */
    public function export(Request $request)
    {
        try {
            $query = Contract::with([
                'customer',
                'businessPerson',
                'technicalDirector',
                'partyAContact',
                'partyBSigner',
                'partyBCompany',
                'creator',
                'updater'
            ]);

            // 根据请求参数决定是否过滤用户
            if ($request->input('my_contracts_only', false)) {
                // 只获取当前用户创建的合同
                $user = $request->user();
                if ($user) {
                    $query->where('created_by', $user->id);
                }
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }
            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%');
            }
            if ($request->filled('contract_code')) {
                $query->where('contract_code', 'like', '%' . $request->contract_code . '%');
            }
            if ($request->filled('service_type')) {
                $query->where('service_type', $request->service_type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('paper_status')) {
                $query->where('paper_status', $request->paper_status);
            }
            if ($request->filled('party_b_company_id')) {
                $query->where('party_b_company_id', $request->party_b_company_id);
            }
            if ($request->filled('min_service_fee')) {
                $query->where('service_fee', '>=', $request->min_service_fee);
            }
            if ($request->filled('max_service_fee')) {
                $query->where('service_fee', '<=', $request->max_service_fee);
            }
            if ($request->filled('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }
            if ($request->filled('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }
            if ($request->filled('sign_date_start')) {
                try {
                    $date = \Carbon\Carbon::parse($request->sign_date_start);
                    $query->where('signing_date', '>=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_start format in export:', ['value' => $request->sign_date_start, 'error' => $e->getMessage()]);
                }
            }
            if ($request->filled('sign_date_end')) {
                try {
                    $date = \Carbon\Carbon::parse($request->sign_date_end);
                    $query->where('signing_date', '<=', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    \Log::error('Invalid sign_date_end format in export:', ['value' => $request->sign_date_end, 'error' => $e->getMessage()]);
                }
            }

            $contracts = $query->orderBy('created_at', 'desc')->limit(5000)->get();

            $selectedFields = $request->input('selected_fields', '[]');
            $fields = json_decode($selectedFields, true) ?: [];
            $fieldKeys = collect($fields)->pluck('prop')->all();

            $mapRow = function ($c) {
                return [
                    '合同流水号' => $c->contract_no,
                    '合同编号' => $c->contract_no,
                    '合同代码' => $c->contract_code,
                    '合同名称' => $c->contract_name,
                    '业务服务类型' => $c->service_type,
                    '合同摘要' => $c->summary,
                    '客户名称' => optional($c->customer)->customer_name,
                    '对应商机号' => $c->opportunity_no,
                    '对应商机名称' => $c->opportunity_name,
                    '合同服务费' => $c->total_service_fee ?? $c->service_fee,
                    '总金额' => $c->total_amount,
                    '项目数量' => $c->case_count,
                    '甲方联系人' => optional($c->partyAContact)->contact_name,
                    '联系电话' => $c->party_a_phone,
                    '甲方签约地址' => $c->party_a_address,
                    '业务人员' => optional($c->businessPerson)->name,
                    '技术主导' => optional($c->technicalDirector)->name,
                    '乙方签约人' => optional($c->partyBSigner)->name,
                    '乙方手机' => $c->party_b_phone,
                    '签约日期' => $c->signing_date,
                    '创建人' => optional($c->creator)->name,
                    '创建时间' => optional($c->created_at)->toDateTimeString(),
                    '合同状态' => $c->status,
                    '最后处理时间' => $c->last_process_time,
                    '对接人部门' => $c->process_remark,
                    '纸件状态' => $c->paper_status,
                ];
            };

            $exportData = $contracts->map($mapRow)->all();

            if (!empty($fieldKeys)) {
                $titleMap = [
                    'contractNo' => '合同流水号',
                    'contractNumber' => '合同编号',
                    'contractCode' => '合同代码',
                    'contractName' => '合同名称',
                    'serviceType' => '业务服务类型',
                    'summary' => '合同摘要',
                    'customerName' => '客户名称',
                    'opportunityNo' => '对应商机号',
                    'opportunityName' => '对应商机名称',
                    'serviceFee' => '合同服务费',
                    'totalAmount' => '总金额',
                    'caseCount' => '项目数量',
                    'partyAContact' => '甲方联系人',
                    'partyAPhone' => '联系电话',
                    'partyAAddress' => '甲方签约地址',
                    'salesman' => '业务人员',
                    'technicalDirector' => '技术主导',
                    'partyBSigner' => '乙方签约人',
                    'partyBPhone' => '乙方手机',
                    'signDate' => '签约日期',
                    'creator' => '创建人',
                    'createTime' => '创建时间',
                    'status' => '合同状态',
                    'lastProcessTime' => '最后处理时间',
                    'processRemark' => '对接人部门',
                    'paperStatus' => '纸件状态',
                ];

                $exportData = array_map(function ($row) use ($fieldKeys, $titleMap) {
                    $newRow = [];
                    foreach ($fieldKeys as $prop) {
                        $title = $titleMap[$prop] ?? $prop;
                        if (array_key_exists($title, $row)) {
                            $newRow[$title] = $row[$title];
                        }
                    }
                    return $newRow;
                }, $exportData);
            }

            $filename = 'contracts_' . date('Ymd_His') . '.xlsx';
            return (new FastExcel($exportData))->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同工作流状态
     * 
     * @param int $id 合同ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "id": 1,
     *     "workflow_id": 1,
     *     "entity_type": "contract",
     *     "entity_id": 1,
     *     "title": "合同审批",
     *     "status": "pending",
     *     "current_step": 1,
     *     "workflow": {
     *       "id": 1,
     *       "name": "合同审批流程"
     *     },
     *     "processes": [
     *       {
     *         "id": 1,
     *         "step": 1,
     *         "status": "pending",
     *         "assignee": {
     *           "id": 1,
     *           "name": "张三"
     *         }
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": null
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "获取工作流状态失败：具体错误信息"
     * }
     */
    public function workflowStatus($id)
    {
        try {
            // 查找指定ID的合同记录，预加载工作流实例及相关数据
            $contract = Contract::with(['workflowInstance.workflow', 'workflowInstance.processes.assignee', 'workflowInstance.processes.processor'])
                ->findOrFail($id);

            // 获取合同的工作流实例
            $workflowInstance = $contract->workflowInstance;

            // 如果没有工作流实例，返回空数据
            if (!$workflowInstance) {
                return response()->json([
                    'success' => true,
                    'message' => '获取成功',
                    'data' => null
                ]);
            }

            // 返回工作流实例数据
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $workflowInstance
            ]);

        } catch (\Exception $e) {
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '获取工作流状态失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动合同工作流（带处理人选择）
     * 
     * @param int $id 合同ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "工作流启动成功",
     *   "data": {
     *     "id": 1,
     *     "workflow_id": 1,
     *     "entity_type": "contract",
     *     "entity_id": 1,
     *     "title": "合同审批",
     *     "status": "pending",
     *     "current_step": 1
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "该合同已有进行中的工作流"
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "未找到合同工作流配置"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "启动工作流失败：具体错误信息"
     * }
     */
    public function startWorkflow($id)
    {
        try {
            // 查找指定ID的合同记录，如果不存在则抛出404异常
            $contract = Contract::findOrFail($id);

            // 检查是否已有进行中的工作流
            if ($contract->hasPendingWorkflow()) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同已有进行中的工作流'
                ], 400);
            }

            // 获取合同工作流配置（查找启用状态的合同类型工作流）
            $contractWorkflow = Workflow::where('case_type', '合同')
                ->where('status', 1)
                ->first();

            // 如果没有找到工作流配置，返回错误
            if (!$contractWorkflow) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到合同工作流配置'
                ], 400);
            }

            // 调用工作流服务启动工作流
            $instance = $this->workflowService->startWorkflow(
                'contract', // 实体类型
                $contract->id, // 实体ID
                $contract->contract_name, // 工作流标题
                $contractWorkflow->id, // 工作流配置ID
                auth()->id() // 启动人ID
            );

            // 返回成功响应，包含工作流实例信息
            return response()->json([
                'success' => true,
                'message' => '工作流启动成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            // 返回错误响应，包含具体错误信息
            return response()->json([
                'success' => false,
                'message' => '启动工作流失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动合同工作流（带用户选择的处理人）
     * 
     * @param Request $request 请求对象
     * @param int $id 合同ID
     * 
     * @bodyParam workflow_id int required 工作流配置ID
     * @bodyParam selected_assignees array required 选择的处理人ID数组
     * @bodyParam selected_assignees.* int 处理人用户ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "工作流启动成功",
     *   "data": {
     *     "id": 1,
     *     "workflow_id": 1,
     *     "entity_type": "contract",
     *     "entity_id": 1,
     *     "title": "合同审批",
     *     "status": "pending",
     *     "current_step": 1
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "参数错误",
     *   "errors": {
     *     "workflow_id": ["工作流配置ID字段是必需的"],
     *     "selected_assignees": ["选择的处理人字段是必需的"]
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "该合同已有进行中的工作流"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "工作流启动失败：具体错误信息"
     * }
     */
    public function startWorkflowWithAssignees(Request $request, $id)
    {
        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'workflow_id' => 'required|integer|exists:workflows,id',
            'selected_assignees' => 'required|array',
            'selected_assignees.*' => 'integer|exists:users,id'
        ]);

        // 验证失败时返回错误响应
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数错误',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // 查找指定的合同
            $contract = Contract::findOrFail($id);

            // 检查是否已有进行中的工作流
            if ($contract->hasPendingWorkflow()) {
                return response()->json([
                    'success' => false,
                    'message' => '该合同已有进行中的工作流'
                ], 400);
            }

            // 启动带指定处理人的工作流
            $instance = $this->workflowService->startWorkflowWithAssignees(
                'contract',
                $contract->id,
                $contract->contract_name,
                $request->workflow_id,
                auth()->id(),
                $request->selected_assignees
            );

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '工作流启动成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '工作流启动失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 重新发起合同工作流（用于退回到最初状态后的重新发起）
     * 
     * @param int $id 合同ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "工作流重新发起成功",
     *   "data": {
     *     "id": 1,
     *     "workflow_id": 1,
     *     "entity_type": "contract",
     *     "entity_id": 1,
     *     "title": "合同审批",
     *     "status": "pending",
     *     "current_step": 1
     *   }
     * }
     * 
     * @response 403 {
     *   "success": false,
     *   "message": "只有合同创建人可以重新发起审批"
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "未找到合同工作流配置"
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "重新发起工作流失败：具体错误信息"
     * }
     */
    public function restartWorkflow($id)
    {
        try {
            // 查找指定的合同
            $contract = Contract::findOrFail($id);

            // 检查权限：只有创建人可以重新发起
            if ($contract->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => '只有合同创建人可以重新发起审批'
                ], 403);
            }

            // 检查当前工作流状态
            $currentInstance = $contract->workflowInstance()
                ->whereIn('status', [WorkflowInstance::STATUS_PENDING, WorkflowInstance::STATUS_REJECTED])
                ->first();

            if ($currentInstance) {
                // 取消现有工作流
                $currentInstance->update(['status' => WorkflowInstance::STATUS_CANCELLED]);
                Log::info('重新发起前取消现有工作流', [
                    'contract_id' => $contract->id,
                    'old_instance_id' => $currentInstance->id,
                    'user_id' => auth()->id()
                ]);
            }

            // 获取合同工作流配置
            $contractWorkflow = Workflow::where('case_type', '合同')
                ->where('status', 1)
                ->first();

            if (!$contractWorkflow) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到合同工作流配置'
                ], 400);
            }

            // 启动新的工作流
            $instance = $this->workflowService->startWorkflow(
                'contract',
                $contract->id,
                $contract->contract_name,
                $contractWorkflow->id,
                auth()->id()
            );

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '工作流重新发起成功',
                'data' => $instance
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '重新发起工作流失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取所有合同的审核进度（管理员视角）
     * 
     * @param Request $request 请求对象
     * 
     * @queryParam contract_name string 合同名称（模糊搜索）
     * @queryParam contract_type string 合同类型
     * @queryParam customer_name string 客户名称（模糊搜索）
     * @queryParam workflow_status string 工作流状态（pending/completed/rejected/cancelled）
     * @queryParam created_by string 创建人（模糊搜索真实姓名或用户名）
     * @queryParam page_size int 每页数量，默认20
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "contract_name": "服务合同",
     *         "contract_type": "服务合同",
     *         "contract_status": "待审核",
     *         "customer_name": "客户公司",
     *         "total_amount": "100,000.00",
     *         "created_by": "张三",
     *         "created_at": "2024-01-01 10:00:00",
     *         "workflow_name": "合同审批流程",
     *         "workflow_status": "进行中",
     *         "current_node": "部门经理审批",
     *         "current_handler": "李四",
     *         "last_update_time": "2024-01-02 14:30:00",
     *         "stop_days": 3
     *       }
     *     ],
     *     "per_page": 20,
     *     "total": 100
     *   }
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "获取合同进度失败：具体错误信息"
     * }
     */
    public function getContractProgress(Request $request)
    {
        try {
            $query = Contract::with(['creator', 'customer', 'workflowInstance.workflow', 'workflowInstance.processes.assignee', 'workflowInstance.processes.processor'])
                ->select(['id', 'contract_name', 'contract_type', 'customer_id', 'total_amount', 'created_by', 'created_at', 'status']);

            // 搜索条件
            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%');
            }

            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('workflow_status')) {
                $query->whereHas('workflowInstance', function ($q) use ($request) {
                    $q->where('status', $request->workflow_status);
                });
            }

            if ($request->filled('created_by')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('real_name', 'like', '%' . $request->created_by . '%')
                      ->orWhere('username', 'like', '%' . $request->created_by . '%');
                });
            }

            // 分页
            $pageSize = $request->get('page_size', 20);
            $contracts = $query->orderBy('created_at', 'desc')->paginate($pageSize);

            // 格式化数据
            $formattedData = $contracts->getCollection()->map(function ($contract) {
                $workflowInstance = $contract->workflowInstance;
                $currentProcess = null;
                $currentHandler = null;
                $lastUpdateTime = null;
                $stopDays = 0;

                if ($workflowInstance) {
                    // 获取当前处理节点信息
                    $currentProcess = $workflowInstance->processes
                        ->where('node_index', $workflowInstance->current_node_index)
                        ->where('action', 'pending')
                        ->first();

                    if ($currentProcess) {
                        $currentHandler = $currentProcess->assignee ? 
                            ($currentProcess->assignee->real_name ?: $currentProcess->assignee->username) : '待分配';
                    }

                    // 计算最后更新时间和停滞天数
                    $lastProcessedTask = $workflowInstance->processes
                        ->whereNotNull('processed_at')
                        ->sortByDesc('processed_at')
                        ->first();

                    if ($lastProcessedTask) {
                        $lastUpdateTime = $lastProcessedTask->processed_at;
                        $stopDays = now()->diffInDays(Carbon::parse($lastProcessedTask->processed_at));
                    } else {
                        $lastUpdateTime = $workflowInstance->created_at;
                        $stopDays = now()->diffInDays(Carbon::parse($workflowInstance->created_at));
                    }
                }

                return [
                    'id' => $contract->id,
                    'contract_name' => $contract->contract_name,
                    'contract_type' => $contract->contract_type,
                    'contract_status' => $this->getContractStatusText($contract->status),
                    'customer_name' => $contract->customer ? $contract->customer->customer_name : '',
                    'total_amount' => $contract->total_amount ? number_format($contract->total_amount, 2) : '0.00',
                    'created_by' => $contract->creator ? ($contract->creator->real_name ?: $contract->creator->username) : '',
                    'created_at' => $contract->created_at ? 
                        Carbon::parse($contract->created_at)->format('Y-m-d H:i:s') : '',
                    
                    // 工作流相关信息
                    'workflow_name' => $workflowInstance ? ($workflowInstance->workflow->name ?? '合同审批流程') : '未发起',
                    'workflow_status' => $workflowInstance ? $this->getWorkflowStatusText($workflowInstance->status) : '未发起',
                    'current_node' => $currentProcess ? $currentProcess->node_name : ($workflowInstance ? '已完成' : '未发起'),
                    'current_handler' => $currentHandler ?: '无',
                    'last_update_time' => $lastUpdateTime ? 
                        Carbon::parse($lastUpdateTime)->format('Y-m-d H:i:s') : '',
                    'stop_days' => $stopDays,
                    
                    // 工作流实例数据（用于详细展示）
                    'workflow_instance' => $workflowInstance
                ];
            });

            $contracts->setCollection($formattedData);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contracts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取合同进度失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同状态文本
     */
    private function getContractStatusText($status)
    {
        $statusMap = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已审核',
            'signed' => '已签订',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取工作流状态文本
     */
    private function getWorkflowStatusText($status)
    {
        $statusMap = [
            'pending' => '进行中',
            'completed' => '已完成',
            'rejected' => '已驳回',
            'cancelled' => '已取消'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取合同工作流状态
     * 
     * @param int $id 合同ID
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "workflow_status": {
     *       "instance_id": 1,
     *       "status": "pending",
     *       "current_step": 2,
     *       "total_steps": 5
     *     },
     *     "workflow_instance": {
     *       "id": 1,
     *       "workflow_id": 1,
     *       "title": "合同审批",
     *       "status": "pending",
     *       "current_step": 2,
     *       "processes": []
     *     }
     *   }
     * }
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "该合同暂无工作流",
     *   "data": null
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "合同不存在"
     * }
     * 
     * @response 500 {
     *   "success": false,
     *   "message": "获取失败：具体错误信息"
     * }
     */
    public function getWorkflowStatus($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $workflowStatus = $contract->getWorkflowStatus();

            if (!$workflowStatus) {
                return response()->json([
                    'success' => true,
                    'message' => '该合同暂无工作流',
                    'data' => null
                ]);
            }

            // 获取工作流详细信息
            $instance = \App\Models\WorkflowInstance::with(['workflow', 'processes.assignee', 'processes.processor'])
                ->find($workflowStatus['instance_id']);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'workflow_status' => $workflowStatus,
                    'workflow_instance' => $instance
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
     * 获取当前用户的待处理合同数量
     */
    public function getPendingCount()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未登录'
                ], 401);
            }

            // 查询当前用户有待处理任务的合同数量
            $count = Contract::whereHas('workflowInstance', function ($q) use ($user) {
                $q->where('status', \App\Models\WorkflowInstance::STATUS_PENDING)
                  ->whereHas('processes', function ($processQuery) use ($user) {
                      $processQuery->where('assignee_id', $user->id)
                                  ->where('action', \App\Models\WorkflowProcess::ACTION_PENDING);
                  });
            })->count();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }
}
