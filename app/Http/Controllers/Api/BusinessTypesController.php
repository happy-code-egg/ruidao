<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessTypes;
use App\Models\User;

class BusinessTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return BusinessTypes::class;
    }
    /**
    获取业务类型的验证规则
    验证规则说明：
    区分新增和更新操作，更新时忽略当前记录的编码唯一性约束
    核心字段（名称、编码、类型名称、状态）必填，非必填字段限制格式与长度
    请求参数验证规则：
    name（业务类型名称）：必填，字符串，最大长度 100 字符，业务类型的显示名称
    code（业务类型编码）：必填，字符串，最大长度 50 字符，新增时需唯一，更新时忽略自身唯一性
    description（描述）：可选，字符串，无长度限制，业务类型的详细说明
    status（状态）：必填，整数，仅允许值为 0（禁用）或 1（启用），控制该业务类型是否可用
    sort_order（排序）：可选，整数，最小值 0，用于业务类型列表的排序展示
    type_name（类型名称）：必填，字符串，最大长度 100 字符，业务类型的具体分类名称
    category（分类）：可选，字符串，最大长度 100 字符，业务类型的进一步细分分类
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 验证规则数组
     */
    /**
    获取业务类型的自定义验证消息
    功能说明：
    继承父类的通用验证消息，可在此添加业务类型相关的特定验证提示
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
            'type_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:business_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:business_types,code';
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
