<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeePaymentItem;
use App\Models\FeePaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 缴费管理控制器
 * 负责缴费单的增删改查、状态管理和缴费明细管理
 */
class FeePaymentController extends Controller
{
    /**
     * 获取缴费单列表 index
     *
     * 功能描述：根据搜索条件获取缴费单列表，支持分页和多种筛选条件
     *
     * 传入参数：
     * - payment_no (string, optional): 缴费单号搜索关键词
     * - payment_name (string, optional): 缴费单名称搜索关键词
     * - customer_name (string, optional): 客户名称搜索关键词
     * - status (string, optional): 缴费单状态筛选
     * - start_date (string, optional): 缴费日期起始时间
     * - end_date (string, optional): 缴费日期结束时间
     * - page (int, optional): 页码，默认为1
     * - per_page (int, optional): 每页数量，默认为10
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 返回数据
     *   - items (array): 缴费单列表数据
     *   - total (int): 总记录数
     *   - page (int): 当前页码
     *   - per_page (int): 每页数量
     * - msg (string): 操作结果消息
     */
    public function index(Request $request)
    {
        try {
            $query = FeePayment::query();

            // 搜索条件
            if ($request->has('payment_no') && $request->payment_no) {
                $query->where('payment_no', 'like', '%' . $request->payment_no . '%');
            }
            if ($request->has('payment_name') && $request->payment_name) {
                $query->where('payment_name', 'like', '%' . $request->payment_name . '%');
            }
            if ($request->has('customer_name') && $request->customer_name) {
                $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
            }
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // 日期范围
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('payment_date', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('payment_date', '<=', $request->end_date);
            }

            // 分页
            $page = $request->get('page', 1);
            $per_page = $request->get('per_page', 10);

            $total = $query->count();

            // 如果需要查询取票码,加载items关系
            if ($request->has('has_pickup_code') && $request->has_pickup_code) {
                $query->with('items');
            }

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
                'msg' => '获取缴费单列表成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '获取缴费单列表失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取缴费单详情 show
     *
     * 功能描述：根据ID获取缴费单的详细信息，包括关联的项目和历史记录
     *
     * 传入参数：
     * - id (int): 缴费单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 缴费单详细信息，包含items和history关联数据
     * - msg (string): 操作结果消息
     */
    public function show($id)
    {
        try {
            $feePayment = FeePayment::with(['items', 'history'])->find($id);

            if (!$feePayment) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '缴费单不存在'
                ], 404);
            }

            return response()->json([
                'code' => 0,
                'data' => $feePayment,
                'msg' => '获取缴费单详情成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '获取缴费单详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建缴费单 store
     *
     * 功能描述：创建新的缴费单记录，包含基本信息、项目明细和操作历史
     *
     * 传入参数：
     * - payment_name (string): 缴费单名称
     * - customer_id (int): 客户ID
     * - customer_name (string): 客户名称
     * - company_id (int): 公司ID
     * - company_name (string): 公司名称
     * - agency_id (int): 代理机构ID
     * - agency_name (string): 代理机构名称
     * - total_amount (float, optional): 总金额
     * - payment_date (string): 缴费日期
     * - remark (string, optional): 备注
     * - items (array, optional): 缴费项目列表
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 创建成功的缴费单信息
     * - msg (string): 操作结果消息
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // 生成缴费单号
            $paymentNo = $this->generatePaymentNo();

            $feePayment = FeePayment::create([
                'payment_no' => $paymentNo,
                'payment_name' => $request->payment_name,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'company_id' => $request->company_id,
                'company_name' => $request->company_name,
                'agency_id' => $request->agency_id,
                'agency_name' => $request->agency_name,
                'total_amount' => $request->total_amount ?? 0,
                'currency' => $request->currency ?? 'CNY',
                'payment_date' => $request->payment_date,
                'status' => 1, // 1-草稿
                'creator_id' => auth()->id() ?? 1,
                'creator_name' => auth()->user()->name ?? '系统用户',
                'modifier_id' => auth()->id() ?? 1,
                'modifier_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->remark,
            ]);

            // 添加缴费单项目
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    FeePaymentItem::create(array_merge($item, ['fee_payment_id' => $feePayment->id]));
                }
            }

            // 记录历史
            FeePaymentHistory::create([
                'fee_payment_id' => $feePayment->id,
                'operation' => 'create',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '创建缴费单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $feePayment->load(['items', 'history']),
                'msg' => '创建缴费单成功'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '创建缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新缴费单 update
     *
     * 功能描述：更新指定ID的缴费单信息，包括基本信息和项目明细
     *
     * 传入参数：
     * - id (int): 缴费单ID
     * - payment_name (string): 缴费单名称
     * - customer_id (int): 客户ID
     * - customer_name (string): 客户名称
     * - company_id (int): 公司ID
     * - company_name (string): 公司名称
     * - agency_id (int): 代理机构ID
     * - agency_name (string): 代理机构名称
     * - total_amount (float, optional): 总金额
     * - payment_date (string): 缴费日期
     * - remark (string, optional): 备注
     * - items (array, optional): 缴费项目列表
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 更新后的缴费单信息
     * - msg (string): 操作结果消息
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $feePayment = FeePayment::find($id);
            if (!$feePayment) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '缴费单不存在'
                ], 404);
            }

            $feePayment->update([
                'payment_name' => $request->payment_name,
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'company_id' => $request->company_id,
                'company_name' => $request->company_name,
                'agency_id' => $request->agency_id,
                'agency_name' => $request->agency_name,
                'total_amount' => $request->total_amount ?? 0,
                'currency' => $request->currency ?? 'CNY',
                'payment_date' => $request->payment_date,
                'modifier_id' => auth()->id() ?? 1,
                'modifier_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->remark,
            ]);

            // 更新缴费单项目
            if ($request->has('items') && is_array($request->items)) {
                FeePaymentItem::where('fee_payment_id', $id)->delete();
                foreach ($request->items as $item) {
                    FeePaymentItem::create(array_merge($item, ['fee_payment_id' => $feePayment->id]));
                }
            }

            // 记录历史
            FeePaymentHistory::create([
                'fee_payment_id' => $feePayment->id,
                'operation' => 'update',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '更新缴费单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $feePayment->load(['items', 'history']),
                'msg' => '更新缴费单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '更新缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除缴费单 destroy
     *
     * 功能描述：删除指定ID的缴费单记录
     *
     * 传入参数：
     * - id (int): 缴费单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (null): 空数据
     * - msg (string): 操作结果消息
     */
    public function destroy($id)
    {
        try {
            $feePayment = FeePayment::find($id);
            if (!$feePayment) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '缴费单不存在'
                ], 404);
            }

            $feePayment->delete();

            return response()->json([
                'code' => 0,
                'data' => null,
                'msg' => '删除缴费单成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '删除缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量删除缴费单 batchDestroy
     *
     * 功能描述：批量删除多个缴费单记录
     *
     * 传入参数：
     * - ids (array): 要删除的缴费单ID数组
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
                    'msg' => '请选择要删除的缴费单'
                ], 400);
            }

            FeePayment::whereIn('id', $ids)->delete();

            return response()->json([
                'code' => 0,
                'data' => null,
                'msg' => '批量删除缴费单成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '批量删除缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 提交缴费单 submit
     *
     * 功能描述：将缴费单状态更新为已提交状态
     *
     * 传入参数：
     * - id (int): 缴费单ID
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 提交后的缴费单信息
     * - msg (string): 操作结果消息
     */
    public function submit(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $feePayment = FeePayment::find($id);
            if (!$feePayment) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '缴费单不存在'
                ], 404);
            }

            $feePayment->update([
                'status' => 3, // 3-已提交
                'submitted_at' => now(),
            ]);

            FeePaymentHistory::create([
                'fee_payment_id' => $feePayment->id,
                'operation' => 'submit',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => '提交缴费单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $feePayment->load(['items', 'history']),
                'msg' => '提交缴费单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '提交缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 退回缴费单 return
     *
     * 功能描述：将缴费单退回到草稿状态
     *
     * 传入参数：
     * - id (int): 缴费单ID
     * - reason (string, optional): 退回原因
     *
     * 输出参数：
     * - code (int): 状态码，0表示成功，1表示失败
     * - data (object): 退回后的缴费单信息
     * - msg (string): 操作结果消息
     */
    public function returnToDraft(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $feePayment = FeePayment::find($id);
            if (!$feePayment) {
                return response()->json([
                    'code' => 1,
                    'data' => null,
                    'msg' => '缴费单不存在'
                ], 404);
            }

            $feePayment->update(['status' => 1]); // 1-草稿

            FeePaymentHistory::create([
                'fee_payment_id' => $feePayment->id,
                'operation' => 'return',
                'operator_id' => auth()->id() ?? 1,
                'operator_name' => auth()->user()->name ?? '系统用户',
                'remark' => $request->reason ?? '退回缴费单',
            ]);

            DB::commit();

            return response()->json([
                'code' => 0,
                'data' => $feePayment->load(['items', 'history']),
                'msg' => '退回缴费单成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'data' => null,
                'msg' => '退回缴费单失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成缴费单号 generatePaymentNo
     *
     * 功能描述：生成唯一的缴费单编号，格式为JF+日期+3位随机数
     *
     * 传入参数：无
     *
     * 输出参数：
     * - string: 生成的缴费单号，例如：JF20231201001
     */
    private function generatePaymentNo()
    {
        $now = now();
        $dateStr = $now->format('Ymd');
        $randomNum = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return 'JF' . $dateStr . $randomNum;
    }
}

