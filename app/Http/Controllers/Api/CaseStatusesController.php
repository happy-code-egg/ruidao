<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStatuses;
use App\Models\User;

/**
 * 案例状态控制器
 * 负责处理案例状态配置的增删改查、数据验证等功能
 */
class CaseStatusesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CaseStatuses::class;
    }
    /**
    获取案件状态的验证规则
    验证规则说明：
    区分新增和更新操作，更新时忽略当前记录的编码唯一性
    核心字段（名称、编码、案件类型、状态名称、状态）必填，非必填字段限制格式
    请求参数验证规则：
    name（案件状态名称）：必填，字符串，最大长度 100 字符，案件状态的显示名称
    code（案件状态编码）：必填，字符串，最大长度 50 字符，新增时需唯一，更新时忽略自身唯一性
    description（描述）：可选，字符串，无长度限制，案件状态的详细说明
    status（状态）：必填，整数，仅允许值为 0（禁用）或 1（启用），控制该案件状态是否可用
    sort_order（排序）：可选，整数，最小值 0，用于案件状态列表的排序展示
    case_type（案件类型）：必填，字符串，最大长度 100 字符，该状态所属的案件类型
    status_name（状态名称）：必填，字符串，最大长度 100 字符，案件状态的具体名称（与 name 字段配合使用）
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 验证规则数组
     */
    /**
    获取案件状态的自定义验证消息
    功能说明：
    继承父类的通用验证消息，可在此方法中添加案件状态相关的特定验证提示
    自定义消息将覆盖父类中同名规则的默认消息，未自定义的仍使用父类规则
    @return array 合并后的验证消息数组，包含父类通用消息和当前模块特定消息
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'case_type' => 'required|string|max:100',
            'status_name' => 'required|string|max:100',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:case_statuses,code,' . $id;
        } else {
            $rules['code'] .= '|unique:case_statuses,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            // 可以在这里添加特定的验证消息
        ]);
    }
}
