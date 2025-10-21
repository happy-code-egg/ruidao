<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceived;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PaymentReceivedController extends Controller
{
    /**
     * 获取到款单列表
     */
    public function index(Request $request)
    {
        try {
            $query = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
                ->whereNull('deleted_at');

            // 状态筛选
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 到款单号搜索
            if ($request->filled('paymentNo')) {
                $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
            }

            // 付款方搜索
            if ($request->filled('payer')) {
                $query->where('payer', 'like', '%' . $request->payer . '%');
            }

            // 客户名称搜索
            if ($request->filled('customerName')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customerName . '%');
                });
            }

            // 合同号搜索
            if ($request->filled('contractNo')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
                });
            }

            // 到款日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('received_date', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('minAmount')) {
                $query->where('amount', '>=', $request->minAmount);
            }
            if ($request->filled('maxAmount')) {
                $query->where('amount', '<=', $request->maxAmount);
            }

            // 认领人搜索
            if ($request->filled('claimedBy')) {
                $query->whereHas('claimer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->claimedBy . '%');
                });
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
                    'paymentNo' => $item->payment_no,
                    'customerName' => $item->customer ? $item->customer->customer_name : '',
                    'contractNo' => $item->contract ? $item->contract->contract_no : '',
                    'amount' => $item->amount,
                    'claimedAmount' => $item->claimed_amount,
                    'unclaimedAmount' => $item->unclaimed_amount,
                    'currency' => $item->currency,
                    'payer' => $item->payer,
                    'payerAccount' => $item->payer_account,
                    'bankAccount' => $item->bank_account,
                    'paymentMethod' => $item->payment_method,
                    'transactionRef' => $item->transaction_ref,
                    'receivedDate' => $item->received_date ? $item->received_date->format('Y-m-d') : '',
                    'status' => $item->status,
                    'statusText' => $this->getStatusText($item->status),
                    'creator' => $item->creator ? $item->creator->name : '',
                    'claimer' => $item->claimer ? $item->claimer->name : '',
                    'claimedAt' => $item->claimed_at ? $item->claimed_at->format('Y-m-d H:i:s') : '',
                    'createdAt' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    'remark' => $item->remark,
                    'requestNos' => $item->paymentRequests->pluck('request_no')->toArray(),
                ];
            });

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => (int)$page,
                'pageSize' => (int)$pageSize,
            ]);

        } catch (\Exception $e) {
            \Log::error('获取到款单列表失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取统计数据
     */
    public function statistics(Request $request)
    {
        try {
            $query = PaymentReceived::whereNull('deleted_at');

            // 应用相同的筛选条件
            if ($request->filled('customerName')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customerName . '%');
                });
            }

            $total = $query->count();
            $draft = (clone $query)->where('status', 1)->count();
            $unclaimed = (clone $query)->where('status', 2)->count();
            $claimed = (clone $query)->where('status', 3)->count();
            $writtenOff = (clone $query)->where('status', 4)->count();
            $totalAmount = $query->sum('amount');

            return json_success('获取统计成功', [
                'total' => $total,
                'draft' => $draft,
                'unclaimed' => $unclaimed,
                'claimed' => $claimed,
                'writtenOff' => $writtenOff,
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
            ]);

        } catch (\Exception $e) {
            \Log::error('获取统计数据失败: ' . $e->getMessage());
            return json_fail('获取统计失败');
        }
    }

    /**
     * 获取到款单详情
     */
    public function show($id)
    {
        try {
            $paymentReceived = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
                ->findOrFail($id);

            $data = [
                'id' => $paymentReceived->id,
                'paymentNo' => $paymentReceived->payment_no,
                'customerId' => $paymentReceived->customer_id,
                'customerName' => $paymentReceived->customer ? $paymentReceived->customer->customer_name : '',
                'contractId' => $paymentReceived->contract_id,
                'contractNo' => $paymentReceived->contract ? $paymentReceived->contract->contract_no : '',
                'amount' => $paymentReceived->amount,
                'claimedAmount' => $paymentReceived->claimed_amount,
                'unclaimedAmount' => $paymentReceived->unclaimed_amount,
                'currency' => $paymentReceived->currency,
                'payer' => $paymentReceived->payer,
                'payerAccount' => $paymentReceived->payer_account,
                'bankAccount' => $paymentReceived->bank_account,
                'paymentMethod' => $paymentReceived->payment_method,
                'transactionRef' => $paymentReceived->transaction_ref,
                'receivedDate' => $paymentReceived->received_date ? $paymentReceived->received_date->format('Y-m-d') : '',
                'status' => $paymentReceived->status,
                'statusText' => $this->getStatusText($paymentReceived->status),
                'creator' => $paymentReceived->creator ? $paymentReceived->creator->name : '',
                'claimer' => $paymentReceived->claimer ? $paymentReceived->claimer->name : '',
                'claimedAt' => $paymentReceived->claimed_at ? $paymentReceived->claimed_at->format('Y-m-d H:i:s') : '',
                'createdAt' => $paymentReceived->created_at ? $paymentReceived->created_at->format('Y-m-d H:i:s') : '',
                'remark' => $paymentReceived->remark,
                'paymentRequests' => $paymentReceived->paymentRequests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'requestNo' => $request->request_no,
                        'amount' => $request->total_amount,
                        'allocatedAmount' => $request->pivot->allocated_amount,
                    ];
                }),
            ];

            return json_success('获取详情成功', $data);

        } catch (\Exception $e) {
            \Log::error('获取到款单详情失败: ' . $e->getMessage());
            return json_fail('获取详情失败');
        }
    }

    /**
     * 创建到款单(草稿)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string',
                'payer' => 'required|string',
                'received_date' => 'required|date',
                'bank_account' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            // 生成到款单号
            $paymentNo = $this->generatePaymentNo();

            // 创建到款单
            $paymentReceived = PaymentReceived::create([
                'payment_no' => $paymentNo,
                'status' => 1, // 1-草稿
                'amount' => $request->amount,
                'unclaimed_amount' => $request->amount,
                'currency' => $request->currency,
                'payer' => $request->payer,
                'payer_account' => $request->payer_account,
                'bank_account' => $request->bank_account,
                'payment_method' => $request->payment_method,
                'transaction_ref' => $request->transaction_ref,
                'received_date' => $request->received_date,
                'remark' => $request->remark,
                'created_by' => Auth::id(),
            ]);

            return json_success('创建成功', ['id' => $paymentReceived->id]);

        } catch (\Exception $e) {
            \Log::error('创建到款单失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('创建失败');
        }
    }

    /**
     * 更新到款单
     */
    public function update(Request $request, $id)
    {
        try {
            $paymentReceived = PaymentReceived::findOrFail($id);

            // 只有草稿状态才能编辑
            if ($paymentReceived->status != 1) {
                return json_fail('只有草稿状态的到款单才能编辑');
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string',
                'payer' => 'required|string',
                'received_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            $paymentReceived->update([
                'amount' => $request->amount,
                'unclaimed_amount' => $request->amount - $paymentReceived->claimed_amount,
                'currency' => $request->currency,
                'payer' => $request->payer,
                'payer_account' => $request->payer_account,
                'bank_account' => $request->bank_account,
                'payment_method' => $request->payment_method,
                'transaction_ref' => $request->transaction_ref,
                'received_date' => $request->received_date,
                'remark' => $request->remark,
            ]);

            return json_success('更新成功');

        } catch (\Exception $e) {
            \Log::error('更新到款单失败: ' . $e->getMessage());
            return json_fail('更新失败');
        }
    }

    /**
     * 删除到款单
     */
    public function destroy($id)
    {
        try {
            $paymentReceived = PaymentReceived::findOrFail($id);

            // 只有草稿状态才能删除
            if ($paymentReceived->status != 1) {
                return json_fail('只有草稿状态的到款单才能删除');
            }

            $paymentReceived->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            \Log::error('删除到款单失败: ' . $e->getMessage());
            return json_fail('删除失败');
        }
    }

    /**
     * 提交到款单(草稿->待认领)
     */
    public function submit($id)
    {
        try {
            $paymentReceived = PaymentReceived::findOrFail($id);

            // 只有草稿状态才能提交
            if ($paymentReceived->status != 1) {
                return json_fail('只有草稿状态的到款单才能提交');
            }

            $paymentReceived->update([
                'status' => 2, // 2-待认领
            ]);

            return json_success('提交成功');

        } catch (\Exception $e) {
            \Log::error('提交到款单失败: ' . $e->getMessage());
            return json_fail('提交失败');
        }
    }

    /**
     * 认领到款单
     */
    public function claim(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'request_ids' => 'required|array|min:1',
                'request_ids.*' => 'exists:payment_requests,id',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            $paymentReceived = PaymentReceived::findOrFail($id);

            // 只有待认领状态才能认领
            if ($paymentReceived->status != 2) {
                return json_fail('只有待认领状态的到款单才能认领');
            }

            DB::beginTransaction();

            // 获取请款单信息并验证
            $paymentRequests = PaymentRequest::whereIn('id', $request->request_ids)->get();
            
            if ($paymentRequests->count() != count($request->request_ids)) {
                DB::rollBack();
                return json_fail('部分请款单不存在');
            }

            // 计算请款单总额
            $totalRequestAmount = $paymentRequests->sum('total_amount');

            // 验证总额不能超过到款金额
            if ($totalRequestAmount > $paymentReceived->amount) {
                DB::rollBack();
                return json_fail('所选请款单总额不能超过到款金额');
            }

            // 更新到款单
            $paymentReceived->update([
                'customer_id' => $request->customer_id,
                'contract_id' => $request->contract_id,
                'status' => 3, // 3-已认领
                'claimed_amount' => $totalRequestAmount,
                'unclaimed_amount' => $paymentReceived->amount - $totalRequestAmount,
                'claimed_by' => Auth::id(),
                'claimed_at' => now(),
            ]);

            // 关联请款单
            $syncData = [];
            foreach ($paymentRequests as $paymentRequest) {
                $syncData[$paymentRequest->id] = [
                    'allocated_amount' => $paymentRequest->total_amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $paymentReceived->paymentRequests()->sync($syncData);

            DB::commit();

            return json_success('认领成功');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('认领到款单失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('认领失败');
        }
    }

    /**
     * 导出
     */
    public function export(Request $request)
    {
        try {
            $query = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
                ->whereNull('deleted_at');

            // 状态筛选
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 到款单号搜索
            if ($request->filled('paymentNo')) {
                $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
            }

            // 付款方搜索
            if ($request->filled('payer')) {
                $query->where('payer', 'like', '%' . $request->payer . '%');
            }

            // 客户名称搜索
            if ($request->filled('customerName')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customerName . '%');
                });
            }

            // 合同号搜索
            if ($request->filled('contractNo')) {
                $query->whereHas('contract', function ($q) use ($request) {
                    $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
                });
            }

            // 到款日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('received_date', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('minAmount')) {
                $query->where('amount', '>=', $request->minAmount);
            }
            if ($request->filled('maxAmount')) {
                $query->where('amount', '<=', $request->maxAmount);
            }

            // 认领人搜索
            if ($request->filled('claimedBy')) {
                $query->whereHas('claimer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->claimedBy . '%');
                });
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 获取所有数据
            $list = $query->get();

            // 准备导出数据
            $exportData = [];
            $exportData[] = [
                '到款单号',
                '客户名称',
                '合同号',
                '到款金额',
                '已认领金额',
                '未认领金额',
                '币种',
                '付款方',
                '付款账号',
                '收款账号',
                '付款方式',
                '交易流水号',
                '到账日期',
                '状态',
                '创建人',
                '认领人',
                '认领时间',
                '创建时间',
                '备注',
            ];

            foreach ($list as $item) {
                $exportData[] = [
                    $item->payment_no,
                    $item->customer ? $item->customer->customer_name : '',
                    $item->contract ? $item->contract->contract_no : '',
                    $item->amount,
                    $item->claimed_amount,
                    $item->unclaimed_amount,
                    $item->currency,
                    $item->payer,
                    $item->payer_account,
                    $item->bank_account,
                    $item->payment_method,
                    $item->transaction_ref,
                    $item->received_date ? $item->received_date->format('Y-m-d') : '',
                    $this->getStatusText($item->status),
                    $item->creator ? $item->creator->name : '',
                    $item->claimer ? $item->claimer->name : '',
                    $item->claimed_at ? $item->claimed_at->format('Y-m-d H:i:s') : '',
                    $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    $item->remark,
                ];
            }

            // 使用 FastExcel 导出
            $fileName = '到款单列表_' . date('YmdHis') . '.xlsx';
            $filePath = storage_path('app/public/exports/' . $fileName);

            // 确保目录存在
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            // 导出Excel
            $fastExcel = new \Rap2hpoutre\FastExcel\FastExcel($exportData);
            $fastExcel->export($filePath);

            // 返回文件
            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('导出失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('导出失败');
        }
    }

    /**
     * 生成到款单号
     */
    private function generatePaymentNo()
    {
        $prefix = 'DK';
        $date = date('Ymd');
        $lastPayment = PaymentReceived::where('payment_no', 'like', $prefix . $date . '%')
            ->orderBy('payment_no', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->payment_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $texts = [
            1 => '草稿',
            2 => '待认领',
            3 => '已认领',
            4 => '已核销',
        ];
        return $texts[$status] ?? $status;
    }
}

