<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WriteOff;
use App\Models\PaymentReceived;
use App\Models\PaymentRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 核销控制器
 * 负责到款单与请款单的核销管理，包括待核销列表查询、核销操作等
 */
class WriteOffController extends Controller
{
    /**
     * 获取待核销列表
     *
     * 请求参数:
     * - paymentNo(string)：到款单号，模糊匹配
     * - requestNo(string)：请款单号，模糊匹配
     * - customerName(string)：客户名称，模糊匹配
     * - contractNo(string)：合同号，模糊匹配
     * - receivedTimeStart(string:YYYY-MM-DD)：到账开始日期
     * - receivedTimeEnd(string:YYYY-MM-DD)：到账结束日期
     * - minAmount(number)：最小到款金额
     * - maxAmount(number)：最大到款金额
     * - claimedBy(string)：认领人姓名，模糊匹配
     * - page(int)：页码，默认1
     * - pageSize(int)：每页数量，默认10
     *
     * 返回参数:
     * - list(array)：记录列表（字段：id、paymentNo、customerName、customerId、techLead、contractNo、totalAmount、usedAmount、remainingAmount、receivedTime、claimedBy、matchedRequests[{requestNo,amount}]、waitingDays）
     * - total(int)：总记录数
     * - page(int)：当前页
     * - pageSize(int)：每页大小
     *
     * 功能:
     * - 查询已认领但未完全核销的到款单，支持多条件筛选与分页
     * - 接口: GET /write-offs/pending
     */
    public function getPendingList(Request $request)
    {
        try {
            // 查询已认领但未完全核销的到款单
            $query = PaymentReceived::with(['customer.techLead', 'contract', 'claimer', 'paymentRequests'])
                ->whereNull('deleted_at')
                ->where('status', 3) // 3-已认领
                ->where(function($q) {
                    $q->whereRaw('amount > claimed_amount')
                      ->orWhereRaw('unclaimed_amount > 0');
                });

            // 搜索条件
            if ($request->filled('paymentNo')) {
                $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
            }

            if ($request->filled('requestNo')) {
                $query->whereHas('paymentRequests', function($q) use ($request) {
                    $q->where('request_no', 'like', '%' . $request->requestNo . '%');
                });
            }

            if ($request->filled('customerName')) {
                $query->whereHas('customer', function($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customerName . '%');
                });
            }

            if ($request->filled('contractNo')) {
                $query->whereHas('contract', function($q) use ($request) {
                    $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
                });
            }

            if ($request->filled('receivedTimeStart') && $request->filled('receivedTimeEnd')) {
                $query->whereBetween('received_date', [$request->receivedTimeStart, $request->receivedTimeEnd]);
            }

            if ($request->filled('minAmount')) {
                $query->where('amount', '>=', $request->minAmount);
            }

            if ($request->filled('maxAmount')) {
                $query->where('amount', '<=', $request->maxAmount);
            }

            if ($request->filled('claimedBy')) {
                $query->whereHas('claimer', function($q) use ($request) {
                    $q->where('real_name', 'like', '%' . $request->claimedBy . '%');
                });
            }

            // 分页
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 10);
            $total = $query->count();

            $list = $query->orderBy('received_date', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            // 格式化数据
            $data = $list->map(function($item) {
                $usedAmount = WriteOff::where('payment_received_id', $item->id)
                    ->where('status', 1) // 1-已完成
                    ->sum('write_off_amount');

                $remainingAmount = $item->amount - $usedAmount;

                // 获取匹配的请款单
                $matchedRequests = $item->paymentRequests->map(function($req) {
                    return [
                        'requestNo' => $req->request_no,
                        'amount' => number_format($req->pivot->allocated_amount, 2, '.', '')
                    ];
                });

                // 计算等待天数
                $receivedDate = \Carbon\Carbon::parse($item->received_date);
                $waitingDays = $receivedDate->diffInDays(now());

                return [
                    'id' => $item->id,
                    'paymentNo' => $item->payment_no,
                    'customerName' => $item->customer ? $item->customer->customer_name : '',
                    'customerId' => $item->customer_id,
                    'techLead' => $item->customer && $item->customer->techLead ? $item->customer->techLead->real_name : '',
                    'contractNo' => $item->contract ? $item->contract->contract_no : '',
                    'totalAmount' => number_format($item->amount, 2, '.', ''),
                    'usedAmount' => number_format($usedAmount, 2, '.', ''),
                    'remainingAmount' => number_format($remainingAmount, 2, '.', ''),
                    'receivedTime' => $item->received_date ? $item->received_date->format('Y-m-d') : '',
                    'claimedBy' => $item->claimer ? $item->claimer->real_name : '',
                    'matchedRequests' => $matchedRequests,
                    'waitingDays' => $waitingDays,
                ];
            });

            return json_success('获取成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
            ]);

        } catch (\Exception $e) {
            \Log::error('获取待核销列表失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('获取列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取待核销统计
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - totalCount(int)：待核销到款单数量
     * - totalAmount(string)：待核销到款总额，保留两位小数
     * - matchedCount(int)：已匹配请款单的到款数
     * - unmatchedCount(int)：未匹配请款单的到款数
     *
     * 功能:
     * - 统计已认领到款单的数量与金额，并区分是否匹配请款单
     * - 接口: GET /write-offs/pending/statistics
     */
    public function getPendingStatistics(Request $request)
    {
        try {
            $query = PaymentReceived::whereNull('deleted_at')
                ->where('status', 3);

            $totalCount = $query->count();
            $totalAmount = $query->sum('amount');

            // 已匹配请款单的数量
            $matchedCount = PaymentReceived::whereNull('deleted_at')
                ->where('status', 3)
                ->whereHas('paymentRequests')
                ->count();
            $unmatchedCount = $totalCount - $matchedCount;

            return json_success('获取成功', [
                'totalCount' => $totalCount,
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
                'matchedCount' => $matchedCount,
                'unmatchedCount' => $unmatchedCount,
            ]);

        } catch (\Exception $e) {
            \Log::error('获取待核销统计失败: ' . $e->getMessage());
            return json_fail('获取统计失败');
        }
    }

    /**
     * 执行核销
     *
     * 请求参数:
     * - paymentReceivedId(int, required)：到款ID
     * - writeOffAmount(number, required, min:0.01)：核销金额
     * - requestId(int, optional)：关联请款单ID
     * - remark(string, optional)：备注
     *
     * 返回参数:
     * - writeOffNo(string)：生成的核销单号
     *
     * 功能:
     * - 对指定到款进行核销，校验剩余可核销金额，记录核销并更新到款累计核销与状态
     * - 接口: POST /write-offs/write-off
     */
    public function writeOff(Request $request)
    {
        try {
            // 步骤1：参数校验
            $validator = Validator::make($request->all(), [
                'paymentReceivedId' => 'required|exists:payment_receiveds,id',
                'writeOffAmount' => 'required|numeric|min:0.01',
                'requestId' => 'nullable|exists:payment_requests,id',
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            // 步骤2：开启事务
            DB::beginTransaction();

            $paymentReceived = PaymentReceived::findOrFail($request->paymentReceivedId);

            // 步骤3：计算已核销与剩余可核销金额，并校验
            $usedAmount = WriteOff::where('payment_received_id', $paymentReceived->id)
                ->where('status', 1)
                ->sum('write_off_amount');
            $remainingAmount = $paymentReceived->amount - $usedAmount;

            if ($request->writeOffAmount > $remainingAmount) {
                DB::rollBack();
                return json_fail('核销金额不能超过待核销金额');
            }

            // 步骤4：创建核销记录
            $writeOff = WriteOff::create([
                'write_off_no' => WriteOff::generateWriteOffNo(),
                'payment_received_id' => $paymentReceived->id,
                'payment_request_id' => $request->requestId,
                'customer_id' => $paymentReceived->customer_id,
                'contract_id' => $paymentReceived->contract_id,
                'write_off_amount' => $request->writeOffAmount,
                'write_off_date' => now()->toDateString(),
                'status' => 1, // 1-已完成
                'remark' => $request->remark,
                'write_off_by' => Auth::id(),
                'write_off_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // 步骤5：更新到款单累计核销与状态
            $newUsedAmount = $usedAmount + $request->writeOffAmount;
            $newRemainingAmount = $paymentReceived->amount - $newUsedAmount;

            $paymentReceived->update([
                'claimed_amount' => $newUsedAmount,
                'unclaimed_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 4 : 3, // 4-已核销, 3-已认领
            ]);

            // 步骤6：提交事务
            DB::commit();

            return json_success('核销成功', ['writeOffNo' => $writeOff->write_off_no]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('核销失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('核销失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量核销
     *
     * 请求参数:
     * - items(array, required)：核销项列表，元素字段：
     *   - paymentReceivedId(int, required)：到款ID
     *   - writeOffAmount(number, required, min:0.01)：核销金额
     *   - requestId(int, optional)：关联请款单ID
     *   - remark(string, optional)：备注
     *
     * 返回参数:
     * - successCount(int)：成功条数
     * - failedCount(int)：失败条数
     * - failedItems(array)：失败项[{paymentNo(string), reason(string)}]
     *
     * 功能:
     * - 批量对到款进行核销，逐项校验剩余金额，支持部分失败并返回失败原因
     * - 接口: POST /write-offs/batch-write-off
     */
    public function batchWriteOff(Request $request)
    {
        try {
            // 步骤1：参数校验
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.paymentReceivedId' => 'required|exists:payment_receiveds,id',
                'items.*.writeOffAmount' => 'required|numeric|min:0.01',
                'items.*.requestId' => 'nullable|exists:payment_requests,id',
                'items.*.remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            // 步骤2：开启事务
            DB::beginTransaction();

            $successCount = 0;
            $failedItems = [];

            // 步骤3：逐项处理核销
            foreach ($request->items as $item) {
                try {
                    $paymentReceived = PaymentReceived::findOrFail($item['paymentReceivedId']);

                    $usedAmount = WriteOff::where('payment_received_id', $paymentReceived->id)
                        ->where('status', 1)
                        ->sum('write_off_amount');
                    $remainingAmount = $paymentReceived->amount - $usedAmount;

                    if ($item['writeOffAmount'] > $remainingAmount) {
                        $failedItems[] = [
                            'paymentNo' => $paymentReceived->payment_no,
                            'reason' => '核销金额超过待核销金额'
                        ];
                        continue;
                    }

                    WriteOff::create([
                        'write_off_no' => WriteOff::generateWriteOffNo(),
                        'payment_received_id' => $paymentReceived->id,
                        'payment_request_id' => $item['requestId'] ?? null,
                        'customer_id' => $paymentReceived->customer_id,
                        'contract_id' => $paymentReceived->contract_id,
                        'write_off_amount' => $item['writeOffAmount'],
                        'write_off_date' => now()->toDateString(),
                        'status' => 1,
                        'remark' => $item['remark'] ?? null,
                        'write_off_by' => Auth::id(),
                        'write_off_at' => now(),
                        'created_by' => Auth::id(),
                    ]);

                    $newUsedAmount = $usedAmount + $item['writeOffAmount'];
                    $newRemainingAmount = $paymentReceived->amount - $newUsedAmount;

                    $paymentReceived->update([
                        'claimed_amount' => $newUsedAmount,
                        'unclaimed_amount' => $newRemainingAmount,
                        'status' => $newRemainingAmount <= 0 ? 4 : 3,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $failedItems[] = [
                        'paymentNo' => $paymentReceived->payment_no ?? 'unknown',
                        'reason' => $e->getMessage()
                    ];
                }
            }

            // 步骤4：提交事务并汇总结果
            DB::commit();

            return json_success('批量核销完成', [
                'successCount' => $successCount,
                'failedCount' => count($failedItems),
                'failedItems' => $failedItems,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('批量核销失败: ' . $e->getMessage());
            return json_fail('批量核销失败');
        }
    }

    /**
     * 获取核销明细列表(已核销)
     *
     * 请求参数:
     * - writeOffNo(string)：核销单号，模糊匹配
     * - paymentNo(string)：到款单号，模糊匹配
     * - requestNo(string)：请款单号，模糊匹配
     * - customerName(string)：客户名称，模糊匹配
     * - writeOffTimeStart(string:YYYY-MM-DD)：核销开始日期
     * - writeOffTimeEnd(string:YYYY-MM-DD)：核销结束日期
     * - minAmount(number)：最小核销金额
     * - maxAmount(number)：最大核销金额
     * - writeOffBy(string)：核销人姓名，模糊匹配
     * - status(int)：状态（1已完成，2已撤销）
     * - page(int)：页码，默认1
     * - pageSize(int)：每页数量，默认10
     *
     * 返回参数:
     * - list(array)：记录列表（字段：id、writeOffNo、paymentNo、requestNo、customerName、customerId、techLead、contractNo、writeOffAmount、writeOffDate、writeOffBy、status、remark、createTime）
     * - total(int)：总记录数
     * - page(int)：当前页
     * - pageSize(int)：每页大小
     *
     * 功能:
     * - 查询已完成的核销记录，支持多条件筛选与分页
     * - 接口: GET /write-offs/completed
     */
    public function getCompletedList(Request $request)
    {
        try {
            $query = WriteOff::with([
                'paymentReceived',
                'paymentRequest',
                'customer.techLead',
                'contract',
                'writeOffUser'
            ])->whereNull('deleted_at')
            ->where('status', 1); // 只显示已完成的核销记录,不显示已撤销的

            // 搜索条件
            if ($request->filled('writeOffNo')) {
                $query->where('write_off_no', 'like', '%' . $request->writeOffNo . '%');
            }

            if ($request->filled('paymentNo')) {
                $query->whereHas('paymentReceived', function($q) use ($request) {
                    $q->where('payment_no', 'like', '%' . $request->paymentNo . '%');
                });
            }

            if ($request->filled('requestNo')) {
                $query->whereHas('paymentRequest', function($q) use ($request) {
                    $q->where('request_no', 'like', '%' . $request->requestNo . '%');
                });
            }

            if ($request->filled('customerName')) {
                $query->whereHas('customer', function($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customerName . '%');
                });
            }

            if ($request->filled('writeOffTimeStart') && $request->filled('writeOffTimeEnd')) {
                $query->whereBetween('write_off_date', [$request->writeOffTimeStart, $request->writeOffTimeEnd]);
            }

            if ($request->filled('minAmount')) {
                $query->where('write_off_amount', '>=', $request->minAmount);
            }

            if ($request->filled('maxAmount')) {
                $query->where('write_off_amount', '<=', $request->maxAmount);
            }

            if ($request->filled('writeOffBy')) {
                $query->whereHas('writeOffUser', function($q) use ($request) {
                    $q->where('real_name', 'like', '%' . $request->writeOffBy . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 分页
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 10);
            $total = $query->count();

            $list = $query->orderBy('write_off_date', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            // 格式化数据
            $data = $list->map(function($item) {
                return [
                    'id' => $item->id,
                    'writeOffNo' => $item->write_off_no,
                    'paymentNo' => $item->paymentReceived ? $item->paymentReceived->payment_no : '',
                    'requestNo' => $item->paymentRequest ? $item->paymentRequest->request_no : '',
                    'customerName' => $item->customer ? $item->customer->customer_name : '',
                    'customerId' => $item->customer_id,
                    'techLead' => $item->customer && $item->customer->techLead ? $item->customer->techLead->real_name : '',
                    'contractNo' => $item->contract ? $item->contract->contract_no : '',
                    'writeOffAmount' => number_format($item->write_off_amount, 2, '.', ''),
                    'writeOffDate' => $item->write_off_date ? $item->write_off_date->format('Y-m-d') : '',
                    'writeOffBy' => $item->writeOffUser ? $item->writeOffUser->real_name : '',
                    'status' => $item->status,
                    'remark' => $item->remark,
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return json_success('获取成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
            ]);

        } catch (\Exception $e) {
            \Log::error('获取核销明细列表失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('获取列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取核销明细统计
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - totalCount(int)：已完成核销记录数
     * - totalAmount(string)：已完成核销总额
     * - thisMonthCount(int)：本月核销记录数
     * - thisMonthAmount(string)：本月核销总额
     *
     * 功能:
     * - 统计核销明细的总体与本月数据（不含撤销）
     * - 接口: GET /write-offs/completed/statistics
     */
    public function getCompletedStatistics(Request $request)
    {
        try {
            // 只统计已完成的核销记录,不包括已撤销的
            $totalCount = WriteOff::whereNull('deleted_at')->where('status', 1)->count();
            $totalAmount = WriteOff::whereNull('deleted_at')->where('status', 1)->sum('write_off_amount');

            // 本月核销
            $thisMonthCount = WriteOff::whereNull('deleted_at')
                ->where('status', 1)
                ->whereYear('write_off_date', date('Y'))
                ->whereMonth('write_off_date', date('m'))
                ->count();

            $thisMonthAmount = WriteOff::whereNull('deleted_at')
                ->where('status', 1)
                ->whereYear('write_off_date', date('Y'))
                ->whereMonth('write_off_date', date('m'))
                ->sum('write_off_amount');

            return json_success('获取成功', [
                'totalCount' => $totalCount,
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
                'thisMonthCount' => $thisMonthCount,
                'thisMonthAmount' => number_format($thisMonthAmount, 2, '.', ''),
            ]);

        } catch (\Exception $e) {
            \Log::error('获取核销统计失败: ' . $e->getMessage());
            return json_fail('获取统计失败');
        }
    }

    /**
     * 获取核销完成统计
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - totalCount(int)：已完全核销的到款单数量
     * - totalAmount(string)：已完全核销的到款总额
     * - thisMonthCount(int)：本月完成核销的到款数
     * - thisMonthAmount(string)：本月完成核销的到款总额
     *
     * 功能:
     * - 针对到款维度统计“核销完成”（到款累计核销=到款总额）的数据
     * - 接口: GET /write-offs/write-off-completed/statistics
     */
    public function getWriteOffCompletedStatistics(Request $request)
    {
        try {
            // 统计已完全核销的到款单
            // 查询status=4且unclaimed_amount=0的到款单(确保数据准确性)
            $query = PaymentReceived::whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0);

            $totalCount = $query->count();
            $totalAmount = $query->sum('amount');

            // 本月核销完成
            $thisMonthCount = PaymentReceived::whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0)
                ->whereYear('updated_at', date('Y'))
                ->whereMonth('updated_at', date('m'))
                ->count();

            $thisMonthAmount = PaymentReceived::whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0)
                ->whereYear('updated_at', date('Y'))
                ->whereMonth('updated_at', date('m'))
                ->sum('amount');

            return json_success('获取成功', [
                'totalCount' => $totalCount,
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
                'thisMonthCount' => $thisMonthCount,
                'thisMonthAmount' => number_format($thisMonthAmount, 2, '.', '')
            ]);
        } catch (\Exception $e) {
            return json_fail('获取失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取核销完成列表(用于核销完成弹窗)
     *
     * 请求参数:
     * - page(int)：页码，默认1
     * - pageSize(int)：每页数量，默认10
     *
     * 返回参数:
     * - list(array)：到款完成核销列表，字段：id、paymentNo、customerName、customerId、techLead、totalAmount、claimedAmount、receivedDate、writeOffs[{writeOffNo, writeOffAmount, writeOffDate, writeOffBy, requestNo}]
     * - total(int)：总记录数
     * - page(int)：当前页
     * - pageSize(int)：每页大小
     * - statistics(object)：统计汇总（totalCount、totalAmount、customerCount、requestCount）
     *
     * 功能:
     * - 展示已完全核销的到款单及其关联的核销记录，并提供统计数据
     * - 接口: GET /write-offs/write-off-completed
     */
    public function getWriteOffCompletedList(Request $request)
    {
        try {
            // 获取已完全核销的到款单
            // 查询status=4且unclaimed_amount=0的到款单(确保数据准确性)
            $query = PaymentReceived::with(['customer.techLead', 'writeOffs.writeOffUser', 'writeOffs.paymentRequest'])
                ->whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0);

            // 分页
            $page = $request->input('page', 1);
            $pageSize = $request->input('pageSize', 10);
            $total = $query->count();

            $list = $query->orderBy('updated_at', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            // 格式化数据
            $data = $list->map(function($item) {
                $writeOffs = $item->writeOffs->where('status', 1)->map(function($wo) {
                    return [
                        'writeOffNo' => $wo->write_off_no,
                        'writeOffAmount' => number_format($wo->write_off_amount, 2, '.', ''),
                        'writeOffDate' => $wo->write_off_date ? $wo->write_off_date->format('Y-m-d') : '',
                        'writeOffBy' => $wo->writeOffUser ? $wo->writeOffUser->real_name : '',
                        'requestNo' => $wo->paymentRequest ? $wo->paymentRequest->request_no : '',
                    ];
                });

                return [
                    'id' => $item->id,
                    'paymentNo' => $item->payment_no,
                    'customerName' => $item->customer ? $item->customer->customer_name : '',
                    'customerId' => $item->customer_id,
                    'techLead' => $item->customer && $item->customer->techLead ? $item->customer->techLead->real_name : '',
                    'totalAmount' => number_format($item->amount, 2, '.', ''),
                    'claimedAmount' => number_format($item->claimed_amount, 2, '.', ''),
                    'receivedDate' => $item->received_date ? $item->received_date->format('Y-m-d') : '',
                    'writeOffs' => $writeOffs->values(),
                ];
            });

            // 统计数据
            $totalCount = $total;
            $totalAmount = PaymentReceived::whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0)
                ->sum('amount');
            $customerCount = PaymentReceived::whereNull('deleted_at')
                ->where('status', 4)
                ->where('unclaimed_amount', '=', 0)
                ->distinct('customer_id')
                ->count('customer_id');
            $requestCount = WriteOff::whereNull('deleted_at')
                ->where('status', 1)
                ->whereNotNull('payment_request_id')
                ->distinct('payment_request_id')
                ->count('payment_request_id');

            return json_success('获取成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
                'statistics' => [
                    'totalCount' => $totalCount,
                    'totalAmount' => number_format($totalAmount, 2, '.', ''),
                    'customerCount' => $customerCount,
                    'requestCount' => $requestCount,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('获取核销完成列表失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('获取列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 撤销核销
     *
     * 请求参数:
     * - id(path int, required)：核销记录ID
     * - reason(string, optional)：撤销原因
     *
     * 返回参数:
     * - message(string)：操作结果描述
     *
     * 功能:
     * - 将指定核销记录状态改为已撤销，并回退到款的累计核销金额与状态
     * - 接口: POST /write-offs/{id}/revert
     */
    public function revertWriteOff(Request $request, $id)
    {
        try {
            // 步骤1：参数校验
            $validator = Validator::make($request->all(), [
                'reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            // 步骤2：开启事务
            DB::beginTransaction();

            $writeOff = WriteOff::findOrFail($id);

            // 步骤3：仅允许撤销已完成的核销记录
            if ($writeOff->status != 1) {
                DB::rollBack();
                return json_fail('只有已完成状态的核销记录才能撤销');
            }

            // 步骤4：更新核销记录状态为已撤销
            $writeOff->update([
                'status' => 2, // 2-已撤销
                'reverted_by' => Auth::id(),
                'reverted_at' => now(),
                'revert_reason' => $request->reason,
            ]);

            // 步骤5：回退到款累计核销金额并恢复状态
            $paymentReceived = $writeOff->paymentReceived;
            $newClaimedAmount = $paymentReceived->claimed_amount - $writeOff->write_off_amount;
            $newUnclaimedAmount = $paymentReceived->unclaimed_amount + $writeOff->write_off_amount;

            $paymentReceived->update([
                'claimed_amount' => $newClaimedAmount,
                'unclaimed_amount' => $newUnclaimedAmount,
                'status' => 3, // 3-已认领(撤销后回到已认领状态)
            ]);

            // 步骤6：提交事务
            DB::commit();

            return json_success('撤销核销成功');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('撤销核销失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('撤销核销失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量撤销核销
     *
     * 请求参数:
     * - ids(array, required)：核销记录ID列表
     * - reason(string, optional)：撤销原因
     *
     * 返回参数:
     * - successCount(int)：成功条数
     * - failedCount(int)：失败条数
     * - failedItems(array)：失败项[{writeOffNo(string), reason(string)}]
     *
     * 功能:
     * - 批量撤销核销记录，逐项校验状态并回退到款累计核销金额，支持部分失败
     * - 接口: POST /write-offs/batch-revert
     */
    public function batchRevertWriteOff(Request $request)
    {
        try {
            // 步骤1：参数校验
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'required|exists:write_offs,id',
                'reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return json_fail('验证失败', $validator->errors());
            }

            // 步骤2：开启事务
            DB::beginTransaction();

            $successCount = 0;
            $failedItems = [];

            // 步骤3：逐项处理撤销
            foreach ($request->ids as $id) {
                try {
                    $writeOff = WriteOff::findOrFail($id);

                    if ($writeOff->status != 1) {
                        $failedItems[] = [
                            'writeOffNo' => $writeOff->write_off_no,
                            'reason' => '只有已完成状态的核销记录才能撤销'
                        ];
                        continue;
                    }

                    $writeOff->update([
                        'status' => 2,
                        'reverted_by' => Auth::id(),
                        'reverted_at' => now(),
                        'revert_reason' => $request->reason,
                    ]);

                    $paymentReceived = $writeOff->paymentReceived;
                    $newClaimedAmount = $paymentReceived->claimed_amount - $writeOff->write_off_amount;
                    $newUnclaimedAmount = $paymentReceived->unclaimed_amount + $writeOff->write_off_amount;

                    $paymentReceived->update([
                        'claimed_amount' => $newClaimedAmount,
                        'unclaimed_amount' => $newUnclaimedAmount,
                        'status' => 3,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $failedItems[] = [
                        'writeOffNo' => $writeOff->write_off_no ?? 'unknown',
                        'reason' => $e->getMessage()
                    ];
                }
            }

            // 步骤4：提交事务并汇总结果
            DB::commit();

            return json_success('批量撤销完成', [
                'successCount' => $successCount,
                'failedCount' => count($failedItems),
                'failedItems' => $failedItems,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('批量撤销失败: ' . $e->getMessage());
            return json_fail('批量撤销失败');
        }
    }

    /**
     * 获取核销详情
     *
     * 请求参数:
     * - id(path int, required)：核销记录ID
     *
     * 返回参数:
     * - 详情对象：id、writeOffNo、paymentNo、requestNo、customerName、customerId、techLead、contractNo、writeOffAmount、writeOffDate、writeOffBy、status、remark、revertedBy、revertedAt、revertReason、createTime、paymentInfo{paymentNo,totalAmount,totalUsed,currentUsed,remaining,usageRate}、operationLogs[{action,operator,time,comment}]
     *
     * 功能:
     * - 返回核销记录的完整细节信息（含到款占用明细及操作日志）
     * - 接口: GET /write-offs/{id}
     */
    public function show($id)
    {
        try {
            $writeOff = WriteOff::with([
                'paymentReceived',
                'paymentRequest',
                'customer.techLead',
                'contract',
                'writeOffUser',
                'revertUser'
            ])->findOrFail($id);

            $data = [
                'id' => $writeOff->id,
                'writeOffNo' => $writeOff->write_off_no,
                'paymentNo' => $writeOff->paymentReceived ? $writeOff->paymentReceived->payment_no : '',
                'requestNo' => $writeOff->paymentRequest ? $writeOff->paymentRequest->request_no : '',
                'customerName' => $writeOff->customer ? $writeOff->customer->customer_name : '',
                'customerId' => $writeOff->customer_id,
                'techLead' => $writeOff->customer && $writeOff->customer->techLead ? $writeOff->customer->techLead->real_name : '',
                'contractNo' => $writeOff->contract ? $writeOff->contract->contract_no : '',
                'writeOffAmount' => number_format($writeOff->write_off_amount, 2, '.', ''),
                'writeOffDate' => $writeOff->write_off_date ? $writeOff->write_off_date->format('Y-m-d') : '',
                'writeOffBy' => $writeOff->writeOffUser ? $writeOff->writeOffUser->real_name : '',
                'status' => $writeOff->status,
                'remark' => $writeOff->remark,
                'revertedBy' => $writeOff->revertUser ? $writeOff->revertUser->real_name : '',
                'revertedAt' => $writeOff->reverted_at ? $writeOff->reverted_at->format('Y-m-d H:i:s') : '',
                'revertReason' => $writeOff->revert_reason,
                'createTime' => $writeOff->created_at ? $writeOff->created_at->format('Y-m-d H:i:s') : '',
                'paymentInfo' => $writeOff->paymentReceived ? [
                    'paymentNo' => $writeOff->paymentReceived->payment_no,
                    'totalAmount' => number_format($writeOff->paymentReceived->amount, 2, '.', ''),
                    'totalUsed' => number_format($writeOff->paymentReceived->claimed_amount, 2, '.', ''),
                    'currentUsed' => number_format($writeOff->write_off_amount, 2, '.', ''),
                    'remaining' => number_format($writeOff->paymentReceived->unclaimed_amount, 2, '.', ''),
                    'usageRate' => $writeOff->paymentReceived->amount > 0
                        ? round(($writeOff->paymentReceived->claimed_amount / $writeOff->paymentReceived->amount) * 100)
                        : 0,
                ] : null,
                'operationLogs' => [
                    [
                        'action' => '创建核销单',
                        'operator' => $writeOff->writeOffUser ? $writeOff->writeOffUser->real_name : '',
                        'time' => $writeOff->write_off_at ? $writeOff->write_off_at->format('Y-m-d H:i:s') : '',
                        'comment' => '核销申请',
                    ],
                    [
                        'action' => '审核通过',
                        'operator' => $writeOff->writeOffUser ? $writeOff->writeOffUser->real_name : '',
                        'time' => $writeOff->write_off_at ? $writeOff->write_off_at->format('Y-m-d H:i:s') : '',
                        'comment' => '审核通过，核销完成',
                    ],
                ],
            ];

            if ($writeOff->status == 2) {
                $data['operationLogs'][] = [
                    'action' => '撤销核销',
                    'operator' => $writeOff->revertUser ? $writeOff->revertUser->real_name : '',
                    'time' => $writeOff->reverted_at ? $writeOff->reverted_at->format('Y-m-d H:i:s') : '',
                    'comment' => $writeOff->revert_reason,
                ];
            }

            return json_success('获取成功', $data);

        } catch (\Exception $e) {
            \Log::error('获取核销详情失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('获取详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出待核销列表
     *
     * 请求参数:
     * - paymentNo(string, optional)：到款单号，模糊匹配
     *
     * 返回参数:
     * - 文件下载：Excel（xlsx）
     *
     * 功能:
     * - 导出筛选后的待核销到款列表为Excel文件
     * - 接口: GET /write-offs/export/pending
     */
    public function exportPending(Request $request)
    {
        try {
            $query = PaymentReceived::with(['customer.techLead', 'contract', 'claimer', 'paymentRequests'])
                ->whereNull('deleted_at')
                ->where('status', 3);

            // 应用搜索条件
            if ($request->filled('paymentNo')) {
                $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
            }

            $list = $query->orderBy('received_date', 'desc')->get();

            $data = $list->map(function($item) {
                $usedAmount = WriteOff::where('payment_received_id', $item->id)
                    ->where('status', 1)
                    ->sum('write_off_amount');
                $remainingAmount = $item->amount - $usedAmount;

                return [
                    '到款单号' => $item->payment_no,
                    '客户名称' => $item->customer ? $item->customer->customer_name : '',
                    '技术主导' => $item->customer && $item->customer->techLead ? $item->customer->techLead->real_name : '',
                    '合同号' => $item->contract ? $item->contract->contract_no : '',
                    '到款总额' => $item->amount,
                    '已核销金额' => $usedAmount,
                    '待核销金额' => $remainingAmount,
                    '到账时间' => $item->received_date ? $item->received_date->format('Y-m-d') : '',
                    '认领人' => $item->claimer ? $item->claimer->real_name : '',
                ];
            });

            $excel = new \Rap2hpoutre\FastExcel\FastExcel($data);
            $fileName = '待核销列表_' . date('YmdHis') . '.xlsx';

            return $excel->download($fileName);

        } catch (\Exception $e) {
            \Log::error('导出待核销列表失败: ' . $e->getMessage());
            return json_fail('导出失败');
        }
    }

    /**
     * 导出核销明细
     *
     * 请求参数:
     * - writeOffNo(string, optional)：核销单号，模糊匹配
     *
     * 返回参数:
     * - 文件下载：Excel（xlsx）
     *
     * 功能:
     * - 导出核销明细列表为Excel文件
     * - 接口: GET /write-offs/export/completed
     */
    public function exportCompleted(Request $request)
    {
        try {
            $query = WriteOff::with([
                'paymentReceived',
                'paymentRequest',
                'customer.techLead',
                'contract',
                'writeOffUser'
            ])->whereNull('deleted_at');

            // 应用搜索条件
            if ($request->filled('writeOffNo')) {
                $query->where('write_off_no', 'like', '%' . $request->writeOffNo . '%');
            }

            $list = $query->orderBy('write_off_date', 'desc')->get();

            $data = $list->map(function($item) {
                return [
                    '核销单号' => $item->write_off_no,
                    '到款单号' => $item->paymentReceived ? $item->paymentReceived->payment_no : '',
                    '关联请款单' => $item->paymentRequest ? $item->paymentRequest->request_no : '',
                    '客户名称' => $item->customer ? $item->customer->customer_name : '',
                    '技术主导' => $item->customer && $item->customer->techLead ? $item->customer->techLead->real_name : '',
                    '合同号' => $item->contract ? $item->contract->contract_no : '',
                    '核销金额' => $item->write_off_amount,
                    '核销日期' => $item->write_off_date ? $item->write_off_date->format('Y-m-d') : '',
                    '核销人' => $item->writeOffUser ? $item->writeOffUser->real_name : '',
                    '状态' => $item->status == 1 ? '已完成' : '已撤销',
                    '备注' => $item->remark,
                ];
            });

            $excel = new \Rap2hpoutre\FastExcel\FastExcel($data);
            $fileName = '核销明细_' . date('YmdHis') . '.xlsx';

            return $excel->download($fileName);

        } catch (\Exception $e) {
            \Log::error('导出核销明细失败: ' . $e->getMessage());
            return json_fail('导出失败');
        }
    }
}
