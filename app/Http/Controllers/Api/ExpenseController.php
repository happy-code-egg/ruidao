<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpenseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * 获取支出单列表
     */
    public function index(Request $request)
    {
        try {
            $query = Expense::query();

            // 搜索条件
            if ($request->has('expense_no') && $request->expense_no) {
                $query->where('expense_no', 'like', '%' . $request->expense_no . '%');
            }
            if ($request->has('expense_name') && $request->expense_name) {
                $query->where('expense_name', 'like', '%' . $request->expense_name . '%');
            }
            if ($request->has('customer_name') && $request->customer_name) {
                $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
            }
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // 日期范围
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('expense_date', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('expense_date', '<=', $request->end_date);
            }

            // 分页
            $page = $request->get('page', 1);
            $per_page = $request->get('per_page', 10);

            $total = $query->count();
            $data = $query->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $per_page)
                ->limit($per_page)
                ->get();

            return response()->json([
                'code' => 0,
                'data' => [
                    'items' => $data,
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $per_page,
                ],
                'msg' => '获取支出单列表成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '获取支出单列表失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取支出单详情
     */
    public function show($id)
    {
        try {
            $expense = Expense::with(['items', 'history'])->find($id);

            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'data' => $expense,
                'msg' => '获取支出单详情成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '获取支出单详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建支出单
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // 生成支出单号
            $expenseNo = $this->generateExpenseNo();

            $expense = Expense::create([
                'expense_no' => $expenseNo,
                'expense_name' => $request->expense_name,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'company_id' => $request->company_id,
                'company_name' => $request->company_name,
                'total_amount' => $request->total_amount ?? 0,
                'expense_date' => $request->expense_date,
                'status' => 'draft',
                'creator_id' => auth()->id() ?? 1,
                'creator_name' => auth()->user()->name ?? '系统用户',
                'modifier_id' => auth()->id() ?? 1,
                'modifier_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->remark,
            ]);

            // 添加支出单项目
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    ExpenseItem::create(array_merge($item, ['expense_id' => $expense->id]));
                }
            }

            // 记录历史
            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'operation' => 'create',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '创建支出单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $expense->load(['items', 'history']),
                'msg' => '创建支出单成功'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '创建支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新支出单
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            $expense->update([
                'expense_name' => $request->expense_name,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'company_id' => $request->company_id,
                'company_name' => $request->company_name,
                'total_amount' => $request->total_amount ?? 0,
                'expense_date' => $request->expense_date,
                'modifier_id' => auth()->id() ?? 1,
                'modifier_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->remark,
            ]);

            // 更新支出单项目
            if ($request->has('items') && is_array($request->items)) {
                ExpenseItem::where('expense_id', $id)->delete();
                foreach ($request->items as $item) {
                    ExpenseItem::create(array_merge($item, ['expense_id' => $expense->id]));
                }
            }

            // 记录历史
            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'operation' => 'update',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '更新支出单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $expense->load(['items', 'history']),
                'msg' => '更新支出单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '更新支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除支出单
     */
    public function destroy($id)
    {
        try {
            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            $expense->delete();

            return response()->json([
                'code' => 0,
                'data' => null,
                'msg' => '删除支出单成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '删除支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量删除支出单
     */
    public function batchDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '请选择要删除的支出单'
                ], 400);
            }

            Expense::whereIn('id', $ids)->delete();

            return response()->json([
                'code' => 0,
                'data' => null,
                'msg' => '批量删除支出单成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '批量删除支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 提交支出单
     */
    public function submit(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            $expense->update(['status' => 'submitted']);

            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'operation' => 'submit',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '提交支出单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $expense->load(['items', 'history']),
                'msg' => '提交支出单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '提交支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审批支出单
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            $expense->update(['status' => 'approved']);

            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'operation' => 'approve',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->remark ?? '审批通过',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $expense->load(['items', 'history']),
                'msg' => '审批支出单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '审批支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 拒绝支出单
     */
    public function reject(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '支出单不存在'
                ], 404);
            }

            $expense->update(['status' => 'rejected']);

            ExpenseHistory::create([
                'expense_id' => $expense->id,
                'operation' => 'reject',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->reason ?? '审批拒绝',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $expense->load(['items', 'history']),
                'msg' => '拒绝支出单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '拒绝支出单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成支出单号
     */
    private function generateExpenseNo()
    {
        $now = now();
        $dateStr = $now->format('Ymd');
        $randomNum = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return 'CK' . $dateStr . $randomNum;
    }
}

