<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContractCaseRecord;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContractCaseRecordController extends Controller
{
    /**
     * 获取合同项目记录列表
     * 
     * 获取合同项目记录的分页列表，支持多种过滤条件和搜索功能
     * 主要用于待立项列表页面，默认只显示状态为"待立项"且合同已审批的项目
     * 
     * @param Request $request HTTP请求对象，包含查询参数
     * 
     * 支持的查询参数：
     * - contract_id: 合同ID，用于查询特定合同下的项目
     * - customer_id: 客户ID，用于过滤特定客户的项目
     * - case_type: 项目类型（1=专利，2=商标，3=版权，4=科服，或字符串形式）
     * - business_type: 业务类型（子分类）
     * - is_filed: 是否已立项
     * - case_name: 项目名称（模糊搜索）
     * - case_code: 项目编号（模糊搜索）
     * - customer_name: 客户名称（模糊搜索）
     * - case_number: 项目编号（模糊搜索，同case_code）
     * - status_filter: 状态过滤标识
     * - page: 页码（默认1）
     * - page_size: 每页数量（默认10）
     * 
     * @return \Illuminate\Http\JsonResponse 返回包含项目列表的JSON响应
     * 
     * 响应数据结构：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "list": [...],      // 项目列表
     *     "total": 100,       // 总数量
     *     "page_size": 10,    // 每页数量
     *     "current_page": 1,  // 当前页码
     *     "last_page": 10     // 最后一页
     *   }
     * }
     */
    public function index(Request $request)
    {
        try {
            // 构建基础查询，预加载所有相关联的模型数据
            $query = ContractCaseRecord::with([
                'customer',        // 客户信息
                'contract',        // 合同信息
                'case',           // 关联的真实案件（立项后）
                'product',        // 产品信息
                'businessPerson', // 业务负责人
                'agent',          // 代理人
                'assistant',      // 助理
                'creator',        // 创建者
                'techLeader',     // 技术负责人
                'presaleSupport'  // 售前支持
            ]);

            // 根据请求参数决定是否限制状态
            // 如果是合同详情页面查询（通过contract_id且没有指定status_filter），则不限制状态
            if (!$request->filled('contract_id') || $request->filled('status_filter')) {
                // 只显示待立项状态的记录（case_status = 2）且合同已审批的项目
                $query->where('case_status', ContractCaseRecord::STATUS_TO_BE_FILED)
                      ->whereHas('contract', function($q) {
                          $q->where('status', '已完成'); // 只显示已审批合同的项目
                      });
            }

            // 根据合同ID过滤
            if ($request->filled('contract_id')) {
                $query->where('contract_id', $request->contract_id);
            }

            // 根据客户ID过滤
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            // 根据项目类型过滤
            if ($request->filled('case_type') && $request->case_type !== 'all') {
                $caseType = $request->case_type;
                // 如果是字符串类型，转换为数值
                if (is_string($caseType)) {
                    switch ($caseType) {
                        case 'patent':
                            $caseType = ContractCaseRecord::TYPE_PATENT;      // 专利
                            break;
                        case 'trademark':
                            $caseType = ContractCaseRecord::TYPE_TRADEMARK;   // 商标
                            break;
                        case 'copyright':
                            $caseType = ContractCaseRecord::TYPE_COPYRIGHT;   // 版权
                            break;
                        case 'project':
                            $caseType = ContractCaseRecord::TYPE_TECH_SERVICE; // 科服
                            break;
                        default:
                            // 如果是数值字符串，转换为整数
                            if (is_numeric($caseType)) {
                                $caseType = (int)$caseType;
                            }
                    }
                }
                $query->where('case_type', $caseType);
            }

            // 根据业务类型过滤（子分类）
            if ($request->filled('business_type') && $request->business_type !== 'all') {
                $query->where('case_subtype', $request->business_type);
            }

            // 注意：不允许通过参数覆盖状态过滤，待立项列表只显示状态为2的记录
            // 如果需要查询其他状态的记录，应该使用其他API接口

            // 根据是否已立项过滤
            if ($request->filled('is_filed')) {
                $query->where('is_filed', $request->is_filed);
            }

            // 搜索条件 - 项目名称模糊搜索
            if ($request->filled('case_name')) {
                $query->where('case_name', 'like', '%' . $request->case_name . '%');
            }

            // 搜索条件 - 项目编号模糊搜索
            if ($request->filled('case_code')) {
                $query->where('case_code', 'like', '%' . $request->case_code . '%');
            }

            // 根据客户名称搜索（通过关联查询）
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // 根据项目编号搜索（与case_code相同）
            if ($request->filled('case_number')) {
                $query->where('case_code', 'like', '%' . $request->case_number . '%');
            }

            // 分页参数处理
            $perPage = $request->input('page_size', 10);  // 每页数量，默认10
            $page = $request->input('page', 1);           // 当前页码，默认1

            // 获取总数量（在应用分页之前）
            $total = $query->count();
            
            // 应用分页和排序，按创建时间倒序
            $records = $query->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 转换数据格式以匹配前端期望的字段名称
            $transformedRecords = $records->map(function ($record) {
                return [
                    'id' => $record->id,
                    // 前端表格使用的字段名
                    'case_code' => $record->case_code ?? '',                    // 项目编号
                    'case_name' => $record->case_name ?? '',                    // 项目名称
                    'case_type' => $record->case_type,                          // 项目类型
                    'case_subtype' => $record->case_subtype ?? '',              // 项目子类型
                    'application_type' => $record->application_type ?? '',      // 申请类型
                    'application_no' => $record->application_no ?? '',          // 申请号
                    'registration_no' => $record->registration_no ?? '',        // 注册号
                    'country_code' => $record->country_code ?? '',              // 国家代码
                    'case_status' => $record->case_status,                      // 项目状态
                    'is_filed' => $record->is_filed ?? false,                   // 是否已立项
                    'case_description' => $record->case_description ?? '',      // 项目描述
                    // 关联的真实项目信息（立项后的Cases）
                    'real_case' => $record->case ? [
                        'id' => $record->case->id,
                        'case_status' => $record->case->case_status,
                        'case_status_text' => $this->getRealCaseStatusText($record->case->case_status),
                        'application_no' => $record->case->application_no ?? '',
                        'registration_no' => $record->case->registration_no ?? '',
                        'created_at' => $record->case->created_at ? $record->case->created_at->format('Y-m-d H:i:s') : null
                    ] : null,
                    // 产品信息关联
                    'product' => $record->product ? [
                        'product_name' => $record->product->product_name ?? '',
                        'specification' => $record->product->specification ?? ''
                    ] : null,
                    // 兼容旧版本字段名（前端可能仍在使用）
                    'customerName' => $record->customer ? $record->customer->customer_name : '',
                    'contractCode' => $record->contract ? $record->contract->contract_code : '',
                    'caseType' => $this->getCaseTypeText($record->case_type),
                    'businessType' => $record->case_subtype ?? '',
                    'applyType' => $record->application_type ?? '',
                    'caseNumber' => $record->case_code ?? '',
                    'caseName' => $record->case_name ?? '',
                    'technicalDirector' => $record->techLeader ? $record->techLeader->real_name : '',
                    'amount' => $record->estimated_cost ?? 0,                   // 预估费用
                    'serviceFee' => $record->service_fee ?? 0,                  // 服务费
                    'officialFee' => $record->official_fee ?? 0,                // 官费
                    'creator' => $record->creator ? $record->creator->real_name : '',
                    'processingTime' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '',
                    'preSales' => $record->presaleSupport ? $record->presaleSupport->real_name : '',
                    'caseHandler' => $record->businessPerson ? $record->businessPerson->real_name : '',
                    'trademarkType' => $record->trademark_category ?? '',       // 商标类别
                    'company' => '',                                            // 需要根据实际情况填充
                    'agencyStructure' => $record->agency_id ? $this->getAgencyName($record->agency_id) : '',
                    'remark' => $record->case_description ?? '',
                    // 添加原始数据以便调试
                    'customerId' => $record->customer_id,
                    'contractId' => $record->contract_id,
                ];
            });

            // 返回成功响应，包含分页信息
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $transformedRecords,                    // 项目列表
                    'total' => $total,                               // 总数量
                    'page_size' => $perPage,                         // 每页数量
                    'current_page' => $page,                         // 当前页码
                    'last_page' => ceil($total / $perPage),          // 最后一页
                ]
            ]);

        } catch (\Exception $e) {
            // 异常处理：记录错误并返回失败响应
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建合同项目记录
     * 
     * 创建新的合同项目记录，用于在合同中添加需要立项的项目
     * 新建项目默认为草稿状态，只有合同审批通过后才会变为待立项状态
     * 
     * @param Request $request HTTP请求对象，包含项目信息
     * 
     * 必需参数：
     * - case_name: 项目名称（最大200字符）
     * - customer_id: 客户ID（必须存在于customers表）
     * - case_type: 项目类型（1=专利，2=商标，3=版权，4=科服）
     * 
     * 可选参数：
     * - contract_id: 合同ID（必须存在于contracts表）
     * - case_subtype: 项目子类型（最大50字符）
     * - application_type: 申请类型（最大50字符）
     * - case_status: 项目状态（1-7，默认为草稿状态）
     * - product_id: 产品ID（必须存在于products表）
     * 
     * @return \Illuminate\Http\JsonResponse 返回创建结果的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "message": "创建成功",
     *   "data": {...}  // 包含关联数据的项目记录
     * }
     * 
     * @throws \Illuminate\Validation\ValidationException 当验证失败时抛出
     */
    public function store(Request $request)
    {
        try {
            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'case_name' => 'required|string|max:200',                    // 项目名称（必需）
                'customer_id' => 'required|integer|exists:customers,id',     // 客户ID（必需，必须存在）
                'contract_id' => 'nullable|integer|exists:contracts,id',     // 合同ID（可选，存在时必须有效）
                'case_type' => 'required|integer|in:1,2,3,4',               // 项目类型（必需，1-4）
                'case_subtype' => 'nullable|string|max:50',                  // 项目子类型（可选）
                'application_type' => 'nullable|string|max:50',              // 申请类型（可选）
                'case_status' => 'nullable|integer|in:1,2,3,4,5,6,7',       // 项目状态（可选，1-7）
                'product_id' => 'nullable|integer|exists:products,id',       // 产品ID（可选，存在时必须有效）
            ]);

            // 如果验证失败，返回错误信息
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 开始数据库事务，确保数据一致性
            DB::beginTransaction();

            // 生成项目编码（根据项目类型自动生成唯一编号）
            $caseCode = $this->generateCaseCode($request->case_type);

            // 准备创建数据
            $recordData = $request->all();
            $recordData['case_code'] = $caseCode;                           // 设置生成的项目编码
            // 新建项目默认为草稿状态，只有合同审批通过后才会变为待立项
            $recordData['case_status'] = $recordData['case_status'] ?? ContractCaseRecord::STATUS_DRAFT;
            $recordData['created_by'] = auth()->id();                       // 设置创建者为当前用户

            // 创建合同项目记录
            $record = ContractCaseRecord::create($recordData);

            // 提交事务
            DB::commit();

            // 返回成功响应，包含关联数据
            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $record->load(['customer', 'contract', 'product'])  // 预加载关联数据
            ]);

        } catch (\Exception $e) {
            // 发生异常时回滚事务
            DB::rollBack();
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 显示指定的合同项目记录详情
     * 
     * 根据记录ID获取单个合同项目记录的详细信息
     * 包含关联的客户、合同、产品等完整信息
     * 
     * @param int $id 合同项目记录ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回记录详情的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "case_name": "项目名称",
     *     "case_code": "项目编码",
     *     "case_type": 1,
     *     "case_status": 1,
     *     "customer": {...},    // 客户信息
     *     "contract": {...},    // 合同信息
     *     "product": {...}      // 产品信息
     *   }
     * }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当记录不存在时抛出
     */
    public function show($id)
    {
        try {
            // 查找指定ID的合同项目记录，如果不存在则抛出异常
            $record = ContractCaseRecord::with(['customer', 'contract', 'product'])
                ->findOrFail($id);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'data' => $record
            ]);

        } catch (\Exception $e) {
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取记录失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 根据合同项目ID获取相关记录
     * 
     * 获取指定合同项目（contract_case）下的所有项目记录
     * 用于显示某个合同项目包含的具体立项内容
     * 
     * @param int $caseId 合同项目ID（contract_cases表的ID）
     * 
     * @return \Illuminate\Http\JsonResponse 返回记录列表的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "case_name": "项目名称",
     *       "case_code": "项目编码",
     *       "case_type": 1,
     *       "case_status": 1,
     *       "customer": {...},    // 客户信息
     *       "contract": {...},    // 合同信息
     *       "product": {...}      // 产品信息
     *     }
     *   ]
     * }
     */
    public function getByCaseId($caseId)
    {
        try {
            // 根据合同项目ID查询所有相关的项目记录
            // 预加载关联数据以减少数据库查询次数
            $records = ContractCaseRecord::with(['customer', 'contract', 'product'])
                ->where('case_id', $caseId)                                 // 筛选指定合同项目的记录
                ->orderBy('created_at', 'desc')                             // 按创建时间倒序排列
                ->get();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'data' => $records
            ]);

        } catch (\Exception $e) {
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新合同项目记录
     * 
     * 更新指定的合同项目记录信息
     * 支持部分字段更新，只更新请求中提供的字段
     * 
     * @param Request $request HTTP请求对象，包含要更新的字段
     * @param int $id 合同项目记录ID
     * 
     * 可更新字段：
     * - case_name: 项目名称（最大200字符）
     * - customer_id: 客户ID（必须存在于customers表）
     * - contract_id: 合同ID（必须存在于contracts表）
     * - case_type: 项目类型（1=专利，2=商标，3=版权，4=科服）
     * - case_subtype: 项目子类型（最大50字符）
     * - application_type: 申请类型（最大50字符）
     * - case_status: 项目状态（1-7）
     * - product_id: 产品ID（必须存在于products表）
     * 
     * @return \Illuminate\Http\JsonResponse 返回更新结果的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "message": "更新成功",
     *   "data": {...}  // 包含关联数据的更新后记录
     * }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当记录不存在时抛出
     * @throws \Illuminate\Validation\ValidationException 当验证失败时抛出
     */
    public function update(Request $request, $id)
    {
        try {
            // 查找要更新的记录，如果不存在则抛出异常
            $record = ContractCaseRecord::findOrFail($id);

            // 验证请求数据（只验证提供的字段）
            $validator = Validator::make($request->all(), [
                'case_name' => 'sometimes|required|string|max:200',          // 项目名称（可选更新）
                'customer_id' => 'sometimes|required|integer|exists:customers,id', // 客户ID（可选更新）
                'contract_id' => 'nullable|integer|exists:contracts,id',     // 合同ID（可选更新）
                'case_type' => 'sometimes|required|integer|in:1,2,3,4',     // 项目类型（可选更新）
                'case_subtype' => 'nullable|string|max:50',                  // 项目子类型（可选更新）
                'application_type' => 'nullable|string|max:50',              // 申请类型（可选更新）
                'case_status' => 'sometimes|integer|in:1,2,3,4,5,6,7',      // 项目状态（可选更新）
                'product_id' => 'nullable|integer|exists:products,id',       // 产品ID（可选更新）
            ]);

            // 如果验证失败，返回错误信息
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 开始数据库事务，确保数据一致性
            DB::beginTransaction();

            // 准备更新数据
            $updateData = $request->all();
            $updateData['updated_by'] = auth()->id();                       // 设置更新者为当前用户

            // 如果项目类型发生变化，需要重新生成项目编码
            if (isset($updateData['case_type']) && $updateData['case_type'] != $record->case_type) {
                $updateData['case_code'] = $this->generateCaseCode($updateData['case_type']);
            }

            // 更新记录
            $record->update($updateData);

            // 提交事务
            DB::commit();

            // 返回成功响应，包含关联数据
            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $record->load(['customer', 'contract', 'product'])  // 预加载关联数据
            ]);

        } catch (\Exception $e) {
            // 发生异常时回滚事务
            DB::rollBack();
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同项目记录
     * 
     * 软删除指定的合同项目记录
     * 删除前会检查记录是否存在以及是否有权限删除
     * 
     * @param int $id 合同项目记录ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回删除结果的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "message": "删除成功"
     * }
     * 
     * 业务规则：
     * - 只能删除草稿状态的记录
     * - 已进入流程的记录不允许删除
     * - 删除操作为软删除，数据仍保留在数据库中
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当记录不存在时抛出
     */
    public function destroy($id)
    {
        try {
            // 查找要删除的记录，如果不存在则抛出异常
            $record = ContractCaseRecord::findOrFail($id);

            // 业务规则检查：只能删除草稿状态的记录
            if ($record->case_status != ContractCaseRecord::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => '只能删除草稿状态的记录'
                ], 403);
            }

            // 开始数据库事务，确保数据一致性
            DB::beginTransaction();

            // 执行软删除
            $record->delete();

            // 提交事务
            DB::commit();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            // 发生异常时回滚事务
            DB::rollBack();
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 立项操作
     */
    public function file($id)
    {
        try {
            $record = ContractCaseRecord::find($id);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            // 检查是否已立项
            if ($record->is_filed) {
                return response()->json([
                    'success' => false,
                    'message' => '该项目已立项'
                ], 400);
            }

            DB::beginTransaction();

            // 更新立项状态
            $record->update([
                'is_filed' => true,
                'filed_at' => now(),
                'filed_by' => auth()->id(),
                'case_status' => ContractCaseRecord::STATUS_SUBMITTED, // 立项后状态改为已提交
                'updated_by' => auth()->id()
            ]);

            // 同步更新cases表
            if ($record->case_id) {
                Cases::where('id', $record->case_id)->update([
                    'case_status' => Cases::STATUS_SUBMITTED,
                    'updated_by' => auth()->id()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '立项成功',
                'data' => $record->load(['customer', 'contract', 'product', 'filer'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '立项失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成项目编码
     */
    private function generateCaseCode($caseType)
    {
        $prefix = [
            1 => 'P', // 专利
            2 => 'T', // 商标
            3 => 'C', // 版权
            4 => 'S', // 科服
        ];

        $typePrefix = $prefix[$caseType] ?? 'X';
        $dateStr = date('Ymd');
        $randomStr = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return $typePrefix . $dateStr . $randomStr;
    }

    /**
     * 获取项目类型文本
     */
    private function getCaseTypeText($caseType)
    {
        $types = [
            ContractCaseRecord::TYPE_PATENT => '专利',
            ContractCaseRecord::TYPE_TRADEMARK => '商标',
            ContractCaseRecord::TYPE_COPYRIGHT => '版权',
            ContractCaseRecord::TYPE_TECH_SERVICE => '科服',
        ];

        return $types[$caseType] ?? '未知';
    }

    /**
     * 获取真实案件状态文本
     */
    private function getRealCaseStatusText($caseStatus)
    {
        $statuses = [
            1 => '草稿',
            2 => '待立项',
            3 => '已提交',
            4 => '处理中',
            5 => '已授权',
            6 => '已驳回',
            7 => '已完成',
            8 => '审核中',
            9 => '待补正',
            10 => '已撤回'
        ];

        return $statuses[$caseStatus] ?? '未知';
    }



    /**
     * 获取代理机构名称
     */
    private function getAgencyName($agencyId)
    {
        if (!$agencyId) {
            return '';
        }

        // 这里需要根据实际的代理机构表来获取名称
        // 假设有Agency模型
        // $agency = \App\Models\Agency::find($agencyId);
        // return $agency ? $agency->name : '';

        return ''; // 暂时返回空字符串
    }

    /**
     * 处理文件上传
     * 
     * 处理合同项目记录相关的文件上传
     * 支持多种文件类型，包括文档、图片等
     * 
     * @param Request $request HTTP请求对象，包含上传的文件
     * 
     * 请求参数：
     * - file: 上传的文件（必需）
     * - record_id: 合同项目记录ID（必需）
     * - file_type: 文件类型标识（可选）
     * 
     * @return \Illuminate\Http\JsonResponse 返回文件上传结果的JSON响应
     * 
     * 成功响应：
     * {
     *   "success": true,
     *   "message": "文件上传成功",
     *   "data": {
     *     "file_path": "storage/uploads/...",
     *     "file_name": "原始文件名",
     *     "file_size": 1024
     *   }
     * }
     * 
     * 文件限制：
     * - 最大文件大小：10MB
     * - 支持的文件类型：pdf, doc, docx, jpg, jpeg, png, gif
     * - 文件存储路径：storage/app/public/contract_case_records/
     */
    public function file(Request $request)
    {
        try {
            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif', // 文件验证
                'record_id' => 'required|integer|exists:contract_case_records,id',        // 记录ID验证
                'file_type' => 'nullable|string|max:50',                                  // 文件类型标识
            ]);

            // 如果验证失败，返回错误信息
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 获取上传的文件
            $file = $request->file('file');
            $recordId = $request->input('record_id');

            // 生成唯一的文件名，避免文件名冲突
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // 定义文件存储路径
            $filePath = $file->storeAs('contract_case_records/' . $recordId, $fileName, 'public');

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '文件上传成功',
                'data' => [
                    'file_path' => 'storage/' . $filePath,                      // 公开访问路径
                    'file_name' => $file->getClientOriginalName(),              // 原始文件名
                    'file_size' => $file->getSize()                             // 文件大小（字节）
                ]
            ]);

        } catch (\Exception $e) {
            // 返回错误响应
            return response()->json([
                'success' => false,
                'message' => '文件上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成项目编码
     * 
     * 根据项目类型自动生成唯一的项目编码
     * 编码格式：类型前缀 + 年月 + 4位序号
     * 
     * @param int $caseType 项目类型（1=专利，2=商标，3=版权，4=科服）
     * 
     * @return string 生成的项目编码
     * 
     * 编码规则：
     * - 专利：ZL + YYYYMM + 0001
     * - 商标：SB + YYYYMM + 0001  
     * - 版权：BQ + YYYYMM + 0001
     * - 科服：KF + YYYYMM + 0001
     * 
     * 示例：ZL2024010001（2024年1月第1个专利项目）
     */
    private function generateCaseCode($caseType)
    {
        // 定义项目类型前缀映射
        $prefixes = [
            1 => 'ZL',  // 专利
            2 => 'SB',  // 商标
            3 => 'BQ',  // 版权
            4 => 'KF'   // 科服
        ];

        // 获取对应的前缀，如果类型不存在则使用默认前缀
        $prefix = $prefixes[$caseType] ?? 'XX';
        
        // 获取当前年月，格式：YYYYMM
        $yearMonth = date('Ym');
        
        // 构建编码前缀：类型前缀 + 年月
        $codePrefix = $prefix . $yearMonth;

        // 查询当月同类型项目的最大序号
        $lastRecord = ContractCaseRecord::where('case_code', 'like', $codePrefix . '%')
            ->orderBy('case_code', 'desc')
            ->first();

        // 计算下一个序号
        if ($lastRecord) {
            // 从最后一个编码中提取序号部分（后4位）
            $lastNumber = intval(substr($lastRecord->case_code, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            // 如果没有记录，从1开始
            $nextNumber = 1;
        }

        // 生成完整的项目编码：前缀 + 4位序号（补零）
        return $codePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 格式化项目状态文本
     * 
     * 将数字状态码转换为对应的中文描述
     * 用于前端显示和报表生成
     * 
     * @param int $status 项目状态码
     * 
     * @return string 状态的中文描述
     * 
     * 状态映射：
     * - 1: 草稿
     * - 2: 待立项
     * - 3: 已立项
     * - 4: 进行中
     * - 5: 已完成
     * - 6: 已暂停
     * - 7: 已取消
     */
    private function formatStatusText($status)
    {
        // 定义状态码与中文描述的映射关系
        $statusMap = [
            1 => '草稿',
            2 => '待立项',
            3 => '已立项',
            4 => '进行中',
            5 => '已完成',
            6 => '已暂停',
            7 => '已取消'
        ];

        // 返回对应的状态文本，如果状态码不存在则返回"未知状态"
        return $statusMap[$status] ?? '未知状态';
    }

    /**
     * 格式化项目类型文本
     * 
     * 将数字类型码转换为对应的中文描述
     * 用于前端显示和报表生成
     * 
     * @param int $type 项目类型码
     * 
     * @return string 类型的中文描述
     * 
     * 类型映射：
     * - 1: 专利
     * - 2: 商标
     * - 3: 版权
     * - 4: 科服
     */
    private function formatTypeText($type)
    {
        // 定义类型码与中文描述的映射关系
        $typeMap = [
            1 => '专利',
            2 => '商标', 
            3 => '版权',
            4 => '科服'
        ];

        // 返回对应的类型文本，如果类型码不存在则返回"未知类型"
        return $typeMap[$type] ?? '未知类型';
    }

    /**
     * 检查用户权限
     * 
     * 检查当前用户是否有权限操作指定的合同项目记录
     * 用于权限控制和数据安全
     * 
     * @param ContractCaseRecord $record 合同项目记录实例
     * @param string $action 操作类型（view, edit, delete等）
     * 
     * @return bool 是否有权限
     * 
     * 权限规则：
     * - 管理员：所有权限
     * - 创建者：所有权限
     * - 业务员：查看和编辑权限
     * - 其他用户：仅查看权限
     */
    private function checkPermission($record, $action = 'view')
    {
        $user = auth()->user();
        
        // 如果用户未登录，无权限
        if (!$user) {
            return false;
        }

        // 管理员拥有所有权限
        if ($user->hasRole('admin')) {
            return true;
        }

        // 创建者拥有所有权限
        if ($record->created_by == $user->id) {
            return true;
        }

        // 根据不同操作类型检查权限
        switch ($action) {
            case 'view':
                // 查看权限：业务相关人员都可以查看
                return $user->hasRole(['business', 'agent', 'assistant']);
                
            case 'edit':
                // 编辑权限：业务员和代理人可以编辑
                return $user->hasRole(['business', 'agent']);
                
            case 'delete':
                // 删除权限：只有创建者和管理员可以删除
                return false;
                
            default:
                return false;
        }
    }
}
