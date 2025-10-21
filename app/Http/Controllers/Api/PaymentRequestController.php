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

class PaymentRequestController extends Controller
{
    /**
     * 获取请款单列表
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
     * 获取请款单详情
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
     * 创建请款单
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
     * 更新请款单
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
     * 删除请款单
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
     * 提交审核
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
     * 撤回请款单
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
     * 审核请款单
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
     * 导出请款单
     */
    public function export(Request $request)
    {
        try {
            // TODO: 实现导出功能
            return json_success('导出功能开发中');

        } catch (\Exception $e) {
            log_exception($e, '导出请款单失败');
            return json_fail('导出失败');
        }
    }

    /**
     * 获取统计数据
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
     * 生成请款单号
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
     * 获取状态文本
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

