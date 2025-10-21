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
     */
    public function index(Request $request)
    {
        try {
            $query = ContractCaseRecord::with([
                'customer',
                'contract',
                'case',
                'product',
                'businessPerson',
                'agent',
                'assistant',
                'creator',
                'techLeader',
                'presaleSupport'
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
                            $caseType = ContractCaseRecord::TYPE_PATENT;
                            break;
                        case 'trademark':
                            $caseType = ContractCaseRecord::TYPE_TRADEMARK;
                            break;
                        case 'copyright':
                            $caseType = ContractCaseRecord::TYPE_COPYRIGHT;
                            break;
                        case 'project':
                            $caseType = ContractCaseRecord::TYPE_TECH_SERVICE;
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

            // 搜索条件
            if ($request->filled('case_name')) {
                $query->where('case_name', 'like', '%' . $request->case_name . '%');
            }

            if ($request->filled('case_code')) {
                $query->where('case_code', 'like', '%' . $request->case_code . '%');
            }

            // 根据客户名称搜索
            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            // 根据项目编号搜索
            if ($request->filled('case_number')) {
                $query->where('case_code', 'like', '%' . $request->case_number . '%');
            }

            // 分页参数
            $perPage = $request->input('page_size', 10);
            $page = $request->input('page', 1);

            $total = $query->count();
            $records = $query->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 转换数据格式以匹配前端期望的字段名称
            $transformedRecords = $records->map(function ($record) {
                return [
                    'id' => $record->id,
                    // 前端表格使用的字段名
                    'case_code' => $record->case_code ?? '',
                    'case_name' => $record->case_name ?? '',
                    'case_type' => $record->case_type,
                    'case_subtype' => $record->case_subtype ?? '',
                    'application_type' => $record->application_type ?? '',
                    'application_no' => $record->application_no ?? '',
                    'registration_no' => $record->registration_no ?? '',
                    'country_code' => $record->country_code ?? '',
                    'case_status' => $record->case_status,
                    'is_filed' => $record->is_filed ?? false,
                    'case_description' => $record->case_description ?? '',
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
                    // 兼容旧版本字段名
                    'customerName' => $record->customer ? $record->customer->customer_name : '',
                    'contractCode' => $record->contract ? $record->contract->contract_code : '',
                    'caseType' => $this->getCaseTypeText($record->case_type),
                    'businessType' => $record->case_subtype ?? '',
                    'applyType' => $record->application_type ?? '',
                    'caseNumber' => $record->case_code ?? '',
                    'caseName' => $record->case_name ?? '',
                    'technicalDirector' => $record->techLeader ? $record->techLeader->real_name : '',
                    'amount' => $record->estimated_cost ?? 0,
                    'serviceFee' => $record->service_fee ?? 0,
                    'officialFee' => $record->official_fee ?? 0,
                    'creator' => $record->creator ? $record->creator->real_name : '',
                    'processingTime' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '',
                    'preSales' => $record->presaleSupport ? $record->presaleSupport->real_name : '',
                    'caseHandler' => $record->businessPerson ? $record->businessPerson->real_name : '',
                    'trademarkType' => $record->trademark_category ?? '',
                    'company' => '', // 需要根据实际情况填充
                    'agencyStructure' => $record->agency_id ? $this->getAgencyName($record->agency_id) : '',
                    'remark' => $record->case_description ?? '',
                    // 添加原始数据以便调试
                    'customerId' => $record->customer_id,
                    'contractId' => $record->contract_id,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $transformedRecords,
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
     * 创建合同项目记录
     */
    public function store(Request $request)
    {
        try {
            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'case_name' => 'required|string|max:200',
                'customer_id' => 'required|integer|exists:customers,id',
                'contract_id' => 'nullable|integer|exists:contracts,id',
                'case_type' => 'required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'case_status' => 'nullable|integer|in:1,2,3,4,5,6,7',
                'product_id' => 'nullable|integer|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // 生成项目编码
            $caseCode = $this->generateCaseCode($request->case_type);

            // 创建合同项目记录
            $recordData = $request->all();
            $recordData['case_code'] = $caseCode;
            // 新建项目默认为草稿状态，只有合同审批通过后才会变为待立项
            $recordData['case_status'] = $recordData['case_status'] ?? ContractCaseRecord::STATUS_DRAFT;
            $recordData['created_by'] = auth()->id();

            $record = ContractCaseRecord::create($recordData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $record->load(['customer', 'contract', 'product'])
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
     * 获取合同项目记录详情
     */
    public function show($id)
    {
        try {
            $record = ContractCaseRecord::with([
                'customer',
                'contract',
                'case',
                'product',
                'businessPerson',
                'agent',
                'assistant',
                'creator'
            ])->find($id);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 通过case_id获取合同项目记录详情
     */
    public function getByCaseId($caseId)
    {
        try {
            $record = ContractCaseRecord::with([
                'customer',
                'contract',
                'case',
                'product',
                'businessPerson',
                'agent',
                'assistant',
                'creator'
            ])->where('case_id', $caseId)->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到case_id为' . $caseId . '的记录'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新合同项目记录
     */
    public function update(Request $request, $id)
    {
        try {
            $record = ContractCaseRecord::find($id);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            // 检查是否已立项，已立项的不允许编辑
            if ($record->is_filed) {
                return response()->json([
                    'success' => false,
                    'message' => '该项目已立项，不允许编辑'
                ], 403);
            }

            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'case_name' => 'sometimes|required|string|max:200',
                'case_type' => 'sometimes|required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'product_id' => 'nullable|integer|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // 更新记录
            $updateData = $request->all();
            $updateData['updated_by'] = auth()->id();
            $record->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $record->load(['customer', 'contract', 'product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同项目记录
     */
    public function destroy($id)
    {
        try {
            $record = ContractCaseRecord::find($id);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '记录不存在'
                ], 404);
            }

            // 检查是否已立项，已立项的不允许删除
            if ($record->is_filed) {
                return response()->json([
                    'success' => false,
                    'message' => '该项目已立项，不允许删除'
                ], 403);
            }

            DB::beginTransaction();

            // 删除记录
            $record->delete();

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
}
