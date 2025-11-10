<?php

namespace App\Http\Controllers\Api;

use App\Models\CasePhases;
use App\Models\User;

class CasePhasesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CasePhases::class;
    }

    /**
    获取案件阶段的验证规则
    验证规则说明：
    区分新增和更新操作，更新时忽略当前记录的编码唯一性约束
    核心字段（名称、编码、国家、案件类型、阶段名称、状态）必填，非必填字段限制格式与长度
    请求参数验证规则：
    name（阶段名称）：必填，字符串，最大长度 100 字符，案件阶段的显示名称
    code（阶段编码）：必填，字符串，最大长度 50 字符，新增时需唯一，更新时忽略自身唯一性
    description（描述）：可选，字符串，无长度限制，案件阶段的详细说明
    status（状态）：必填，整数，仅允许值为 0（禁用）或 1（启用），控制该案件阶段是否可用
    sort_order（排序）：可选，整数，最小值 0，用于案件阶段列表的排序展示
    country（国家）：必填，字符串，最大长度 50 字符，该阶段适用的国家
    case_type（案件类型）：必填，字符串，最大长度 100 字符，该阶段所属的案件类型
    phase_name（阶段名称）：必填，字符串，最大长度 100 字符，案件阶段的具体名称
    phase_name_en（英文阶段名称）：可选，字符串，最大长度 100 字符，案件阶段的英文名称
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 验证规则数组
     */
    /**
    获取案件阶段的自定义验证消息
    功能说明：
    继承父类的通用验证消息，可在此添加案件阶段相关的特定验证提示
    自定义消息将覆盖父类中同名规则的默认消息，未自定义的仍沿用父类规则
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
            'country' => 'required|string|max:50',
            'case_type' => 'required|string|max:100',
            'phase_name' => 'required|string|max:100',
            'phase_name_en' => 'nullable|string|max:100',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:case_phases,code,' . $id;
        } else {
            $rules['code'] .= '|unique:case_phases,code';
        }

        return $rules;
    }
    /**
     * 获取验证消息数组
     *
     * 此方法用于获取验证规则对应的错误消息，通过合并父类的验证消息和当前类的特定验证消息来构建完整的验证消息数组。
     * 子类可以在此方法中添加特定于当前类的验证消息。
     *
     * @return array 返回验证消息数组，键为验证规则，值为对应的错误消息
     */
    protected function getValidationMessages()
    {
        // 合并父类验证消息和当前类特定验证消息
        return array_merge(parent::getValidationMessages(), [
            // 可以在这里添加特定的验证消息
        ]);
    }
}
