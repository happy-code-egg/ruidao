<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessServiceTypes;
use Illuminate\Http\Request;

class BusinessServiceTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return BusinessServiceTypes::class;
    }

    /**
    获取业务服务类型的验证规则
    验证规则说明：
    区分新增和更新操作，更新时忽略当前记录的编码唯一性
    核心字段必填，非必填字段限制格式或长度
    请求参数验证规则：
    name（服务类型名称）：必填，字符串，最大长度 100 字符
    code（服务类型编码）：可选，字符串，最大长度 50 字符，新增时需唯一，更新时忽略自身唯一性
    description（描述）：可选，字符串，无长度限制
    status（状态）：必填，整数，仅允许值为 0（禁用）或 1（启用）
    sort_order（排序）：可选，整数，最小值 0，用于列表排序
    category（业务类别）：必填，字符串，最大长度 50 字符，服务类型所属的业务类别
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['category'] = 'required|string|max:50';

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:business_service_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:business_service_types,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '业务服务类型名称不能为空',
            'code.unique' => '业务服务类型编码已存在',
        ]);
    }

    /**
    重写 index 方法，支持按名称、业务类别、状态搜索业务服务类型列表
    请求参数：
    name（服务类型名称）：可选，字符串，非空时模糊匹配服务类型名称
    category（业务类别）：可选，字符串 / 整数，非空时精确筛选对应业务类别的服务类型
    status（状态）：可选，整数，非空时筛选对应状态的服务类型（如 0 = 禁用、1 = 启用等，需结合业务实际状态定义）
    page（页码）：可选，整数，默认 1，最小值 1，指定分页页码
    limit（每页条数）：可选，整数，默认 15，最小值 1、最大值 100，指定分页每页显示的记录数
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，获取列表结果的描述信息
    data（列表数据）：对象，包含以下字段：
    list（服务类型列表）：数组，包含业务服务类型详情对象，字段包括 id、name、category、status、sort_order 等表中字段
    total（总条数）：整数，符合条件的业务服务类型总记录数
    page（当前页码）：整数，当前分页的页码
    limit（每页条数）：整数，当前分页每页显示的记录数
    pages（总页数）：整数，分页的总页数（向上取整计算）
    @param Request $request 请求对象，包含搜索参数和分页参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含业务服务类型列表及分页信息
     */
    public function index(Request $request)
    {
        try {
            // 创建 BusinessServiceTypes 模型查询构建器
            $query = BusinessServiceTypes::query();

            // 按名称模糊查询 - 当name参数存在且不为空时进行模糊匹配
            if ($request->has('name') && $request->name !== '' && $request->name !== null) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            // 按业务类别精确筛选 - 当category参数存在且不为空时进行精确匹配
            if ($request->has('category') && $request->category !== '' && $request->category !== null) {
                $query->where('category', $request->category);
            }

            // 状态筛选 - 当status参数存在且不为空时进行状态匹配
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页处理
            // 确保页码至少为1，防止负数或0
            $page = max(1, (int) $request->get('page', 1));
            // 确保每页记录数在1-100范围内
            $limit = max(1, min(100, (int) $request->get('limit', 15)));

            // 获取符合条件的总记录数
            $total = $query->count();

            // 获取当前页的数据列表，按 sort_order 和 id 升序排列
            $list = $query->orderBy('sort_order')
                ->orderBy('id')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            // 返回成功响应，包含列表数据和分页信息
            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit), // 计算总页数
            ]);
        } catch (\Exception $e) {
            // 记录异常日志并返回失败响应
            log_exception($e, '获取业务服务类型列表失败');
            return json_fail('获取列表失败');
        }
    }
}
