<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestDetail;
use App\Models\CaseFee;
use App\Models\Customer;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 请款单控制器
 * 
 * 功能描述：负责管理请款单的创建、查询、编辑、提交审核等操作
 * 
 * 主要功能：
 * - 获取请款单列表（支持搜索、筛选和分页）
 * - 获取请款单详情
 * - 创建新的请款单
 * - 更新请款单信息
 * - 删除请款单
 * - 提交请款单审核
 * - 撤回请款单
 * - 审核请款单
 * - 导出请款单数据
 * - 获取请款单统计数据
 */
class PaymentRequestController extends Controller
{
    /**
     * 获取请款单列表 index
     * 
     * 功能描述：获取请款单列表，支持状态筛选、搜索和分页
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - status (int, optional): 状态筛选
     *   - requestNo (string, optional): 请款单号搜索
     *   - customerId (int, optional): 客户ID筛选
     *   - customerName (string, optional): 客户名称搜索
     *   - contractNo (string, optional): 合同号搜索
     *   - creator (string, optional): 创建人搜索
     *   - startDate (string, optional): 创建时间开始范围
     *   - endDate (string, optional): 创建时间结束范围
     *   - minAmount (float, optional): 最小金额筛选
     *   - maxAmount (float, optional): 最大金额筛选
     *   - page (int, optional): 页码，默认为1
     *   - pageSize (int, optional): 每页数量，默认为10
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 分页数据
     *   - list (array): 请款单列表数据
     *     - id (int): ID
     *     - requestNo (string): 请款单号
     *     - customerName (string): 客户名称
     *     - contractNo (string): 合同号
     *     - totalAmount (float): 总金额
     *     - officialFee (float): 官费总额
     *     - serviceFee (float): 服务费总额
     *     - status (int): 状态
     *     - statusText (string): 状态文本
     *     - creator (string): 创建人
     *     - createdAt (string): 创建时间
     *     - remark (string): 备注
     *   - total (int): 总记录数
     *   - page (int): 当前页码
     *   - pageSize (int): 每页数量
     */
    public function index(Request $request)
    {
        try {
            $query = PaymentRequest::with(['customer', 'contract', 'creator', 'details'])
                ->whereNull('deleted_at');

            // 状态筛选
            if ($request->filled('status')) {
                $query->where('request_status', $request->status);
            }

            // 请款单号搜索
            if ($request->filled('requestNo')) {
                $query->where('request_no', 'like', '%' . $request->requestNo . '%');
            }

            // 客户ID筛选
            if ($request->filled('customerId')) {
                $query->where('customer_id', $request->customerId);
            }

            // 客户名称搜索
            if ($request->filled('customerName')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->customerName . '%');
                });
            }

            // 合同号搜索
            if ($request->filled('contractNo')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
                });
            }

            // 创建人搜索
            if ($request->filled('creator')) {
                $query->whereHas('creator', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->creator . '%');
                });
            }

            // 创建时间范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('minAmount')) {
                $query->where('total_amount', '>=', $request->minAmount);
            }
            if ($request->filled('maxAmount')) {
                $query->where('total_amount', '<=', $request->maxAmount);
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 10);
            
            $total = $query->count();
            $list = $query->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            // 格式化数据
            $list = $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'requestNo' => $item->request_no,
                    'customerName' => $item->customer ? $item->customer->name : '',
                    'contractNo' => $item->contract ? $item->contract->contract_no : '',
                    'totalAmount' => $item->total_amount,
                    'officialFee' => $item->details->where('fee_type', 'official')->sum('amount'),
                    'serviceFee' => $item->details->where('fee_type', 'service')->sum('amount'),
                    'status' => $item->request_status,
                    'statusText' => $this->getStatusText($item->request_status),
                    'creator' => $item->creator ? $item->creator->name : '',
                    'createdAt' => $item->created_at->format('Y-m-d H:i:s'),
                    'remark' => $item->remark,
                ];
            });

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => (int)$page,
                'pageSize' => (int)$pageSize,
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取请款单列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取请款单详情 show
     * 
     * 功能描述：根据ID获取请款单的详细信息，包括关联的客户、合同和费用明细
     * 
     * 传入参数：
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 请款单详情数据
     *   - id (int): ID
     *   - requestNo (string): 请款单号
     *   - customerName (string): 客户名称
     *   - customerId (int): 客户ID
     *   - contractNo (string): 合同号
     *   - contractId (int): 合同ID
     *   - totalAmount (float): 总金额
     *   - status (int): 状态
     *   - statusText (string): 状态文本
     *   - creator (string): 创建人
     *   - createdAt (string): 创建时间
     *   - remark (string): 备注
     *   - details (array): 请款明细列表
     *     - id (int): 明细ID
     *     - caseId (int): 案件ID
     *     - caseFeeId (int): 案件费用ID
     *     - ourRef (string): 我方编号
     *     - applicationNo (string): 申请号
     *     - caseName (string): 案件名称
     *     - caseType (string): 案件类型
     *     - feeType (string): 费用类型
     *     - feeName (string): 费用名称
     *     - amount (float): 金额
     *     - currency (string): 币种
     *     - invoiceNumber (string): 发票号码
     */
    public function show($id)
    {
        try {
            $paymentRequest = PaymentRequest::with(['customer', 'contract', 'creator', 'details.caseFee.case'])
                ->findOrFail($id);

            $data = [
                'id' => $paymentRequest->id,
                'requestNo' => $paymentRequest->request_no,
                'customerName' => $paymentRequest->customer ? $paymentRequest->customer->name : '',
                'customerId' => $paymentRequest->customer_id,
                'contractNo' => $paymentRequest->contract ? $paymentRequest->contract->contract_no : '',
                'contractId' => $paymentRequest->contract_id,
                'totalAmount' => $paymentRequest->total_amount,
                'status' => $paymentRequest->request_status,
                'statusText' => $this->getStatusText($paymentRequest->request_status),
                'creator' => $paymentRequest->creator ? $paymentRequest->creator->name : '',
                'createdAt' => $paymentRequest->created_at->format('Y-m-d H:i:s'),
                'remark' => $paymentRequest->remark,
                'details' => $paymentRequest->details->map(function ($detail) {
                    $caseFee = $detail->caseFee;
                    $case = $caseFee ? $caseFee->case : null;
                    
                    return [
                        'id' => $detail->id,
                        'caseId' => $detail->case_id,
                        'caseFeeId' => $detail->case_fee_id,
                        'ourRef' => $case ? $case->our_ref : '',
                        'applicationNo' => $case ? $case->application_no : '',
                        'caseName' => $case ? $case->case_name : '',
                        'caseType' => $case ? $case->case_type : '',
                        'feeType' => $detail->fee_type,
                        'feeName' => $detail->fee_name,
                        'amount' => $detail->amount,
                        'currency' => $detail->currency,
                        'invoiceNumber' => $detail->invoice_number,
                    ];
                }),
            ];

            return json_success('获取详情成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取请款单详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 创建请款单 store
     * 
     * 功能描述：创建新的请款单，包括基本信息和费用明细
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - customer_id (int): 客户ID，必填，必须存在
     *   - contract_id (int, optional): 合同ID，可为空，必须存在
     *   - fee_ids (array): 费用ID数组，必填，至少包含一个元素，每个元素必须存在
     *   - remark (string, optional): 备注
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 创建结果数据
     *   - id (int): 新创建的请款单ID
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'contract_id' => 'nullable|exists:contracts,id',
                'fee_ids' => 'required|array|min:1',
                'fee_ids.*' => 'exists:case_fees,id',
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            DB::beginTransaction();

            // 生成请款单号
            $requestNo = $this->generateRequestNo();

            // 创建请款单
            $paymentRequest = PaymentRequest::create([
                'request_no' => $requestNo,
                'customer_id' => $request->customer_id,
                'contract_id' => $request->contract_id,
                'request_status' => 1, // 1-草稿
                'total_amount' => 0,
                'created_by' => Auth::id(),
                'remark' => $request->remark,
            ]);

            // 添加请款明细
            $totalAmount = 0;
            foreach ($request->fee_ids as $feeId) {
                $caseFee = CaseFee::findOrFail($feeId);
                
                PaymentRequestDetail::create([
                    'request_id' => $paymentRequest->id,
                    'case_id' => $caseFee->case_id,
                    'case_fee_id' => $caseFee->id,
                    'fee_type' => $caseFee->fee_type,
                    'fee_name' => $caseFee->fee_name,
                    'amount' => $caseFee->amount,
                    'currency' => $caseFee->currency ?? 'CNY',
                ]);

                $totalAmount += $caseFee->amount;
            }

            // 更新总金额
            $paymentRequest->update(['total_amount' => $totalAmount]);

            DB::commit();

            return json_success('创建成功', ['id' => $paymentRequest->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            log_exception($e, '创建请款单失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 更新请款单 update
     * 
     * 功能描述：更新请款单信息，仅允许更新草稿状态的请款单
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - customer_id (int, optional): 客户ID
     *   - contract_id (int, optional): 合同ID
     *   - fee_ids (array, optional): 费用ID数组
     *   - remark (string, optional): 备注
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function update(Request $request, $id)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($id);

            // 只有草稿状态才能编辑
            if ($paymentRequest->request_status != 1) {
                return json_fail('只有草稿状态的请款单才能编辑');
            }

            DB::beginTransaction();

            // 更新基本信息
            $paymentRequest->update([
                'customer_id' => $request->customer_id ?? $paymentRequest->customer_id,
                'contract_id' => $request->contract_id ?? $paymentRequest->contract_id,
                'remark' => $request->remark ?? $paymentRequest->remark,
            ]);

            // 如果提供了新的费用列表,更新明细
            if ($request->has('fee_ids')) {
                // 删除旧明细
                PaymentRequestDetail::where('request_id', $id)->delete();

                // 添加新明细
                $totalAmount = 0;
                foreach ($request->fee_ids as $feeId) {
                    $caseFee = CaseFee::findOrFail($feeId);
                    
                    PaymentRequestDetail::create([
                        'request_id' => $paymentRequest->id,
                        'case_id' => $caseFee->case_id,
                        'case_fee_id' => $caseFee->id,
                        'fee_type' => $caseFee->fee_type,
                        'fee_name' => $caseFee->fee_name,
                        'amount' => $caseFee->amount,
                        'currency' => $caseFee->currency ?? 'CNY',
                    ]);

                    $totalAmount += $caseFee->amount;
                }

                // 更新总金额
                $paymentRequest->update(['total_amount' => $totalAmount]);
            }

            DB::commit();

            return json_success('更新成功');

        } catch (\Exception $e) {
            DB::rollBack();
            log_exception($e, '更新请款单失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除请款单 destroy
     * 
     * 功能描述：删除请款单，仅允许删除草稿状态的请款单
     * 
     * 传入参数：
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function destroy($id)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($id);

            // 只有草稿状态才能删除
            if ($paymentRequest->request_status != 1) {
                return json_fail('只有草稿状态的请款单才能删除');
            }

            DB::beginTransaction();

            // 删除明细
            PaymentRequestDetail::where('request_id', $id)->delete();

            // 删除请款单
            $paymentRequest->delete();

            DB::commit();

            return json_success('删除成功');

        } catch (\Exception $e) {
            DB::rollBack();
            log_exception($e, '删除请款单失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 提交审核 submit
     * 
     * 功能描述：提交请款单进行审核，仅允许提交草稿状态的请款单
     * 
     * 传入参数：
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function submit($id)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($id);

            // 只有草稿状态才能提交
            if ($paymentRequest->request_status != 1) {
                return json_fail('只有草稿状态的请款单才能提交');
            }

            // 检查是否有明细
            if ($paymentRequest->details()->count() == 0) {
                return json_fail('请款单没有明细,无法提交');
            }

            // 更新状态为待审核
            $paymentRequest->update([
                'request_status' => 2, // 2-待审核
                'submitted_at' => now(),
            ]);

            return json_success('提交成功');

        } catch (\Exception $e) {
            log_exception($e, '提交请款单失败');
            return json_fail('提交失败');
        }
    }

    /**
     * 撤回请款单 withdraw
     * 
     * 功能描述：撤回已提交的请款单，仅允许撤回待审核状态的请款单
     * 
     * 传入参数：
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function withdraw($id)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($id);

            // 只有待审核状态才能撤回
            if ($paymentRequest->request_status != 2) {
                return json_fail('只有待审核状态的请款单才能撤回');
            }

            // 更新状态为草稿
            $paymentRequest->update([
                'request_status' => 1, // 1-草稿
            ]);

            return json_success('撤回成功');

        } catch (\Exception $e) {
            log_exception($e, '撤回请款单失败');
            return json_fail('撤回失败');
        }
    }

    /**
     * 审核请款单 approve
     * 
     * 功能描述：审核请款单，可选择通过或退回，仅允许审核待审核状态的请款单
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - status (int): 审核结果，必填，3表示通过，4表示退回
     *   - remark (string, optional): 审核备注
     * - id (int): 请款单ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function approve(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:3,4', // 3-已通过, 4-已退回
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            $paymentRequest = PaymentRequest::findOrFail($id);

            // 只有待审核状态才能审核
            if ($paymentRequest->request_status != 2) {
                return json_fail('只有待审核状态的请款单才能审核');
            }

            // 更新状态
            $paymentRequest->update([
                'request_status' => $request->status,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approve_remark' => $request->remark,
            ]);

            return json_success('审核成功');

        } catch (\Exception $e) {
            log_exception($e, '审核请款单失败');
            return json_fail('审核失败');
        }
    }

    /**
     * 导出请款单 export
     *
     * 功能描述：导出请款单数据为Excel文件
     *
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - ids (array, optional): 请款单ID数组，如果提供则只导出指定的请款单
     *
     * 输出参数：
     * - Excel文件流
     */
    public function export(Request $request)
    {
        try {
            $query = PaymentRequest::with(['customer', 'contract', 'creator', 'details'])
                ->whereNull('deleted_at');

            // 如果提供了ID列表，只导出指定的请款单
            if ($request->filled('ids') && is_array($request->ids)) {
                $query->whereIn('id', $request->ids);
            }

            $paymentRequests = $query->get();

            // 准备导出数据
            $exportData = [];
            $exportData[] = ['请款单号', '客户名称', '合同号', '总金额', '官费', '服务费', '状态', '创建人', '创建时间', '备注'];

            foreach ($paymentRequests as $request) {
                $exportData[] = [
                    $request->request_no,
                    $request->customer ? $request->customer->customer_name : '',
                    $request->contract ? $request->contract->contract_no : '',
                    $request->total_amount,
                    $request->details->where('fee_type', 'official')->sum('amount'),
                    $request->details->where('fee_type', 'service')->sum('amount'),
                    $this->getStatusText($request->request_status),
                    $request->creator ? $request->creator->name : '',
                    $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : '',
                    $request->remark ?? '',
                ];
            }

            // 使用简单的CSV格式导出（可以后续升级为Excel）
            $filename = '请款单列表_' . date('YmdHis') . '.csv';

            $callback = function() use ($exportData) {
                $file = fopen('php://output', 'w');
                // 添加BOM以支持中文
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                foreach ($exportData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            log_exception($e, '导出请款单失败');
            return json_fail('导出失败');
        }
    }

    /**
     * 获取统计数据 statistics
     * 
     * 功能描述：获取请款单相关的统计数据
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - customerName (string, optional): 客户名称搜索条件
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 统计数据
     *   - total (int): 总数
     *   - draft (int): 草稿数量
     *   - pending (int): 待审核数量
     *   - approved (int): 已通过数量
     *   - rejected (int): 已退回数量
     *   - totalAmount (float): 总金额
     */
    public function statistics(Request $request)
    {
        try {
            $query = PaymentRequest::whereNull('deleted_at');

            // 应用筛选条件
            if ($request->filled('customerName')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->customerName . '%');
                });
            }

            $total = $query->count();
            $draft = (clone $query)->where('request_status', 1)->count();
            $pending = (clone $query)->where('request_status', 2)->count();
            $approved = (clone $query)->where('request_status', 3)->count();
            $rejected = (clone $query)->where('request_status', 4)->count();
            $totalAmount = (clone $query)->sum('total_amount');

            return json_success('获取统计成功', [
                'total' => $total,
                'draft' => $draft,
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'totalAmount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取统计数据失败');
            return json_fail('获取统计失败');
        }
    }

    /**
     * 加入请款单 addFees
     *
     * 功能描述：将选中的费用项加入到新的请款单草稿中
     *
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - items (array): 费用项数组，必填
     *     - id (int): 费用ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 创建结果数据
     *   - id (int): 新创建的请款单ID
     *   - requestNo (string): 请款单号
     */
    public function addFees(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:case_fees,id',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            DB::beginTransaction();

            // 获取第一个费用项以确定客户
            $firstFeeId = $request->items[0]['id'];
            $firstFee = CaseFee::with('case.customer')->findOrFail($firstFeeId);

            if (!$firstFee->case || !$firstFee->case->customer_id) {
                return json_fail('费用项未关联有效的客户');
            }

            // 创建请款单草稿
            $paymentRequest = PaymentRequest::create([
                'request_no' => $this->generateRequestNo(),
                'customer_id' => $firstFee->case->customer_id,
                'contract_id' => $firstFee->case->contract_id ?? null,
                'request_status' => 1, // 1-草稿
                'total_amount' => 0,
                'created_by' => Auth::id() ?? 1,
            ]);

            // 添加请款明细
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $caseFee = CaseFee::findOrFail($item['id']);

                PaymentRequestDetail::create([
                    'request_id' => $paymentRequest->id,
                    'case_id' => $caseFee->case_id,
                    'case_fee_id' => $caseFee->id,
                    'fee_type' => $caseFee->fee_type,
                    'fee_name' => $caseFee->fee_name,
                    'amount' => $caseFee->amount,
                    'currency' => $caseFee->currency ?? 'CNY',
                ]);

                $totalAmount += $caseFee->amount;
            }

            // 更新总金额
            $paymentRequest->update(['total_amount' => $totalAmount]);

            DB::commit();

            return json_success('已加入请款单', [
                'id' => $paymentRequest->id,
                'requestNo' => $paymentRequest->request_no,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            log_exception($e, '加入请款单失败');
            return json_fail('加入请款单失败');
        }
    }

    /**
     * 生成请款单号 generateRequestNo
     *
     * 功能描述：生成唯一的请款单号
     *
     * 生成规则：
     * - 前缀：QK
     * - 日期：年月日（YYYYMMDD格式）
     * - 序号：4位数字，每天从1开始递增，不足4位补0
     *
     * 输出参数：
     * - string: 生成的请款单号
     */
    private function generateRequestNo()
    {
        $prefix = 'QK';
        $date = date('Ymd');
        $lastRequest = PaymentRequest::where('request_no', 'like', $prefix . $date . '%')
            ->orderBy('request_no', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNo = intval(substr($lastRequest->request_no, -4));
            $newNo = $lastNo + 1;
        } else {
            $newNo = 1;
        }

        return $prefix . $date . str_pad($newNo, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取状态文本 getStatusText
     * 
     * 功能描述：将状态码转换为可读的状态文本
     * 
     * 传入参数：
     * - status (int): 状态码
     *   - 1: 草稿
     *   - 2: 待审核
     *   - 3: 已通过
     *   - 4: 已退回
     *   - 5: 已撤回
     * 
     * 输出参数：
     * - string: 状态文本，如果状态码无效则返回'未知'
     */
    private function getStatusText($status)
    {
        $statusMap = [
            1 => '草稿',
            2 => '待审核',
            3 => '已通过',
            4 => '已退回',
            5 => '已撤回',
        ];

        return $statusMap[$status] ?? '未知';
    }
}

