<?php

namespace App\Http\Controllers\Api;

use App\Models\TechServiceTypes;

class TechServiceTypesController extends BaseDataConfigController
{
    /**
     * 功能: 科技服务类型数据配置管理控制器（继承通用配置控制器）
     * 接口:
     * - GET /data-config/tech-service-types: 获取列表（index）
     * - GET /data-config/tech-service-types/options: 获取下拉选项（options）
     * - POST /data-config/tech-service-types: 创建记录（store）
     * - GET /data-config/tech-service-types/{id}: 获取详情（show）
     * - PUT /data-config/tech-service-types/{id}: 更新记录（update）
     * - DELETE /data-config/tech-service-types/{id}: 删除记录（destroy）
     * - POST /data-config/tech-service-types/batch-status: 批量启用/禁用（batchUpdateStatus）
     * 说明: 以上接口方法由 BaseDataConfigController 提供，本类仅定义模型绑定与参数校验规则/文案。
     */
    protected function getModelClass()
    {
        /**
         * 功能: 返回绑定的 Eloquent 模型类名
         * 请求参数: 无
         * 返回参数: string|class - 模型类名（TechServiceTypes::class）
         * 接口: 无（内部方法，供父类通用CRUD使用）
         */
        return TechServiceTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        /**
         * 功能: 返回创建/更新时的参数校验规则集合
         * 请求参数:
         * - isUpdate(bool, 可选): 是否为更新场景，默认 false；用于动态调整唯一性校验
         * 字段规则说明:
         * - sort(integer, 可选): 排序序号，>=1
         * - name(string, 必填): 类型名称，<=100
         * - code(string, 可选): 类型编码，<=50，唯一（更新时忽略当前ID）
         * - apply_type(string, 必填): 适用类型，<=100
         * - description(string, 可选): 描述
         * - status(enum, 必填): 状态（0=禁用，1=启用）
         * - sort_order(integer, 可选): 排序值，>=0
         * - updater(string, 可选): 更新人，<=100
         * 返回参数:
         * - array: Laravel 验证规则数组
         * 接口: 间接用于 POST /data-config/tech-service-types 与 PUT /data-config/tech-service-types/{id}
         * 内部说明:
         * - 当 isUpdate 为 true 时，通过 request()->route('id') 获取路径ID，用于唯一性校验忽略当前记录
         */
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'apply_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:tech_service_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:tech_service_types,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        /**
         * 功能: 返回参数校验的自定义提示文案
         * 请求参数: 无
         * 返回参数:
         * - array: 错误消息数组（合并父类通用消息，并覆盖/补充本模块特有文案）
         * 特别说明:
         * - 对 name 字段的必填错误提供更友好的中文提示
         * 接口: 间接用于 POST /data-config/tech-service-types 与 PUT /data-config/tech-service-types/{id}
         */
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '科技服务类型名称不能为空'
        ]);
    }
}
