<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceQueryController extends Controller
{
    /**
     * 查询发票列表
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('invoice_applications')
                ->where('flow_status', '!=', 'draft');

            // 搜索条件
            if ($request->filled('applicationNo')) {
                $query->where('application_no', 'like', '%' . $request->applicationNo . '%');
            }

            if ($request->filled('applicant')) {
                $query->where('applicant', 'like', '%' . $request->applicant . '%');
            }

            if ($request->filled('clientName')) {
                $query->where('client_name', 'like', '%' . $request->clientName . '%');
            }

            if ($request->filled('contractName')) {
                $query->where('contract_name', 'like', '%' . $request->contractName . '%');
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

            // 日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('amountMin')) {
                $query->where('invoice_amount', '>=', $request->amountMin);
            }

            if ($request->filled('amountMax')) {
                $query->where('invoice_amount', '<=', $request->amountMax);
            }

            // 客户范围
            if ($request->filled('clientScope') && is_array($request->clientScope)) {
                $query->whereIn('client_id', $request->clientScope);
            }

            // 合同范围
            if ($request->filled('contractScope') && is_array($request->contractScope)) {
                $query->whereIn('contract_id', $request->contractScope);
            }

            // 分页
            $pageSize = $request->input('pageSize', 10);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $list = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'list' => $list,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('查询发票列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票查询统计数据
     */
    public function statistics(Request $request)
    {
        try {
            // 应用搜索条件
            $query = DB::table('invoice_applications')
                ->where('flow_status', '!=', 'draft');

            // 搜索条件
            if ($request->filled('applicationNo')) {
                $query->where('application_no', 'like', '%' . $request->applicationNo . '%');
            }

            if ($request->filled('applicant')) {
                $query->where('applicant', 'like', '%' . $request->applicant . '%');
            }

            if ($request->filled('clientName')) {
                $query->where('client_name', 'like', '%' . $request->clientName . '%');
            }

            if ($request->filled('contractName')) {
                $query->where('contract_name', 'like', '%' . $request->contractName . '%');
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

            // 日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('amountMin')) {
                $query->where('invoice_amount', '>=', $request->amountMin);
            }

            if ($request->filled('amountMax')) {
                $query->where('invoice_amount', '<=', $request->amountMax);
            }

            // 客户范围
            if ($request->filled('clientScope') && is_array($request->clientScope)) {
                $query->whereIn('client_id', $request->clientScope);
            }

            // 合同范围
            if ($request->filled('contractScope') && is_array($request->contractScope)) {
                $query->whereIn('contract_id', $request->contractScope);
            }

            // 获取合同总额（从contracts表）
            $contractTotal = DB::table('contracts')->sum('contract_amount') ?? 0;

            // 获取实际到款总额（从payment_received表）
            $actualPaymentTotal = DB::table('payment_received')->where('status', 'claimed')->sum('amount') ?? 0;

            // 获取已开票总额
            $invoicedTotal = (clone $query)->where('flow_status', 'completed')->sum('invoice_amount') ?? 0;

            // 获取未开票总额（合同总额 - 已开票总额）
            $uninvoicedTotal = $contractTotal - $invoicedTotal;

            // 获取待开票总额
            $pendingTotal = (clone $query)->whereIn('flow_status', ['reviewing', 'finance_review', 'leader_approve', 'approved'])->sum('invoice_amount') ?? 0;

            // 获取本月开票总额
            $monthTotal = (clone $query)->where('flow_status', 'completed')->whereMonth('invoice_date', now()->month)->sum('invoice_amount') ?? 0;

            $stats = [
                'contractTotal' => $contractTotal,
                'actualPaymentTotal' => $actualPaymentTotal,
                'invoicedTotal' => $invoicedTotal,
                'uninvoicedTotal' => $uninvoicedTotal,
                'pendingTotal' => $pendingTotal,
                'monthTotal' => $monthTotal,
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票查询统计失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 下载发票
     */
    public function download($id)
    {
        try {
            $invoice = DB::table('invoice_applications')->where('id', $id)->first();

            if (!$invoice) {
                return response()->json([
                    'code' => 1,
                    'msg' => '发票不存在',
                    'data' => null
                ], 404);
            }

            // 这里应该返回实际的文件流
            // 暂时返回成功响应
            return response()->json([
                'code' => 0,
                'msg' => '下载成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('下载发票失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '下载失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票下载记录
     */
    public function downloadRecords($id)
    {
        try {
            // 这里可以从下载记录表中获取数据
            // 暂时返回空数组
            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => []
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票下载记录失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 导出发票数据
     */
    public function export(Request $request)
    {
        try {
            $query = DB::table('invoice_applications')
                ->where('flow_status', '!=', 'draft');

            // 搜索条件
            if ($request->filled('applicationNo')) {
                $query->where('application_no', 'like', '%' . $request->applicationNo . '%');
            }

            if ($request->filled('applicant')) {
                $query->where('applicant', 'like', '%' . $request->applicant . '%');
            }

            if ($request->filled('clientName')) {
                $query->where('client_name', 'like', '%' . $request->clientName . '%');
            }

            if ($request->filled('contractName')) {
                $query->where('contract_name', 'like', '%' . $request->contractName . '%');
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

            // 日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 金额范围
            if ($request->filled('amountMin')) {
                $query->where('invoice_amount', '>=', $request->amountMin);
            }

            if ($request->filled('amountMax')) {
                $query->where('invoice_amount', '<=', $request->amountMax);
            }

            // 客户范围
            if ($request->filled('clientScope') && is_array($request->clientScope)) {
                $query->whereIn('client_id', $request->clientScope);
            }

            // 合同范围
            if ($request->filled('contractScope') && is_array($request->contractScope)) {
                $query->whereIn('contract_id', $request->contractScope);
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            // 生成Excel文件
            $filename = '发票查询_' . date('YmdHis') . '.xlsx';

            // 使用CSV格式导出
            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                // 添加BOM头以支持中文
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // 表头
                fputcsv($file, [
                    '申请单号',
                    '申请人',
                    '客户名称',
                    '合同名称',
                    '发票类型',
                    '开票金额',
                    '流程状态',
                    '申请时间',
                    '所属部门'
                ]);

                // 数据行
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->application_no ?? '',
                        $row->applicant ?? '',
                        $row->client_name ?? '',
                        $row->contract_name ?? '',
                        $this->getInvoiceTypeLabel($row->invoice_type ?? ''),
                        $row->invoice_amount ?? 0,
                        $this->getFlowStatusLabel($row->flow_status ?? ''),
                        $row->application_date ?? '',
                        $row->department ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('导出发票数据失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '导出失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票类型标签
     */
    private function getInvoiceTypeLabel($type)
    {
        $labels = [
            'special' => '增值税专用发票',
            'general' => '增值税普通发票',
            'electronic' => '电子发票'
        ];
        return $labels[$type] ?? $type;
    }

    /**
     * 获取流程状态标签
     */
    private function getFlowStatusLabel($status)
    {
        $labels = [
            'draft' => '草稿',
            'reviewing' => '审核中',
            'finance_review' => '财务审核',
            'leader_approve' => '领导审批',
            'approved' => '已通过',
            'returned' => '已退回',
            'completed' => '已完成'
        ];
        return $labels[$status] ?? $status;
    }

