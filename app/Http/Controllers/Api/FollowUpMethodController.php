<?php

namespace App\Http\Controllers\Api;

use App\Models\FollowUpMethod;
use Illuminate\Http\Request;

/**
 * 跟进方式控制器
 */
class FollowUpMethodController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return FollowUpMethod::class;
    }

   /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义跟进方式数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - name (string): 跟进方式名称，必填，字符串，最大100字符
 *   - code (string): 跟进方式编码，必填，字符串，最大50字符，唯一性验证
 *   - description (string): 描述，可选，字符串
 *   - status (int): 状态，必填，只能是0或1
 *   - sort_order (int): 排序，可选，整数，最小值0
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'name' => 'required|string|max:100',           // 跟进方式名称：必填，字符串，最大100字符
        'code' => 'required|string|max:50',            // 跟进方式编码：必填，字符串，最大50字符
        'description' => 'nullable|string',             // 描述：可为空，字符串
        'status' => 'required|in:0,1',                 // 状态：必填，只能是0或1
        'sort_order' => 'nullable|integer|min:0',      // 排序：可为空，整数，最小值0
    ];

    // 根据是否为更新操作设置编码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性验证
        $id = request()->route('id') ?? request()->route('follow_up_method');
        $rules['code'] .= '|unique:follow_up_methods,code,' . $id;
    } else {
        // 创建时，全局唯一性验证
        $rules['code'] .= '|unique:follow_up_methods,code';
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
        'name.required' => '跟进方式名称不能为空',      // 名称必填验证消息
        'code.required' => '跟进方式编码不能为空',      // 编码必填验证消息
        'code.unique' => '跟进方式编码已存在',          // 编码唯一性验证消息
    ]);
}

/**
 * 重写 index 支持 name/status 搜索 index
 *
 * 功能描述：获取跟进方式列表，支持按名称和状态搜索，支持分页
 *
 * 传入参数：
 * - name (string, optional): 跟进方式名称搜索关键词
 * - status (int, optional): 状态筛选条件
 * - page (int, optional): 页码，默认为1
 * - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 跟进方式列表
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = FollowUpMethod::query();

        // 名称搜索条件
        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 状态筛选条件
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // 分页参数处理
        $page = max(1, (int) $request->get('page', 1));
        $limit = max(1, min(100, (int) $request->get('limit', 15)));

        // 执行查询
        $total = $query->count();
        $list = $query->orderBy('sort_order')
                      ->orderBy('id')
                      ->offset(($page - 1) * $limit)
                      ->limit($limit)
                      ->get();

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);

    } catch (\Exception $e) {
        // 异常处理
        log_exception($e, '获取跟进方式列表失败');
        return json_fail('获取列表失败');
    }
}

}
