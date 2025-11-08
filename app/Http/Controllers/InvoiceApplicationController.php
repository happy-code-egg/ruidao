<?php

namespace App\Http\Controllers;

use App\Models\InvoiceApplication;
use App\Models\InvoiceApplicationHistory;
use App\Models\Customer;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 发票申请控制器
 * 处理发票申请的创建、查询、更新、删除、审批等操作
 */
class InvoiceApplicationController extends Controller
{
    /**
     * 获取发票申请列表
     * @param Request $request 包含搜索条件的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的列表数据，包含分页信息
     */
    public function index(Request $request)
    {
        try {
            $query = InvoiceApplication::query();

            // 搜索条件
            if ($request->filled('applicationNo')) {
                $query->where('application_no', 'like', '%' . $request->applicationNo . '%');
            }

            if ($request->filled('applicant')) {
                $query->where('applicant', 'like', '%' . $request->applicant . '%');
            }

            if ($request->filled('clientName')) {
                $query->where('customer_name', 'like', '%' . $request->clientName . '%');
            }

            if ($request->filled('contractName')) {
                $query->where('contract_name', 'like', '%' . $request->contractName . '%');
            }

            if ($request->filled('invoiceType')) {
                $query->where('invoice_type', $request->invoiceType);
            }

            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            if ($request->filled('flowStatus')) {
                $query->where('flow_status', $request->flowStatus);
            }

            if ($request->filled('amountMin')) {
                $query->where('invoice_amount', '>=', $request->amountMin);
            }

            if ($request->filled('amountMax')) {
                $query->where('invoice_amount', '<=', $request->amountMax);
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 分页
            $pageSize = $request->input('pageSize', 15);
            $page = $request->input('page', 1);

            $total = $query->count();
            $list = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get()
                ->map(function ($item) {
                    return $this->formatInvoiceData($item);
                });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'total' => $total,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票申请列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票申请详情
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的发票申请详情数据
     */
    public function show($id)
    {
        try {
            $invoice = InvoiceApplication::with(['customer', 'contract'])->findOrFail($id);

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $this->formatInvoiceData($invoice)
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票申请详情失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 创建发票申请
     * @param Request $request 包含发票申请信息的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的创建结果，包含新建的发票申请数据
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            // 转换前端camelCase字段为snake_case
            $data = $this->convertToSnakeCase($data);

            // 生成申请单号
            if (empty($data['application_no'])) {
                $data['application_no'] = InvoiceApplication::generateApplicationNo();
            }

            // 设置必填字段默认值
            $data['created_by'] = Auth::id() ?? 1;
            $data['flow_status'] = 'reviewing'; // 提交后进入审核中状态

            // 确保申请日期有值
            if (empty($data['application_date']) || $data['application_date'] === 'null') {
                $data['application_date'] = date('Y-m-d');
            }

            // 确保申请人有值
            if (empty($data['applicant'])) {
                $data['applicant'] = Auth::user()->name ?? '系统';
            }

            // 确保部门有值
            if (empty($data['department'])) {
                $data['department'] = 'info_management';
            }

            // 确保发票类型有值
            if (empty($data['invoice_type'])) {
                $data['invoice_type'] = 'special';
            }

            // 确保开票金额有值
            if (!isset($data['invoice_amount'])) {
                $data['invoice_amount'] = 0;
            }

            $invoice = InvoiceApplication::create($data);

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '申请提交',
                'handler' => $data['applicant'] ?? 'System',
                'action' => 'submit',
                'comment' => '提交开票申请',
                'type' => 'primary',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '创建成功',
                'data' => $this->formatInvoiceData($invoice)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('创建发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '创建失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 更新发票申请
     * @param Request $request 包含更新信息的请求对象
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的更新结果，包含更新后的发票申请数据
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = InvoiceApplication::findOrFail($id);
            $data = $request->all();

            // 转换前端camelCase字段为snake_case
            $data = $this->convertToSnakeCase($data);

            // 设置更新人
            $data['updated_by'] = Auth::id() ?? 1;

            // 确保申请日期有值
            if (empty($data['application_date']) || $data['application_date'] === 'null') {
                $data['application_date'] = $invoice->application_date ?? date('Y-m-d');
            }

            $invoice->update($data);

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '申请更新',
                'handler' => $data['applicant'] ?? 'System',
                'action' => 'update',
                'comment' => '更新开票申请信息',
                'type' => 'info',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '更新成功',
                'data' => $this->formatInvoiceData($invoice->fresh())
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '更新失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 删除发票申请
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的删除结果
     */
    public function destroy($id)
    {
        try {
            $invoice = InvoiceApplication::findOrFail($id);
            
            // 只有草稿状态才能删除
            if ($invoice->flow_status !== 'draft') {
                return response()->json([
                    'code' => 1,
                    'msg' => '只有草稿状态的申请才能删除',
                    'data' => null
                ], 400);
            }

            $invoice->delete();

            return response()->json([
                'code' => 0,
                'msg' => '删除成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('删除发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '删除失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 保存草稿
     * @param Request $request 包含发票申请草稿信息的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的保存结果，包含保存的草稿数据
     */
    public function saveDraft(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            // 转换前端camelCase字段为snake_case
            $data = $this->convertToSnakeCase($data);

            // 生成申请单号
            if (empty($data['application_no'])) {
                $data['application_no'] = InvoiceApplication::generateApplicationNo();
            }

            // 设置创建人和状态
            $data['created_by'] = Auth::id() ?? 1;
            $data['flow_status'] = 'draft';

            // 确保申请日期有值
            if (empty($data['application_date']) || $data['application_date'] === 'null') {
                $data['application_date'] = date('Y-m-d');
            }

            // 确保申请人有值
            if (empty($data['applicant'])) {
                $data['applicant'] = Auth::user()->name ?? '系统';
            }

            // 确保部门有值
            if (empty($data['department'])) {
                $data['department'] = 'info_management';
            }

            // 确保发票类型有值
            if (empty($data['invoice_type'])) {
                $data['invoice_type'] = 'special';
            }

            // 确保开票金额有值
            if (!isset($data['invoice_amount'])) {
                $data['invoice_amount'] = 0;
            }

            $invoice = InvoiceApplication::create($data);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '草稿保存成功',
                'data' => $this->formatInvoiceData($invoice)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('保存草稿失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '保存失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取统计数据
     * @param Request $request 包含查询条件的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的统计数据
     */
    public function statistics(Request $request)
    {
        try {
            $query = InvoiceApplication::query();

            // 应用搜索条件
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            $contractTotal = $query->sum(DB::raw('(SELECT SUM(contract_amount) FROM contracts WHERE contracts.id = invoice_applications.contract_id)')) ?? 0;
            $actualPaymentTotal = $query->sum(DB::raw('(SELECT SUM(actual_payment) FROM contracts WHERE contracts.id = invoice_applications.contract_id)')) ?? 0;
            $invoicedTotal = $query->sum('invoice_amount') ?? 0;
            $uninvoicedAmount = max($contractTotal, $actualPaymentTotal) - $invoicedTotal;

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'contractTotal' => $contractTotal,
                    'actualPaymentTotal' => $actualPaymentTotal,
                    'invoicedTotal' => $invoicedTotal,
                    'uninvoicedAmount' => max(0, $uninvoicedAmount),
                    'applicationCount' => $query->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取统计数据失败: ' . $e->getMessage());
            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'contractTotal' => 0,
                    'actualPaymentTotal' => 0,
                    'invoicedTotal' => 0,
                    'uninvoicedAmount' => 0,
                    'applicationCount' => 0,
                ]
            ]);
        }
    }

    /**
     * 获取待处理列表
     * @param Request $request 包含查询条件的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的待处理发票申请列表
     */
    public function pending(Request $request)
    {
        try {
            $query = InvoiceApplication::query()
                ->whereIn('flow_status', ['reviewing', 'approved']);

            // 搜索条件
            if ($request->filled('applicationNo')) {
                $query->where('application_no', 'like', '%' . $request->applicationNo . '%');
            }

            if ($request->filled('applicant')) {
                $query->where('applicant', 'like', '%' . $request->applicant . '%');
            }

            if ($request->filled('clientName')) {
                $query->where('customer_name', 'like', '%' . $request->clientName . '%');
            }

            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }

            if ($request->filled('invoiceType')) {
                $query->where('invoice_type', $request->invoiceType);
            }

            if ($request->filled('flowStatus')) {
                $query->where('flow_status', $request->flowStatus);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 分页
            $pageSize = $request->input('pageSize', 15);
            $page = $request->input('page', 1);

            $total = $query->count();
            $list = $query->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get()
                ->map(function ($item) {
                    return $this->formatInvoiceData($item);
                });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'total' => $total,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取待处理列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取待处理统计
     */
    public function pendingStats()
    {
        try {
            $reviewing = InvoiceApplication::where('flow_status', 'reviewing')->count();
            $urgent = InvoiceApplication::where('priority', 'urgent')
                ->whereIn('flow_status', ['reviewing', 'approved'])
                ->count();
            $overdue = 0; // 可以根据实际业务逻辑计算
            $today = InvoiceApplication::whereDate('application_date', today())->count();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'reviewing' => $reviewing,
                    'urgent' => $urgent,
                    'overdue' => $overdue,
                    'today' => $today,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取待处理统计失败: ' . $e->getMessage());
            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'reviewing' => 0,
                    'urgent' => 0,
                    'overdue' => 0,
                    'today' => 0,
                ]
            ]);
        }
    }

    /**
     * 审批通过
     * @param Request $request 包含审批信息的请求对象
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的审批结果
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = InvoiceApplication::findOrFail($id);
            $invoice->flow_status = 'approved';
            $invoice->approval_comment = $request->input('comment');
            $invoice->approved_by = Auth::id() ?? 1;
            $invoice->approved_at = now();
            $invoice->save();

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '审批通过',
                'handler' => Auth::user()->name ?? 'System',
                'action' => 'approve',
                'comment' => $request->input('comment', '审批通过'),
                'type' => 'success',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '审批成功',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('审批失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '审批失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 审批退回
     * @param Request $request 包含拒绝信息的请求对象
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的拒绝结果
     */
    public function reject(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = InvoiceApplication::findOrFail($id);
            $invoice->flow_status = 'rejected';
            $invoice->approval_comment = $request->input('comment');
            $invoice->save();

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '审批退回',
                'handler' => Auth::user()->name ?? 'System',
                'action' => 'reject',
                'comment' => $request->input('comment', '审批退回'),
                'type' => 'danger',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '退回成功',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('退回失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '退回失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 转交发票申请
     * @param Request $request 包含转交信息的请求对象
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的转交结果
     */
    public function transfer(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = InvoiceApplication::findOrFail($id);
            $invoice->current_handler = $request->input('transferTo');
            $invoice->save();

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '转办处理',
                'handler' => Auth::user()->name ?? 'System',
                'action' => 'transfer',
                'comment' => $request->input('comment', '转办给 ' . $request->input('transferTo')),
                'type' => 'warning',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '转办成功',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('转办失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '转办失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 批量审批通过
     */
    public function batchApprove(Request $request)
    {
        try {
            DB::beginTransaction();

            $ids = $request->input('ids', []);
            $comment = $request->input('comment', '批量审批通过');

            foreach ($ids as $id) {
                $invoice = InvoiceApplication::find($id);
                if ($invoice) {
                    $invoice->flow_status = 'approved';
                    $invoice->approval_comment = $comment;
                    $invoice->approved_by = Auth::id() ?? 1;
                    $invoice->approved_at = now();
                    $invoice->save();

                    // 记录历史
                    InvoiceApplicationHistory::create([
                        'invoice_application_id' => $invoice->id,
                        'title' => '批量审批通过',
                        'handler' => Auth::user()->name ?? 'System',
                        'action' => 'approve',
                        'comment' => $comment,
                        'type' => 'success',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '批量审批成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('批量审批失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '批量审批失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 批量退回
     */
    public function batchReject(Request $request)
    {
        try {
            DB::beginTransaction();

            $ids = $request->input('ids', []);
            $comment = $request->input('comment', '批量退回');

            foreach ($ids as $id) {
                $invoice = InvoiceApplication::find($id);
                if ($invoice) {
                    $invoice->flow_status = 'rejected';
                    $invoice->approval_comment = $comment;
                    $invoice->save();

                    // 记录历史
                    InvoiceApplicationHistory::create([
                        'invoice_application_id' => $invoice->id,
                        'title' => '批量退回',
                        'handler' => Auth::user()->name ?? 'System',
                        'action' => 'reject',
                        'comment' => $comment,
                        'type' => 'danger',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '批量退回成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('批量退回失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '批量退回失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 提交发票上传信息
     */
    public function submitUpload(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = InvoiceApplication::findOrFail($id);
            $invoice->invoice_number = $request->input('invoiceNumber');
            $invoice->invoice_date = $request->input('invoiceDate');
            $invoice->invoice_files = $request->input('files');
            $invoice->upload_remark = $request->input('remark');
            $invoice->flow_status = 'completed';
            $invoice->save();

            // 记录历史
            InvoiceApplicationHistory::create([
                'invoice_application_id' => $invoice->id,
                'title' => '发票上传',
                'handler' => Auth::user()->name ?? 'System',
                'action' => 'upload',
                'comment' => '上传发票文件，发票号码：' . $request->input('invoiceNumber'),
                'type' => 'success',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '上传成功',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('上传发票失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '上传失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取流程历史
     */
    public function history($id)
    {
        try {
            $history = InvoiceApplicationHistory::where('invoice_application_id', $id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'title' => $item->title,
                        'handler' => $item->handler,
                        'timestamp' => $item->timestamp,
                        'type' => $item->type,
                        'comment' => $item->comment,
                    ];
                });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('获取流程历史失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取客户开票信息
     */
    public function getCustomerInvoiceInfo($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);

            // 组合地址和电话信息
            $addressPhone = trim(($customer->invoice_address ?? $customer->address ?? '') . ' ' . ($customer->invoice_phone ?? $customer->contact_phone ?? ''));
            // 组合银行和账号信息
            $bankAccount = trim(($customer->bank_name ?? '') . ' ' . ($customer->bank_account ?? ''));

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'customerNo' => $customer->customer_no ?? '',
                    'name' => $customer->name ?? $customer->customer_name ?? '',
                    'buyerName' => $customer->name ?? $customer->customer_name ?? '',
                    'taxId' => $customer->invoice_credit_code ?? $customer->credit_code ?? '',
                    'invoiceCreditCode' => $customer->invoice_credit_code ?? $customer->credit_code ?? '',
                    'address' => $addressPhone,
                    'invoiceAddress' => $addressPhone,
                    'bankName' => $customer->bank_name ?? '',
                    'bankAccount' => $bankAccount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取客户开票信息失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取客户合同列表
     */
    public function getCustomerContracts($customerId = null)
    {
        try {
            $query = Contract::query();

            if ($customerId) {
                $query->where('customer_id', $customerId);
            }

            $contracts = $query->get()->map(function ($contract) {
                // 计算已开票金额
                $invoicedAmount = InvoiceApplication::where('contract_id', $contract->id)
                    ->whereIn('flow_status', ['approved', 'completed'])
                    ->sum('invoice_amount');

                return [
                    'id' => $contract->id,
                    'contractName' => $contract->contract_name ?? '',
                    'contractNo' => $contract->contract_no ?? '',
                    'contractAmount' => $contract->total_amount ?? 0,
                    'actualPayment' => 0, // Contract模型没有actual_payment字段
                    'invoicedAmount' => $invoicedAmount,
                ];
            });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $contracts
            ]);
        } catch (\Exception $e) {
            Log::error('获取合同列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取合同开票信息
     */
    public function getContractInvoiceInfo($contractId)
    {
        try {
            $contract = Contract::findOrFail($contractId);

            // 计算已开票金额
            $invoicedAmount = InvoiceApplication::where('contract_id', $contractId)
                ->whereIn('flow_status', ['approved', 'completed'])
                ->sum('invoice_amount');

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'contractNo' => $contract->contract_no ?? '',
                    'contractAmount' => $contract->total_amount ?? 0,
                    'actualPayment' => 0, // Contract模型没有actual_payment字段，暂时返回0
                    'invoicedAmount' => $invoicedAmount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取合同开票信息失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取客户列表
     */
    public function getCustomerList()
    {
        try {
            $customers = Customer::select('id', 'name', 'customer_no', 'credit_code', 'address', 'bank_name', 'bank_account')
                ->get();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            Log::error('获取客户列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 转换前端camelCase字段为snake_case
     */
    private function convertToSnakeCase($data)
    {
        $converted = [];
        $fieldMap = [
            'applicationNo' => 'application_no',
            'applicationDate' => 'application_date',
            'applicant' => 'applicant',
            'department' => 'department',
            'customerId' => 'customer_id',
            'customerName' => 'customer_name',
            'customerNo' => 'customer_no',
            'contractId' => 'contract_id',
            'contractName' => 'contract_name',
            'contractNo' => 'contract_no',
            'buyerName' => 'buyer_name',
            'buyerTaxId' => 'buyer_tax_id',
            'buyerAddress' => 'buyer_address',
            'buyerBankAccount' => 'buyer_bank_account',
            'invoiceType' => 'invoice_type',
            'invoiceAmount' => 'invoice_amount',
            'items' => 'items',
            'remark' => 'remark',
            'flowStatus' => 'flow_status',
            'currentHandler' => 'current_handler',
            'priority' => 'priority',
            'invoiceNumber' => 'invoice_number',
            'invoiceDate' => 'invoice_date',
            'invoiceFiles' => 'invoice_files',
            'uploadRemark' => 'upload_remark',
            'approvalComment' => 'approval_comment',
        ];

        foreach ($data as $key => $value) {
            $snakeKey = $fieldMap[$key] ?? $key;
            $converted[$snakeKey] = $value;
        }

        return $converted;
    }

    /**
     * 格式化发票数据为前端需要的格式(驼峰命名)
     */
    private function formatInvoiceData($invoice)
    {
        return [
            'id' => $invoice->id,
            'applicationNo' => $invoice->application_no,
            'applicant' => $invoice->applicant,
            'applicantId' => $invoice->applicant_id,
            'department' => $invoice->department,
            'customerId' => $invoice->customer_id,
            'customerName' => $invoice->customer_name,
            'customerNo' => $invoice->customer_no,
            'contractId' => $invoice->contract_id,
            'contractName' => $invoice->contract_name,
            'contractNo' => $invoice->contract_no,
            'contractAmount' => $invoice->contract_amount,
            'actualPayment' => $invoice->actual_payment,
            'invoicedAmount' => $invoice->invoiced_amount,
            'uninvoicedAmount' => $invoice->uninvoiced_amount,
            'buyerName' => $invoice->buyer_name,
            'buyerTaxId' => $invoice->buyer_tax_id,
            'buyerAddress' => $invoice->buyer_address,
            'buyerBankAccount' => $invoice->buyer_bank_account,
            'invoiceType' => $invoice->invoice_type,
            'invoiceAmount' => $invoice->invoice_amount,
            'items' => $invoice->items,
            'remark' => $invoice->remark,
            'flowStatus' => $invoice->flow_status,
            'flowStatusText' => $invoice->flow_status_text,
            'currentHandler' => $invoice->current_handler,
            'currentHandlerId' => $invoice->current_handler_id,
            'priority' => $invoice->priority,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoice->invoice_date,
            'invoiceFiles' => $invoice->invoice_files,
            'approver' => $invoice->approver,
            'approverId' => $invoice->approver_id,
            'approveTime' => $invoice->approve_time,
            'approveComment' => $invoice->approve_comment,
            'applicationDate' => $invoice->application_date,
            'createdAt' => $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i:s') : null,
            'updatedAt' => $invoice->updated_at ? $invoice->updated_at->format('Y-m-d H:i:s') : null,
            'deadline' => $invoice->deadline,
            'clientName' => $invoice->customer_name, // 别名,兼容前端
        ];
    }
}

