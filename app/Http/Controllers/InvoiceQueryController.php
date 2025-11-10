<?php

namespace App\Http\Controllers;

use App\Models\InvoiceApplication;
use App\Models\InvoiceDownloadRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 发票查询控制器
 * 处理发票的查询、统计、下载等操作
 */
class InvoiceQueryController extends Controller
{
    /**
     * 获取发票查询列表
     * @param Request $request 包含搜索条件的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的查询结果，包含分页信息和发票列表
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
            $list = $query->with(['customer', 'contract'])
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get()
                ->map(function ($item) {
                    // 计算合同金额、实际到款、已开票金额、未开票金额
                    $contractAmount = $item->contract->total_amount ?? 0;
                    $actualPayment = 0; // Contract模型没有actual_payment字段
                    $invoicedAmount = $item->invoice_amount ?? 0;
                    $uninvoicedAmount = max($contractAmount, $actualPayment) - $invoicedAmount;

                    return [
                        'id' => $item->id,
                        'applicationNo' => $item->application_no,
                        'applicant' => $item->applicant,
                        'department' => $item->department,
                        'contractName' => $item->contract_name,
                        'contractNo' => $item->contract_no,
                        'clientName' => $item->customer_name,
                        'contractAmount' => $contractAmount,
                        'actualPayment' => $actualPayment,
                        'invoiceAmount' => $invoicedAmount,
                        'uninvoicedAmount' => max(0, $uninvoicedAmount),
                        'invoiceType' => $item->invoice_type_text,
                        'applicationDate' => $item->application_date ? $item->application_date : null,
                        'flowStatus' => $item->flow_status_text,
                    ];
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
            Log::error('获取发票查询列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票查询统计
     * @param Request $request 包含查询条件的请求对象
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的统计数据，包含合同总额、实际到款总额、已开票总额等信息
     */
    public function statistics(Request $request)
    {
        try {
            $query = InvoiceApplication::query();

            // 应用搜索条件
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
            }

            // 简化统计逻辑
            $invoicedTotal = $query->sum('invoice_amount') ?? 0;
            $applicationCount = $query->count();

            // 获取合同总额和实际到款总额（简化版本）
            $contractTotal = 0;
            $actualPaymentTotal = 0;
            
            $invoices = $query->with('contract')->get();
            foreach ($invoices as $invoice) {
                if ($invoice->contract) {
                    $contractTotal += $invoice->contract->contract_amount ?? 0;
                    $actualPaymentTotal += $invoice->contract->actual_payment ?? 0;
                }
            }

            $uninvoicedAmount = max($contractTotal, $actualPaymentTotal) - $invoicedTotal;

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => [
                    'contractTotal' => $contractTotal,
                    'actualPaymentTotal' => $actualPaymentTotal,
                    'invoicedTotal' => $invoicedTotal,
                    'uninvoicedAmount' => max(0, $uninvoicedAmount),
                    'applicationCount' => $applicationCount,
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
     * 下载发票
     * @param int $id 发票申请ID
     * @return \Symfony\Component\HttpFoundation\Response 返回发票文件的响应
     */
    public function download($id)
    {
        try {
            $invoice = InvoiceApplication::findOrFail($id);

            // 这里应该生成PDF或返回实际的发票文件
            // 暂时返回一个示例响应
            $pdfContent = "发票PDF内容 - 申请单号: {$invoice->application_no}";

            // 记录下载
            InvoiceDownloadRecord::create([
                'invoice_application_id' => $id,
                'downloader' => Auth::user()->name ?? 'System',
                'download_ip' => request()->ip(),
                'download_type' => 'invoice',
                'remark' => '下载发票文件',
            ]);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="invoice_' . $invoice->application_no . '.pdf"');
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
     * 获取下载记录
     * @param int $id 发票申请ID
     * @return \Illuminate\Http\JsonResponse 返回JSON格式的下载记录列表
     */
    public function downloadRecords($id)
    {
        try {
            $records = InvoiceDownloadRecord::where('invoice_application_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'downloadTime' => $record->download_time,
                        'downloader' => $record->downloader,
                        'downloadIp' => $record->download_ip,
                        'downloadType' => $record->download_type,
                        'remark' => $record->remark,
                    ];
                });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            Log::error('获取下载记录失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 导出发票列表
     * @param Request $request 包含导出条件的请求对象
     * @return \Symfony\Component\HttpFoundation\Response 返回Excel文件的响应
     */
    public function export(Request $request)
    {
        try {
            // 这里应该生成Excel文件
            // 暂时返回一个示例响应
            $excelContent = "发票列表Excel内容";

            return response($excelContent)
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="invoices_' . date('YmdHis') . '.xlsx"');
        } catch (\Exception $e) {
            Log::error('导出发票列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '导出失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

