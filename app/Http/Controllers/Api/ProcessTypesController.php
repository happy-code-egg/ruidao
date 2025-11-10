<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessTypes;
use Illuminate\Http\Request;

/**
 * 处理事项类型控制器
 *
 * 功能:
 * - 管理处理事项类型的数据（列表、选项、创建、详情、更新、删除）
 *
 * 路由说明:
 * - GET    /api/data-config/process-types           (接口名: api.process.types.index)
 * - GET    /api/data-config/process-types/options   (接口名: api.process.types.options)
 * - POST   /api/data-config/process-types           (接口名: api.process.types.store)
 * - GET    /api/data-config/process-types/{id}      (接口名: api.process.types.show)
 * - PUT    /api/data-config/process-types/{id}      (接口名: api.process.types.update)
 * - DELETE /api/data-config/process-types/{id}      (接口名: api.process.types.destroy)
 *
 * 统一返回:
 * - 成功: `json_success(message, data)`
 * - 失败: `json_fail(message)`
 *
 * 依赖:
 * - 模型 `App\\Models\\ProcessTypes`
 * - 请求对象 `Illuminate\\Http\\Request`
 *
 * 内部说明:
 * - 通用的增删改查方法由 `BaseDataConfigController` 提供；本类主要定义模型绑定与字段验证规则。
 */
class ProcessTypesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     *
     * 功能:
     * - 返回当前控制器所使用的 Eloquent 模型类名
     *
     * 返回参数:
     * - string 模型类名，例如 `App\\Models\\ProcessTypes`
     *
     * 内部说明:
     * - 供父类通用逻辑（增删改查）动态加载模型使用
     */
    protected function getModelClass()
    {
        return ProcessTypes::class;
    }

    /**
     * 获取验证规则
     *
     * 功能:
     * - 返回创建或更新场景下的字段验证规则
     *
     * 路由说明:
     * - 创建: POST `/api/data-config/process-types`
     * - 更新: PUT  `/api/data-config/process-types/{id}`
     *
     * 参数:
     * - bool $isUpdate 是否为更新场景（更新时可能调整唯一性约束）
     *
     * 字段规则:
     * - name: 必填、字符串、最长100
     * - description: 可空、字符串
     * - status: 必填、枚举 0 或 1
     * - sort_order: 可空、整数、最小值0
     * - category: 必填、字符串、最长50
     * - code: 更新场景下（若存在该字段）唯一性校验示例：`unique:process_types,code,{id},id`
     *
     * 返回参数:
     * - array 验证规则数组
     *
     * 内部说明:
     * - `$isUpdate=true` 时示例演示对 `code` 字段的唯一性排除当前记录（如存在该字段）。
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['category'] = 'required|string|max:50';

        if ($isUpdate) {
            $id = request()->route('id');
            // 注: 若存在 `code` 字段，可按如下方式进行唯一性验证
            // $rules['code'] = ($rules['code'] ?? 'nullable|string|max:100') . '|unique:process_types,code,' . $id . ',id';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     *
     * 功能:
     * - 定义并返回自定义字段验证失败时的错误提示文案
     *
     * 返回参数:
     * - array 验证消息数组（与 `getValidationRules` 字段对应）
     *
     * 内部说明:
     * - 与父类默认消息合并，当前控制器定义的文案优先
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '处理事项类型名称不能为空',
        ]);
    }
    
}
