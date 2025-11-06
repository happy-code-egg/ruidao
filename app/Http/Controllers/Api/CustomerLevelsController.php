<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerLevel;

class CustomerLevelsController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     *
     * @return string 返回客户等级模型类的完全限定类名
     */
    protected function getModelClass()
    {
        return CustomerLevel::class;
    }

    /**
     * 获取验证规则
     *
     * @param bool $isUpdate 是否为更新操作，默认为false
     * @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        // 定义基础验证规则
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'level_name' => 'required|string|max:100',
            'level_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
            'updater' => 'nullable|string|max:100'
        ];

        // 如果是更新操作，为level_code字段添加唯一性验证规则
        if ($isUpdate) {
            $id = request()->route('id');
            $rules['level_code'] .= '|unique:customer_levels,level_code,' . $id . ',id';
        }

        return $rules;
    }


    /**
     * 获取验证错误消息
     *
     * 该方法用于定义表单验证失败时的错误提示信息，包括客户等级名称、编码和排序字段的验证规则对应的错误消息。
     * 方法通过合并父类的验证消息和当前类自定义的验证消息来构建完整的验证提示体系。
     *
     * @return array 返回验证错误消息数组，键为验证规则，值为对应的错误提示文本
     */
    protected function getValidationMessages()
    {
        // 合并父类验证消息与当前类自定义验证消息
        return array_merge(parent::getValidationMessages(), [
            'level_name.required' => '客户等级名称不能为空',
            'level_name.max' => '客户等级名称长度不能超过100个字符',
            'level_code.nullable' => '客户等级编码不能为空',
            'level_code.max' => '客户等级编码长度不能超过50个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }


    /**
     * 重写index方法以支持特定的搜索条件
     *  功能描述：根据筛选条件获取客户等级列表，支持分页和排序
     *
     *  传入参数：
     *  - level_name (string, 可选): 客户等级名称，用于模糊搜索
     *  - is_valid (int, 可选): 是否有效（0:无效, 1:有效）
     *  - page (int, 可选, 默认1): 当前页码
     *  - limit (int, 可选, 默认15): 每页显示数量，最大100
     *
     *  输出参数：
     *  - message (string): 操作结果消息
     *  - data (object): 分页数据对象
     *    - list (array): 客户等级列表数据
     *      - id (int): 等级ID
     *      - sort (int): 排序值
     *      - level_name (string): 等级名称
     *      - level_code (string): 等级编码
     *      - description (string): 描述
     *      - is_valid (int): 是否有效（0:无效, 1:有效）
     *      - created_at (datetime): 创建时间
     *      - updated_at (datetime): 更新时间
     *    - total (int): 总记录数
     *    - page (int): 当前页码
     *    - limit (int): 每页显示数量
     *    - pages (int): 总页数
     *
     *  错误响应：
     *  - message (string): 错误信息
     * /
     *
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = CustomerLevel::query();

            // 客户等级名称搜索
            if ($request->has('level_name') && !empty($request->level_name)) {
                $query->where('level_name', 'like', '%' . $request->level_name . '%');
            }

            // 是否有效筛选
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort')
                ->orderBy('id')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取客户等级列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     *
     * 功能描述：获取所有有效的客户等级选项列表，用于下拉选择等场景
     *
     * 传入参数：无
     *
     * 输出参数：
     * - message (string): 操作结果消息
     * - data (array): 客户等级选项列表
     *   - value (int): 等级ID
     *   - label (string): 等级名称
     *   - level_code (string): 等级编码
     *
     * 错误响应：
     * - message (string): 错误信息
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            // 查询所有有效的客户等级，只选择必要字段并重命名以适应前端下拉框需求
            $data = CustomerLevel::where('is_valid', 1)
                ->orderBy('sort')
                ->orderBy('id')
                ->select('id as value', 'level_name as label', 'level_code')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            return json_fail('获取选项列表失败');
        }
    }
}
