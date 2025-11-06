<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 客户规模控制器
 */
class CustomerScaleController extends Controller
{
  /**
 * 获取客户规模列表
 *
 * 功能描述：根据筛选条件获取客户规模列表，支持分页和排序
 *
 * 传入参数：
 * - scaleName (string, 可选): 客户规模名称，用于模糊搜索
 * - isValid (int, 可选): 是否有效状态（0:无效, 1:有效）
 * - page (int, 可选, 默认1): 当前页码
 * - limit (int, 可选, 默认15): 每页显示数量，最大100
 *
 * 输出参数：
 * - message (string): 操作结果消息
 * - data (object): 分页数据对象
 *   - list (array): 客户规模列表数据
 *     - id (int): 规模ID
 *     - scale_name (string): 规模名称
 *     - is_valid (int): 是否有效（0:无效, 1:有效）
 *     - sort (int): 排序值
 *     - created_by (int): 创建人ID
 *     - updated_by (int): 更新人ID
 *     - created_at (datetime): 创建时间
 *     - updated_at (datetime): 更新时间
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页显示数量
 *   - pages (int): 总页数
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function index(Request $request)
{
    try {
        // 初始化查询构造器
        $query = CustomerScale::query();

        // 规模名称搜索：如果提供了scaleName参数且不为空，则进行模糊匹配
        if ($request->has('scaleName') && !empty($request->scaleName)) {
            $query->where('scale_name', 'like', '%' . $request->scaleName . '%');
        }

        // 状态筛选：如果提供了isValid参数且不为空，则按该值筛选
        if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
            $query->where('is_valid', $request->isValid);
        }

        // 分页参数处理：确保page最小为1，limit在1-100之间
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总数用于分页计算
        $total = $query->count();

        // 获取数据并进行排序和分页
        $data = $query->orderBy('sort')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get();

        // 返回成功响应，包含列表数据和分页信息
        return json_success('获取列表成功', [
            'list' => $data->toArray(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "获取客户规模列表失败: {$e->getMessage()}",
            [
                'title' => '客户规模列表',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取列表失败');
    }
}
  /**
 * 获取客户规模详情
 *
 * 功能描述：根据ID获取指定客户规模的详细信息
 *
 * 传入参数：
 * - id (int, 路径参数): 客户规模ID
 *
 * 输出参数：
 * - message (string): 操作结果消息
 * - data (object): 客户规模详情对象
 *   - id (int): 规模ID
 *   - scale_name (string): 规模名称
 *   - is_valid (int): 是否有效（0:无效, 1:有效）
 *   - sort (int): 排序值
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function show($id)
{
    try {
        // 根据ID查找客户规模记录
        $item = CustomerScale::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应及详情数据
        return json_success('获取详情成功', $item->toArray());

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "获取客户规模详情失败: {$e->getMessage()}",
            [
                'title' => '客户规模详情',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取详情失败');
    }
}

    /**
 * 创建客户规模
 *
 * 功能描述：创建一个新的客户规模记录
 *
 * 传入参数：
 * - scaleName (string, 必填): 客户规模名称，最大100个字符
 * - isValid (boolean, 必填): 是否有效状态
 * - sort (int, 可选): 排序值，最小为1
 *
 * 输出参数：
 * - message (string): 操作结果消息
 * - data (object): 创建成功的客户规模对象
 *   - id (int): 规模ID
 *   - scale_name (string): 规模名称
 *   - is_valid (int): 是否有效（0:无效, 1:有效）
 *   - sort (int): 排序值
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function store(Request $request)
{
    try {
        // 验证请求数据的合法性
        $validator = Validator::make($request->all(), [
            'scaleName' => 'required|string|max:100',     // 规模名称必填且不超过100字符
            'isValid' => 'required|boolean',              // 是否有效必填且必须是布尔值
            'sort' => 'nullable|integer|min:1'            // 排序可为空但必须是大于等于1的整数
        ], [
            // 自定义验证错误消息
            'scaleName.required' => '客户规模名称不能为空',
            'scaleName.max' => '客户规模名称长度不能超过100个字符',
            'isValid.required' => '是否有效不能为空',
            'isValid.boolean' => '是否有效必须是布尔值',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于1'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备创建数据
        $data = [
            'scale_name' => $request->scaleName,         // 规模名称
            'is_valid' => $request->isValid,             // 是否有效
            'sort' => $request->sort ?? 1,               // 排序值，默认为1
            'created_by' => Auth::user()->id ?? 1,       // 创建人ID
            'updated_by' => Auth::user()->id ?? 1        // 更新人ID
        ];

        // 创建新的客户规模记录
        $item = CustomerScale::create($data);

        // 返回成功响应及创建的数据
        return json_success('创建成功', $item->toArray());

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "创建客户规模失败: {$e->getMessage()}",
            [
                'title' => '客户规模创建',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('创建失败');
    }
}


   /**
 * 更新客户规模
 *
 * 功能描述：根据ID更新指定客户规模的信息
 *
 * 传入参数：
 * - id (int, 路径参数): 客户规模ID
 * - scaleName (string, 必填): 客户规模名称，最大100个字符
 * - isValid (boolean, 必填): 是否有效状态
 * - sort (int, 可选): 排序值，最小为1
 *
 * 输出参数：
 * - message (string): 操作结果消息
 * - data (object): 更新后的客户规模对象
 *   - id (int): 规模ID
 *   - scale_name (string): 规模名称
 *   - is_valid (int): 是否有效（0:无效, 1:有效）
 *   - sort (int): 排序值
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找客户规模记录
        $item = CustomerScale::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证请求数据的合法性
        $validator = Validator::make($request->all(), [
            'scaleName' => 'required|string|max:100',     // 规模名称必填且不超过100字符
            'isValid' => 'required|boolean',              // 是否有效必填且必须是布尔值
            'sort' => 'nullable|integer|min:1'            // 排序可为空但必须是大于等于1的整数
        ], [
            // 自定义验证错误消息
            'scaleName.required' => '客户规模名称不能为空',
            'scaleName.max' => '客户规模名称长度不能超过100个字符',
            'isValid.required' => '是否有效不能为空',
            'isValid.boolean' => '是否有效必须是布尔值',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于1'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备更新数据
        $data = [
            'scale_name' => $request->scaleName,         // 规模名称
            'is_valid' => $request->isValid,             // 是否有效
            'sort' => $request->sort ?? 1,               // 排序值，默认为1
            'updated_by' => Auth::user()->id ?? 1        // 更新人ID
        ];

        // 更新客户规模记录
        $item->update($data);

        // 返回成功响应及更新后的数据
        return json_success('更新成功', $item->toArray());

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "更新客户规模失败: {$e->getMessage()}",
            [
                'title' => '客户规模更新',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('更新失败');
    }
}

/**
 * 删除客户规模
 *
 * 功能描述：根据ID删除指定的客户规模记录
 *
 * 传入参数：
 * - id (int, 路径参数): 客户规模ID
 *
 * 输出参数：
 * - message (string): 操作结果消息
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function destroy($id)
{
    try {
        // 根据ID查找客户规模记录
        $item = CustomerScale::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 执行删除操作
        $item->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "删除客户规模失败: {$e->getMessage()}",
            [
                'title' => '客户规模删除',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('删除失败');
    }
}

  /**
 * 获取客户规模选项列表（用于下拉框等）
 *
 * 功能描述：获取所有有效的客户规模选项列表，用于下拉选择等场景
 *
 * 传入参数：无
 *
 * 输出参数：
 * - message (string): 操作结果消息
 * - data (array): 客户规模选项列表
 *   - value (int): 规模ID
 *   - label (string): 规模名称
 *
 * 错误响应：
 * - message (string): 错误信息
 */
public function options(Request $request)
{
    try {
        // 查询所有有效的客户规模，按排序字段排列，并选择ID和名称字段
        $data = CustomerScale::enabled()->ordered()
            ->select('id as value', 'scale_name as label')
            ->get();

        // 返回成功响应及选项数据
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 异常处理：记录日志并返回失败响应
        $this->log(
            8,
            "获取客户规模选项失败: {$e->getMessage()}",
            [
                'title' => '客户规模选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取选项列表失败');
    }
}

}
