<?php

namespace App\Http\Controllers\Api;

use App\Models\InnovationIndices;
use Illuminate\Http\Request;

class InnovationIndicesController extends BaseDataConfigController
{
   /**
 * 获取模型类名 getModelClass
 *
 * 功能描述：返回当前控制器使用的模型类名
 *
 * 传入参数：无
 *
 * 输出参数：
 * - string: 模型类名 InnovationIndices::class
 */
protected function getModelClass()
{
    return InnovationIndices::class;
}

/**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义创新指数数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - name (string): 创新指数名称，必填，字符串，最大100字符
 *   - code (string): 编码，可选，字符串，最大50字符，唯一性验证
 *   - index_name (string): 指数名称，必填，字符串，最大200字符
 *   - description (string): 描述，可选，字符串
 *   - base_value (numeric): 基准值，可选，数值型
 *   - current_value (numeric): 当前值，可选，数值型
 *   - status (int): 状态，必填，只能是0或1
 *   - sort_order (int): 排序，可选，整数，最小值0
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'name' => 'required|string|max:100',            // 创新指数名称：必填，字符串，最大100字符
        'code' => 'nullable|string|max:50',             // 编码：可选，字符串，最大50字符
        'index_name' => 'required|string|max:200',      // 指数名称：必填，字符串，最大200字符
        'description' => 'nullable|string',              // 描述：可为空，字符串
        'base_value' => 'nullable|numeric',             // 基准值：可选，数值型
        'current_value' => 'nullable|numeric',          // 当前值：可选，数值型
        'status' => 'required|in:0,1',                  // 状态：必填，只能是0或1
        'sort_order' => 'nullable|integer|min:0',       // 排序：可为空，整数，最小值0
    ];

    // 根据是否为更新操作设置编码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性验证
        $id = request()->route('id');
        $rules['code'] .= '|unique:innovation_indices,code,' . $id;
    } else {
        // 创建时，全局唯一性验证
        $rules['code'] .= '|unique:innovation_indices,code';
    }

    // 返回验证规则
    return $rules;
}

/**
 * 获取验证错误消息 getValidationMessages
 *
 * 功能描述：定义验证失败时的错误消息，继承父类消息并添加特定消息
 *
 * 传入参数：无
 *
 * 输出参数：
 * - array: 验证错误消息数组
 */
protected function getValidationMessages()
{
    // 合并父类的验证消息和当前类的特定验证消息
    return array_merge(parent::getValidationMessages(), [
        'name.required' => '创新指数名称不能为空'         // 名称必填验证消息
    ]);
}

/**
 * 重写 index 支持 index_level 筛选 index
 *
 * 功能描述：获取创新指数列表，支持关键词搜索、状态筛选和分页
 *
 * 传入参数：
 * - keyword (string, optional): 搜索关键词
 * - status (int, optional): 状态筛选条件
 * - page (int, optional): 页码，默认为1
 * - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 创新指数列表
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = InnovationIndices::query();

        // 关键词搜索条件
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('index_name', 'like', "%{$keyword}%");
            });
        }

        // 状态筛选条件
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行查询并格式化数据
        $data = $query->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get()->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                            'index_name' => $item->index_name,
                            'description' => $item->description,
                            'base_value' => $item->base_value ?? '',
                            'current_value' => $item->current_value ?? '',
                            'status' => $item->status,
                            'status_text' => $item->status_text,
                            'sort_order' => $item->sort_order,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'created_by' => $item->creator->real_name ?? '',
                            'updated_by' => $item->updater->real_name ?? '',
                        ];
                     });

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int)ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 异常处理
        log_exception($e, '获取创新指数列表失败');
        return json_fail('获取列表失败');
    }
}

/**
 * 获取选项列表（用于下拉框等） options
 *
 * 功能描述：获取启用状态的创新指数选项列表，用于下拉框等场景
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项列表
 *   - value (int): 选项值（ID）
 *   - label (string): 选项标签（指数名称）
 */
public function options(\Illuminate\Http\Request $request)
{
    try {
        // 查询启用状态的创新指数，按排序和ID排序，返回value/label格式
        $data = \App\Models\InnovationIndices::where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->select('id as value', 'index_name as label')
            ->get();

        // 返回成功响应
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 异常处理
        return json_fail('获取选项列表失败');
    }
}

}
