<?php

namespace App\Http\Controllers\Api;

use App\Models\ApplicantTypes;
use App\Models\User;

class ApplicantTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ApplicantTypes::class;
    }
    /**
    获取申请人类型验证规则
    用于创建或更新申请人类型时的参数验证，根据操作类型动态调整编码唯一性规则
    基础验证规则：
    name（申请人类型名称）：必填，字符串，最大 100 字符
    code（申请人类型编码）：必填，字符串，最大 50 字符，默认需唯一
    description（描述）：可选，字符串
    status（状态）：必填，整数，仅允许 0（禁用）或 1（启用）
    sort_order（排序值）：可选，整数，最小值 0
    type_name（类型名称）：必填，字符串，最大 100 字符
    动态规则说明：
    新增操作（$isUpdate=false）：code 需在 applicant_types 表中唯一
    更新操作（$isUpdate=true）：code 需唯一（排除当前更新记录的 ID）
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 组合后的完整验证规则数组
     */
    /**
    获取申请人类型验证提示消息
    继承父类基础验证消息，可在此方法中补充申请人类型相关的自定义验证提示
    @return array 合并后的验证提示消息数组（父类消息 + 自定义消息）
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'type_name' => 'required|string|max:100',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:applicant_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:applicant_types,code';
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
