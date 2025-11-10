<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpportunityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 商机类型控制器
 */
class OpportunityTypeController extends Controller
{
    /**
 * 获取列表 index
 *
 * 功能描述：获取商机类型列表，支持搜索和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - statusName (string, optional): 商机类型名称搜索条件
 *   - isValid (int, optional): 有效性筛选条件（0-无效，1-有效）
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 商机类型列表数据
 *     - id (int): 记录ID
 *     - status_name (string): 商机类型名称
 *     - is_valid (int): 是否有效（0-无效，1-有效）
 *     - sort (int): 排序
 *     - updated_by (string): 最后更新人
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = OpportunityType::query();

        // 类型名称搜索
        if ($request->has('statusName') && !empty($request->statusName)) {
            $query->where('status_name', 'like', '%' . $request->statusName . '%');
        }

        // 状态筛选
        if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
            $query->where('is_valid', $request->isValid);
        }

        // 分页处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询，按排序字段和ID排序
        $data = $query->orderBy('sort')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get();

        // 返回成功响应，包含分页数据
        return json_success('获取列表成功', [
            'list' => $data->toArray(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('获取商机类型列表失败: ' . $e->getMessage());
        return json_fail('获取列表失败');
    }
}

  /**
 * 获取详情 show
 *
 * 功能描述：根据ID获取单条商机类型的详细信息
 *
 * 传入参数：
 * - id (int): 商机类型的ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 商机类型详细信息
 *   - id (int): 记录ID
 *   - status_name (string): 商机类型名称
 *   - is_valid (int): 是否有效（0-无效，1-有效）
 *   - sort (int): 排序
 *   - updated_by (string): 最后更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找商机类型记录
        $item = OpportunityType::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应，包含记录详细信息
        return json_success('获取详情成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('获取商机类型详情失败: ' . $e->getMessage());
        return json_fail('获取详情失败');
    }
}

/**
 * 创建 store
 *
 * 功能描述：创建新的商机类型
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - statusName (string, required): 商机类型名称，最大100字符
 *   - isValid (boolean, required): 是否有效
 *   - sort (int, optional): 排序，默认为1
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 创建的商机类型信息
 *   - id (int): 记录ID
 *   - status_name (string): 商机类型名称
 *   - is_valid (int): 是否有效（0-无效，1-有效）
 *   - sort (int): 排序
 *   - updated_by (string): 最后更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function store(Request $request)
{
    try {
        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'statusName' => 'required|string|max:100',
            'isValid' => 'required|boolean',
            'sort' => 'nullable|integer|min:1'
        ], [
            'statusName.required' => '商机类型名称不能为空',
            'statusName.max' => '商机类型名称长度不能超过100个字符',
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
            'status_name' => $request->statusName,
            'is_valid' => $request->isValid,
            'sort' => $request->sort ?? 1,
            // 获取当前用户名称，如果不存在则使用默认值
            'updated_by' => auth()->user()->name ?? '系统管理员'
        ];

        // 创建商机类型记录
        $item = OpportunityType::create($data);

        // 返回成功响应，包含创建的记录信息
        return json_success('创建成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('创建商机类型失败: ' . $e->getMessage());
        return json_fail('创建失败');
    }
}


   /**
 * 更新 update
 *
 * 功能描述：根据ID更新商机类型信息
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - statusName (string, required): 商机类型名称，最大100字符
 *   - isValid (boolean, required): 是否有效
 *   - sort (int, optional): 排序，默认为1
 * - id (int): 要更新的商机类型ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 更新后的商机类型信息
 *   - id (int): 记录ID
 *   - status_name (string): 商机类型名称
 *   - is_valid (int): 是否有效（0-无效，1-有效）
 *   - sort (int): 排序
 *   - updated_by (string): 最后更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找商机类型记录
        $item = OpportunityType::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'statusName' => 'required|string|max:100',
            'isValid' => 'required|boolean',
            'sort' => 'nullable|integer|min:1'
        ], [
            'statusName.required' => '商机类型名称不能为空',
            'statusName.max' => '商机类型名称长度不能超过100个字符',
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
            'status_name' => $request->statusName,
            'is_valid' => $request->isValid,
            'sort' => $request->sort ?? 1,
            // 获取当前用户名称，如果不存在则使用默认值
            'updated_by' => auth()->user()->name ?? '系统管理员'
        ];

        // 更新商机类型记录
        $item->update($data);

        // 返回成功响应，包含更新后的记录信息
        return json_success('更新成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('更新商机类型失败: ' . $e->getMessage());
        return json_fail('更新失败');
    }
}

/**
 * 删除 destroy
 *
 * 功能描述：根据ID删除单条商机类型记录
 *
 * 传入参数：
 * - id (int): 要删除的商机类型ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找商机类型记录
        $item = OpportunityType::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 删除商机类型记录
        $item->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('删除商机类型失败: ' . $e->getMessage());
        return json_fail('删除失败');
    }
}

/**
 * 获取选项列表 options
 *
 * 功能描述：获取所有有效的商机类型选项列表，用于下拉框等场景
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项列表数据
 *   - id (int): 记录ID
 *   - label (string): 选项标签（商机类型名称）
 *   - value (string): 选项值（商机类型名称）
 */
public function options(Request $request)
{
    try {
        // 获取所有有效的商机类型，按排序字段排列
        $data = OpportunityType::enabled()->ordered()
            ->select('id', 'status_name as label', 'status_name as value')
            ->get();

        // 返回成功响应，包含选项列表数据
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        \Log::error('获取商机类型选项失败: ' . $e->getMessage());
        return json_fail('获取选项列表失败');
    }
}

}
