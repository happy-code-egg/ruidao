<?php

namespace App\Http\Controllers\Api;

use App\Models\ParkConfig;

class ParksConfigController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ParkConfig::class;
    }

   /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义园区配置数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - park_name (string): 园区名称，必填，字符串，最大200字符
 *   - park_code (string): 园区编码，可选，字符串，最大50字符，唯一性验证
 *   - description (string): 描述，可选，字符串
 *   - address (string): 地址，可选，字符串，最大500字符
 *   - contact_person (string): 联系人，可选，字符串，最大100字符
 *   - contact_phone (string): 联系电话，可选，字符串，最大50字符
 *   - is_valid (boolean): 是否有效，可选，布尔值
 *   - sort_order (int): 排序值，必填，整数，最小值0
 *   - created_by (int): 创建人，可选，整数
 *   - updated_by (int): 更新人，可选，整数
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'park_name' => 'required|string|max:200',        // 园区名称：必填，字符串，最大200字符
        'park_code' => 'nullable|string|max:50',         // 园区编码：可选，字符串，最大50字符
        'description' => 'nullable|string',               // 描述：可为空，字符串
        'address' => 'nullable|string|max:500',          // 地址：可选，字符串，最大500字符
        'contact_person' => 'nullable|string|max:100',   // 联系人：可选，字符串，最大100字符
        'contact_phone' => 'nullable|string|max:50',     // 联系电话：可选，字符串，最大50字符
        'is_valid' => 'nullable|boolean',                // 是否有效：可选，布尔值
        'sort_order' => 'required|integer|min:0',        // 排序值：必填，整数，最小值0
        'created_by' => 'nullable|integer',              // 创建人：可选，整数
        'updated_by' => 'nullable|integer'               // 更新人：可选，整数
    ];

    // 根据是否为更新操作设置园区编码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性验证
        $id = request()->route('id');
        $rules['park_code'] .= '|unique:parks_config,park_code,' . $id . ',id';
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
        'park_name.required' => '园区名称不能为空',              // 园区名称必填验证消息
        'park_name.max' => '园区名称长度不能超过200个字符',      // 园区名称长度验证消息
        'park_code.max' => '园区编码长度不能超过50个字符',       // 园区编码长度验证消息
        'address.max' => '园区地址长度不能超过500个字符',        // 地址长度验证消息
        'contact_person.max' => '联系人长度不能超过100个字符',   // 联系人长度验证消息
        'contact_phone.max' => '联系电话长度不能超过50个字符',   // 联系电话长度验证消息
        'sort_order.required' => '排序值不能为空',              // 排序值必填验证消息
        'sort_order.integer' => '排序值必须是整数',             // 排序值整数验证消息
        'sort_order.min' => '排序值不能小于0',                  // 排序值最小值验证消息
    ]);
}

/**
 * 重写index方法以支持特定的搜索条件 index
 *
 * 功能描述：获取园区配置列表，支持园区名称搜索和有效性筛选，支持分页
 *
 * 传入参数：
 * - park_name (string, optional): 园区名称搜索关键词
 * - is_valid (boolean, optional): 是否有效筛选条件
 * - page (int, optional): 页码，默认为1
 * - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 园区配置列表
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(\Illuminate\Http\Request $request)
{
    try {
        // 初始化查询构建器
        $query = ParkConfig::query();

        // 园区名称搜索条件
        if ($request->has('park_name') && !empty($request->park_name)) {
            $query->where('park_name', 'like', '%' . $request->park_name . '%');
        }

        // 是否有效筛选条件
        if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
            $query->where('is_valid', $request->is_valid);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行查询并获取数据
        $data = $query->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get();

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 异常处理和日志记录
        $this->log(
            8,
            "获取园区配置列表失败：{$e->getMessage()}",
            [
                'title' => '园区配置列表',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取列表失败');
    }
}

/**
 * 获取选项列表（用于下拉框等） options
 *
 * 功能描述：获取有效的园区配置选项列表，用于下拉框等场景
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项列表
 *   - value (int): 选项值（ID）
 *   - label (string): 选项标签（园区名称）
 */
public function options(\Illuminate\Http\Request $request)
{
    try {
        // 查询有效的园区配置，按排序和ID排序，返回value/label格式
        $data = ParkConfig::where('is_valid', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->select('id as value', 'park_name as label')
            ->get();

        // 返回成功响应
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 异常处理
        return json_fail('获取选项列表失败');
    }
}

}
