<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpenseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 支出控制器
 * 负责支出单的增删改查、状态管理和支出明细管理
 */
class ExpenseController extends Controller
{
    /**
     * 获取支出单列表 index
     *
     * 功能描述：根据搜索条件获取支出单列表，支持分页和多种筛选条件
     *
     * 传入参数：
     * - expense_no (string, optional): 支出单号搜索关键词
     * - expense_name (string, optional): 支出单名称搜索关键词
     * - customer_name (string, optional): 客户名称搜索关键词
     * - status (string, optional): 支出单状态筛选
     * - start_date (string, optional): 支出日期起始时间
     * - end_date (string, optional): 支出日期结束时间
     * - page (int, optional): 页码，默认为1
     * - per_page (int, optional): 每页数量，默认为10
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 返回数据
     *   - items (array): 支出单列表数据
     *   - total (int): 总记录数
     *   - page (int): 当前页码
     *   - per_page (int): 每页数量
     * - msg (string): 操作结果消息
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
     * 获取支出单详情 show
     *
     * 功能描述：根据ID获取支出单的详细信息，包括关联的项目和历史记录
     *
     * 传入参数：
     * - id (int): 支出单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 支出单详细信息，包含items和history关联数据
     * - msg (string): 操作结果消息
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
     * 创建支出单 store
     *
     * 功能描述：创建新的支出单记录，包含基本信息、项目明细和操作历史
     *
     * 传入参数：
     * - expense_name (string): 支出单名称
     * - customer_id (int): 客户ID
     * - customer_name (string): 客户名称
     * - company_id (int): 公司ID
     * - company_name (string): 公司名称
     * - total_amount (float, optional): 总金额
     * - expense_date (string): 支出日期
     * - remark (string, optional): 备注
     * - items (array, optional): 支出项目列表
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 创建成功的支出单信息
     * - msg (string): 操作结果消息
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
     * 更新支出单 update
     *
     * 功能描述：更新指定ID的支出单信息，包括基本信息和项目明细
     *
     * 传入参数：
     * - id (int): 支出单ID
     * - expense_name (string): 支出单名称
     * - customer_id (int): 客户ID
     * - customer_name (string): 客户名称
     * - company_id (int): 公司ID
     * - company_name (string): 公司名称
     * - total_amount (float, optional): 总金额
     * - expense_date (string): 支出日期
     * - remark (string, optional): 备注
     * - items (array, optional): 支出项目列表
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 更新后的支出单信息
     * - msg (string): 操作结果消息
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
     * 删除支出单 destroy
     *
     * 功能描述：删除指定ID的支出单记录
     *
     * 传入参数：
     * - id (int): 支出单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (null): 空数据
     * - msg (string): 操作结果消息
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
     * 批量删除支出单 batchDestroy
     *
     * 功能描述：批量删除多个支出单记录
     *
     * 传入参数：
     * - ids (array): 要删除的支出单ID数组
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (null): 空数据
     * - msg (string): 操作结果消息
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
     * 提交支出单 submit
     *
     * 功能描述：将支出单状态更新为已提交状态
     *
     * 传入参数：
     * - id (int): 支出单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 提交后的支出单信息
     * - msg (string): 操作结果消息
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
     * 审批支出单 approve
     *
     * 功能描述：审批通过支出单，将状态更新为已批准
     *
     * 传入参数：
     * - id (int): 支出单ID
     * - remark (string, optional): 审批备注
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 审批后的支出单信息
     * - msg (string): 操作结果消息
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
     * 拒绝支出单 reject
     *
     * 功能描述：拒绝支出单，将状态更新为已拒绝
     *
     * 传入参数：
     * - id (int): 支出单ID
     * - reason (string, optional): 拒绝原因
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 拒绝后的支出单信息
     * - msg (string): 操作结果消息
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
     * 生成支出单号 generateExpenseNo
     *
     * 功能描述：生成唯一的支出单编号，格式为CK+日期+3位随机数
     *
     * 传入参数：无
     *
     * 输出参数：
     * - string: 生成的支出单号，例如：CK20231201001
     */
    private function generateExpenseNo()
    {
        $now = now();
        $dateStr = $now->format('Ymd');
        $randomNum = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return 'CK' . $dateStr . $randomNum;
    }
}

