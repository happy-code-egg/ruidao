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
 * 获取到款单列表 index
 **
 * 功能描述：获取到款单列表数据，支持多种筛选条件和分页
 *
 * 传入参数：
 * - status (int, optional): 状态筛选 (1-草稿, 2-待认领, 3-已认领, 4-已核销)
 * - paymentNo (string, optional): 到款单号搜索关键词
 * - payer (string, optional): 付款方搜索关键词
 * - customerName (string, optional): 客户名称搜索关键词
 * - contractNo (string, optional): 合同号搜索关键词
 * - startDate (string, optional): 到款开始日期 (格式: YYYY-MM-DD)
 * - endDate (string, optional): 到款结束日期 (格式: YYYY-MM-DD)
 * - minAmount (float, optional): 最小金额
 * - maxAmount (float, optional): 最大金额
 * - claimedBy (string, optional): 认领人搜索关键词
 * - page (int, optional): 页码，默认为1
 * - pageSize (int, optional): 每页数量，默认为10
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 到款单列表数据
 *     - id (int): 到款单ID
 *     - paymentNo (string): 到款单号
 *     - customerName (string): 客户名称
 *     - contractNo (string): 合同号
 *     - amount (float): 到款金额
 *     - claimedAmount (float): 已认领金额
 *     - unclaimedAmount (float): 未认领金额
 *     - currency (string): 币种
 *     - payer (string): 付款方
 *     - payerAccount (string): 付款账号
 *     - bankAccount (string): 收款账号
 *     - paymentMethod (string): 付款方式
 *     - transactionRef (string): 交易流水号
 *     - receivedDate (string): 到账日期
 *     - status (int): 状态
 *     - statusText (string): 状态文本
 *     - creator (string): 创建人
 *     - claimer (string): 认领人
 *     - claimedAt (string): 认领时间
 *     - createdAt (string): 创建时间
 *     - remark (string): 备注
 *     - requestNos (array): 关联的请款单号数组
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - pageSize (int): 每页数量
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器，预加载关联关系
        $query = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
            ->whereNull('deleted_at');

        // 状态筛选条件
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 到款单号搜索条件
        if ($request->filled('paymentNo')) {
            $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
        }

        // 付款方搜索条件
        if ($request->filled('payer')) {
            $query->where('payer', 'like', '%' . $request->payer . '%');
        }

        // 客户名称搜索条件
        if ($request->filled('customerName')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customerName . '%');
            });
        }

        // 合同号搜索条件
        if ($request->filled('contractNo')) {
            $query->whereHas('contract', function ($q) use ($request) {
                $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
            });
        }

        // 到款日期范围筛选条件
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('received_date', [$request->startDate, $request->endDate]);
        }

        // 金额范围筛选条件
        if ($request->filled('minAmount')) {
            $query->where('amount', '>=', $request->minAmount);
        }
        if ($request->filled('maxAmount')) {
            $query->where('amount', '<=', $request->maxAmount);
        }

        // 认领人搜索条件
        if ($request->filled('claimedBy')) {
            $query->whereHas('claimer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->claimedBy . '%');
            });
        }

        // 按创建时间倒序排序
        $query->orderBy('created_at', 'desc');

        // 分页参数处理
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询
        $list = $query->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();

        // 格式化返回数据
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

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $list,
            'total' => $total,
            'page' => (int)$page,
            'pageSize' => (int)$pageSize,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        \Log::error('获取到款单列表失败: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_fail('获取列表失败');
    }
}


   /**
 * 获取到款单统计数据 statistics
 *
 * 功能描述：获取到款单的各种状态统计信息和总金额
 *
 * 传入参数：
 * - customerName (string, optional): 客户名称搜索关键词
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 统计数据
 *   - total (int): 到款单总数量
 *   - draft (int): 草稿状态(1)的到款单数量
 *   - unclaimed (int): 待认领状态(2)的到款单数量
 *   - claimed (int): 已认领状态(3)的到款单数量
 *   - writtenOff (int): 已核销状态(4)的到款单数量
 *   - totalAmount (string): 到款单总金额，格式化为保留两位小数的字符串
 */
public function statistics(Request $request)
{
    try {
        // 初始化查询构建器，排除已删除的记录
        $query = PaymentReceived::whereNull('deleted_at');

        // 如果提供了客户名称，则添加客户名称筛选条件
        if ($request->filled('customerName')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customerName . '%');
            });
        }

        // 统计各种状态的到款单数量
        $total = $query->count();  // 总数量
        $draft = (clone $query)->where('status', 1)->count();      // 草稿状态数量
        $unclaimed = (clone $query)->where('status', 2)->count();  // 待认领状态数量
        $claimed = (clone $query)->where('status', 3)->count();    // 已认领状态数量
        $writtenOff = (clone $query)->where('status', 4)->count(); // 已核销状态数量

        // 计算到款单总金额
        $totalAmount = $query->sum('amount');

        // 返回成功响应，包含统计数据
        return json_success('获取统计成功', [
            'total' => $total,
            'draft' => $draft,
            'unclaimed' => $unclaimed,
            'claimed' => $claimed,
            'writtenOff' => $writtenOff,
            'totalAmount' => number_format($totalAmount, 2, '.', ''), // 格式化总金额为2位小数
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('获取统计数据失败: ' . $e->getMessage());
        return json_fail('获取统计失败');
    }
}


   /**
 * 获取到款单详情 show
 *
 * 功能描述：根据ID获取到款单的详细信息，包括关联的客户、合同、创建人、认领人和请款单信息
 *
 * 传入参数：
 * - id (int): 到款单ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 到款单详细信息
 *   - id (int): 到款单ID
 *   - paymentNo (string): 到款单号
 *   - customerId (int): 客户ID
 *   - customerName (string): 客户名称
 *   - contractId (int): 合同ID
 *   - contractNo (string): 合同号
 *   - amount (float): 到款金额
 *   - claimedAmount (float): 已认领金额
 *   - unclaimedAmount (float): 未认领金额
 *   - currency (string): 币种
 *   - payer (string): 付款方
 *   - payerAccount (string): 付款账号
 *   - bankAccount (string): 收款账号
 *   - paymentMethod (string): 付款方式
 *   - transactionRef (string): 交易流水号
 *   - receivedDate (string): 到账日期
 *   - status (int): 状态
 *   - statusText (string): 状态文本
 *   - creator (string): 创建人
 *   - claimer (string): 认领人
 *   - claimedAt (string): 认领时间
 *   - createdAt (string): 创建时间
 *   - remark (string): 备注
 *   - paymentRequests (array): 关联的请款单列表
 *     - id (int): 请款单ID
 *     - requestNo (string): 请款单号
 *     - amount (float): 请款单金额
 *     - allocatedAmount (float): 分配金额
 */
public function show($id)
{
    try {
        // 根据ID查找到款单，并预加载关联关系
        $paymentReceived = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
            ->findOrFail($id);

        // 构造返回数据
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
            // 格式化关联的请款单信息
            'paymentRequests' => $paymentReceived->paymentRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'requestNo' => $request->request_no,
                    'amount' => $request->total_amount,
                    'allocatedAmount' => $request->pivot->allocated_amount,
                ];
            }),
        ];

        // 返回成功响应
        return json_success('获取详情成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('获取到款单详情失败: ' . $e->getMessage());
        return json_fail('获取详情失败');
    }
}


  /**
 * 创建到款单(草稿) store
 *
 * 功能描述：创建一个新的到款单，初始状态为草稿
 *
 * 传入参数：
 * - amount (float): 到款金额，必须大于等于0
 * - currency (string): 币种，必填
 * - payer (string): 付款方，必填
 * - received_date (date): 到账日期，必填
 * - bank_account (string, optional): 收款账号
 * - payment_method (string, optional): 付款方式
 * - remark (string, optional): 备注
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建结果
 *   - id (int): 新创建的到款单ID
 */
public function store(Request $request)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'payer' => 'required|string',
            'received_date' => 'required|date',
            'bank_account' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        // 如果验证失败，返回错误信息
        if ($validator->fails()) {
            return json_fail('验证失败', $validator->errors());
        }

        // 生成到款单号
        $paymentNo = $this->generatePaymentNo();

        // 创建到款单，初始状态为草稿(1)
        $paymentReceived = PaymentReceived::create([
            'payment_no' => $paymentNo,
            'status' => 1, // 1-草稿
            'amount' => $request->amount,
            'unclaimed_amount' => $request->amount, // 初始未认领金额等于到款金额
            'currency' => $request->currency,
            'payer' => $request->payer,
            'payer_account' => $request->payer_account,
            'bank_account' => $request->bank_account,
            'payment_method' => $request->payment_method,
            'transaction_ref' => $request->transaction_ref,
            'received_date' => $request->received_date,
            'remark' => $request->remark,
            'created_by' => Auth::id(), // 记录创建人
        ]);

        // 返回成功响应，包含新创建的到款单ID
        return json_success('创建成功', ['id' => $paymentReceived->id]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('创建到款单失败: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_fail('创建失败');
    }
}


   /**
 * 更新到款单 update
 *
 * 功能描述：更新草稿状态的到款单信息
 *
 * 传入参数：
 * - id (int): 到款单ID
 * - amount (float): 到款金额，必须大于等于0
 * - currency (string): 币种，必填
 * - payer (string): 付款方，必填
 * - received_date (date): 到账日期，必填
 * - bank_account (string, optional): 收款账号
 * - payment_method (string, optional): 付款方式
 * - transaction_ref (string, optional): 交易流水号
 * - remark (string, optional): 备注
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找要更新的到款单
        $paymentReceived = PaymentReceived::findOrFail($id);

        // 只有草稿状态才能编辑，其他状态不允许更新
        if ($paymentReceived->status != 1) {
            return json_fail('只有草稿状态的到款单才能编辑');
        }

        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'payer' => 'required|string',
            'received_date' => 'required|date',
        ]);

        // 如果验证失败，返回错误信息
        if ($validator->fails()) {
            return json_fail('验证失败', $validator->errors());
        }

        // 更新到款单信息
        $paymentReceived->update([
            'amount' => $request->amount,
            // 重新计算未认领金额：新的到款金额减去已认领金额
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

        // 返回成功响应
        return json_success('更新成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('更新到款单失败: ' . $e->getMessage());
        return json_fail('更新失败');
    }
}


  /**
 * 删除到款单 destroy
 *
 * 功能描述：删除草稿状态的到款单
 *
 * 传入参数：
 * - id (int): 到款单ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找要删除的到款单
        $paymentReceived = PaymentReceived::findOrFail($id);

        // 只有草稿状态才能删除，其他状态不允许删除
        if ($paymentReceived->status != 1) {
            return json_fail('只有草稿状态的到款单才能删除');
        }

        // 执行删除操作
        $paymentReceived->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('删除到款单失败: ' . $e->getMessage());
        return json_fail('删除失败');
    }
}


  /**
 * 提交到款单(草稿->待认领) submit
 *
 * 功能描述：将草稿状态的到款单提交为待认领状态
 *
 * 传入参数：
 * - id (int): 到款单ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function submit($id)
{
    try {
        // 根据ID查找要提交的到款单
        $paymentReceived = PaymentReceived::findOrFail($id);

        // 只有草稿状态才能提交，其他状态不允许提交
        if ($paymentReceived->status != 1) {
            return json_fail('只有草稿状态的到款单才能提交');
        }

        // 更新到款单状态为待认领(2)
        $paymentReceived->update([
            'status' => 2, // 2-待认领
        ]);

        // 返回成功响应
        return json_success('提交成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('提交到款单失败: ' . $e->getMessage());
        return json_fail('提交失败');
    }
}


 /**
 * 认领到款单 claim
 *
 * 功能描述：将待认领状态的到款单认领给指定客户和请款单，更新为已认领状态
 *
 * 传入参数：
 * - id (int): 到款单ID
 * - customer_id (int): 客户ID，必须存在
 * - request_ids (array): 请款单ID数组，至少包含一个ID
 * - request_ids.* (int): 请款单ID，必须存在
 * - contract_id (int, optional): 合同ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function claim(Request $request, $id)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'exists:payment_requests,id',
        ]);

        // 如果验证失败，返回错误信息
        if ($validator->fails()) {
            return json_fail('验证失败', $validator->errors());
        }

        // 根据ID查找要认领的到款单
        $paymentReceived = PaymentReceived::findOrFail($id);

        // 只有待认领状态才能认领，其他状态不允许认领
        if ($paymentReceived->status != 2) {
            return json_fail('只有待认领状态的到款单才能认领');
        }

        // 开启事务处理，确保数据一致性
        DB::beginTransaction();

        // 获取请款单信息并验证
        $paymentRequests = PaymentRequest::whereIn('id', $request->request_ids)->get();

        // 验证所有请款单都存在
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

        // 更新到款单信息
        $paymentReceived->update([
            'customer_id' => $request->customer_id,
            'contract_id' => $request->contract_id,
            'status' => 3, // 3-已认领
            'claimed_amount' => $totalRequestAmount,
            'unclaimed_amount' => $paymentReceived->amount - $totalRequestAmount,
            'claimed_by' => Auth::id(), // 记录认领人
            'claimed_at' => now(), // 记录认领时间
        ]);

        // 关联请款单，建立多对多关系
        $syncData = [];
        foreach ($paymentRequests as $paymentRequest) {
            $syncData[$paymentRequest->id] = [
                'allocated_amount' => $paymentRequest->total_amount, // 分配金额等于请款单总额
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $paymentReceived->paymentRequests()->sync($syncData);

        // 提交事务
        DB::commit();

        // 返回成功响应
        return json_success('认领成功');

    } catch (\Exception $e) {
        // 回滚事务
        DB::rollBack();
        // 记录错误日志并返回失败响应
        \Log::error('认领到款单失败: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_fail('认领失败');
    }
}


   /**
 * 导出 export
 *
 * 功能描述：根据筛选条件导出到款单列表数据为Excel文件
 *
 * 传入参数：
 * - status (int, optional): 状态筛选 (1-草稿, 2-待认领, 3-已认领, 4-已核销)
 * - paymentNo (string, optional): 到款单号搜索关键词
 * - payer (string, optional): 付款方搜索关键词
 * - customerName (string, optional): 客户名称搜索关键词
 * - contractNo (string, optional): 合同号搜索关键词
 * - startDate (string, optional): 到款开始日期 (格式: YYYY-MM-DD)
 * - endDate (string, optional): 到款结束日期 (格式: YYYY-MM-DD)
 * - minAmount (float, optional): 最小金额
 * - maxAmount (float, optional): 最大金额
 * - claimedBy (string, optional): 认领人搜索关键词
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (file): Excel文件下载
 */
public function export(Request $request)
{
    try {
        // 初始化查询构建器，预加载关联关系
        $query = PaymentReceived::with(['customer', 'contract', 'creator', 'claimer', 'paymentRequests'])
            ->whereNull('deleted_at');

        // 状态筛选条件
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 到款单号搜索条件
        if ($request->filled('paymentNo')) {
            $query->where('payment_no', 'like', '%' . $request->paymentNo . '%');
        }

        // 付款方搜索条件
        if ($request->filled('payer')) {
            $query->where('payer', 'like', '%' . $request->payer . '%');
        }

        // 客户名称搜索条件
        if ($request->filled('customerName')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customerName . '%');
            });
        }

        // 合同号搜索条件
        if ($request->filled('contractNo')) {
            $query->whereHas('contract', function ($q) use ($request) {
                $q->where('contract_no', 'like', '%' . $request->contractNo . '%');
            });
        }

        // 到款日期范围筛选条件
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('received_date', [$request->startDate, $request->endDate]);
        }

        // 金额范围筛选条件
        if ($request->filled('minAmount')) {
            $query->where('amount', '>=', $request->minAmount);
        }
        if ($request->filled('maxAmount')) {
            $query->where('amount', '<=', $request->maxAmount);
        }

        // 认领人搜索条件
        if ($request->filled('claimedBy')) {
            $query->whereHas('claimer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->claimedBy . '%');
            });
        }

        // 按创建时间倒序排序
        $query->orderBy('created_at', 'desc');

        // 获取所有匹配的数据
        $list = $query->get();

        // 准备导出数据
        $exportData = [];
        // 添加表头
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

        // 添加数据行
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

        // 生成文件名和文件路径
        $fileName = '到款单列表_' . date('YmdHis') . '.xlsx';
        $filePath = storage_path('app/public/exports/' . $fileName);

        // 确保导出目录存在
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // 使用 FastExcel 导出Excel文件
        $fastExcel = new \Rap2hpoutre\FastExcel\FastExcel($exportData);
        $fastExcel->export($filePath);

        // 返回文件下载响应，并在发送后删除文件
        return response()->download($filePath)->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('导出失败: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return json_fail('导出失败');
    }
}


   /**
 * 生成到款单号 generatePaymentNo
 *
 * 功能描述：生成唯一的到款单号，格式为DK+日期+4位序号
 *
 * 输出参数：
 * - string: 生成的到款单号，格式如 DK202312010001
 */
private function generatePaymentNo()
{
    // 设置到款单号前缀
    $prefix = 'DK';
    // 获取当前日期
    $date = date('Ymd');

    // 查找同一天内最后一条记录，按到款单号倒序排列
    $lastPayment = PaymentReceived::where('payment_no', 'like', $prefix . $date . '%')
        ->orderBy('payment_no', 'desc')
        ->first();

    // 如果存在同天记录，则在最后一条记录序号基础上加1；否则从1开始
    if ($lastPayment) {
        $lastNumber = intval(substr($lastPayment->payment_no, -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    // 拼接并返回到款单号，序号部分补零至4位
    return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * 获取状态文本 getStatusText
 *
 * 功能描述：根据状态码返回对应的状态文本描述
 *
 * 传入参数：
 * - status (int): 状态码 (1-草稿, 2-待认领, 3-已认领, 4-已核销)
 *
 * 输出参数：
 * - string: 状态文本描述
 */
private function getStatusText($status)
{
    // 定义状态码与文本的映射关系
    $texts = [
        1 => '草稿',
        2 => '待认领',
        3 => '已认领',
        4 => '已核销',
    ];

    // 返回对应状态文本，如果未找到则返回原状态码
    return $texts[$status] ?? $status;
}

}

