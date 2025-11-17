<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceApplicationController extends Controller
{
    /**
     * 获取发票申请列表
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('invoice_applications');

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

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
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
            Log::error('获取发票申请列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票申请统计数据
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total' => DB::table('invoice_applications')->count(),
                'draft' => DB::table('invoice_applications')->where('flow_status', 'draft')->count(),
                'reviewing' => DB::table('invoice_applications')->where('flow_status', 'reviewing')->count(),
                'approved' => DB::table('invoice_applications')->where('flow_status', 'approved')->count(),
                'returned' => DB::table('invoice_applications')->where('flow_status', 'returned')->count(),
                'completed' => DB::table('invoice_applications')->where('flow_status', 'completed')->count(),
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票申请统计失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取待处理发票申请列表
     */
    public function pending(Request $request)
    {
        try {
            $query = DB::table('invoice_applications')
                ->whereIn('flow_status', ['reviewing', 'finance_review', 'leader_approve']);

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

            // 日期范围
            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query->whereBetween('application_date', [$request->startDate, $request->endDate]);
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
            Log::error('获取待处理发票申请列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取待处理发票申请统计数据
     */
    public function pendingStats(Request $request)
    {
        try {
            $stats = [
                'reviewing' => DB::table('invoice_applications')->where('flow_status', 'reviewing')->count(),
                'urgent' => DB::table('invoice_applications')->where('priority', 'urgent')->whereIn('flow_status', ['reviewing', 'finance_review', 'leader_approve'])->count(),
                'overdue' => DB::table('invoice_applications')->whereIn('flow_status', ['reviewing', 'finance_review', 'leader_approve'])->where('created_at', '<', now()->subDays(3))->count(),
                'today' => DB::table('invoice_applications')->whereIn('flow_status', ['reviewing', 'finance_review', 'leader_approve'])->whereDate('created_at', today())->count(),
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('获取待处理发票申请统计失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票申请详情
     */
    public function show($id)
    {
        try {
            $invoice = DB::table('invoice_applications')->where('id', $id)->first();

            if (!$invoice) {
                return response()->json([
                    'code' => 1,
                    'msg' => '发票申请不存在',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $invoice
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
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $id = DB::table('invoice_applications')->insertGetId($data);

            return response()->json([
                'code' => 0,
                'msg' => '创建成功',
                'data' => ['id' => $id]
            ]);
        } catch (\Exception $e) {
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
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $data['updated_at'] = now();

            DB::table('invoice_applications')->where('id', $id)->update($data);

            return response()->json([
                'code' => 0,
                'msg' => '更新成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
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
     */
    public function destroy($id)
    {
        try {
            DB::table('invoice_applications')->where('id', $id)->delete();

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
     * 保存发票申请草稿
     */
    public function saveDraft(Request $request)
    {
        try {
            $data = $request->all();
            $data['flow_status'] = 'draft';
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $id = DB::table('invoice_applications')->insertGetId($data);

            return response()->json([
                'code' => 0,
                'msg' => '保存草稿成功',
                'data' => ['id' => $id]
            ]);
        } catch (\Exception $e) {
            Log::error('保存发票申请草稿失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '保存失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 审批发票申请
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::table('invoice_applications')->where('id', $id)->update([
                'flow_status' => 'approved',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '审批成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('审批发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '审批失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 拒绝发票申请
     */
    public function reject(Request $request, $id)
    {
        try {
            DB::table('invoice_applications')->where('id', $id)->update([
                'flow_status' => 'returned',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '退回成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('退回发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '退回失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 转交发票申请
     */
    public function transfer(Request $request, $id)
    {
        try {
            // 这里可以添加转交逻辑
            return response()->json([
                'code' => 0,
                'msg' => '转交成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('转交发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '转交失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 批量审批发票申请
     */
    public function batchApprove(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            DB::table('invoice_applications')->whereIn('id', $ids)->update([
                'flow_status' => 'approved',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '批量审批成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('批量审批发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '批量审批失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 批量拒绝发票申请
     */
    public function batchReject(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            DB::table('invoice_applications')->whereIn('id', $ids)->update([
                'flow_status' => 'returned',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '批量退回成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('批量退回发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '批量退回失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 提交发票申请上传信息
     */
    public function submitUpload(Request $request, $id)
    {
        try {
            $data = [
                'invoice_number' => $request->input('invoiceNumber'),
                'invoice_date' => $request->input('invoiceDate'),
                'invoice_file' => $request->input('fileList'),
                'remark' => $request->input('remark'),
                'flow_status' => 'completed',
                'updated_at' => now()
            ];

            DB::table('invoice_applications')->where('id', $id)->update($data);

            return response()->json([
                'code' => 0,
                'msg' => '提交成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('提交发票上传信息失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '提交失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 获取发票申请历史记录
     */
    public function history($id)
    {
        try {
            // 这里可以从工作流历史表中获取数据
            // 暂时返回空数组
            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => []
            ]);
        } catch (\Exception $e) {
            Log::error('获取发票申请历史记录失败: ' . $e->getMessage());
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
            $customer = DB::table('customers')->where('id', $customerId)->first();

            if (!$customer) {
                return response()->json([
                    'code' => 1,
                    'msg' => '客户不存在',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $customer
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
    public function getCustomerContracts($customerId)
    {
        try {
            $contracts = DB::table('contracts')->where('customer_id', $customerId)->get();

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $contracts
            ]);
        } catch (\Exception $e) {
            Log::error('获取客户合同列表失败: ' . $e->getMessage());
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
            $contract = DB::table('contracts')->where('id', $contractId)->first();

            if (!$contract) {
                return response()->json([
                    'code' => 1,
                    'msg' => '合同不存在',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $contract
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
     * 催办发票申请
     */
    public function urge($id)
    {
        try {
            // 这里可以添加催办逻辑，比如发送通知等
            return response()->json([
                'code' => 0,
                'msg' => '催办成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('催办发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '催办失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 撤回发票申请
     */
    public function recall($id)
    {
        try {
            DB::table('invoice_applications')->where('id', $id)->update([
                'flow_status' => 'draft',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '撤回成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('撤回发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '撤回失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 提交发票申请
     */
    public function submit($id)
    {
        try {
            DB::table('invoice_applications')->where('id', $id)->update([
                'flow_status' => 'reviewing',
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => '提交成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('提交发票申请失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '提交失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

