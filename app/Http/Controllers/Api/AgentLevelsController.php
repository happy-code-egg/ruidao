<?php

namespace App\Http\Controllers\Api;

use App\Models\AgentLevels;
use App\Models\User;

class AgentLevelsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return AgentLevels::class;
    }
    /**
    获取代理等级验证规则
    用于创建或更新代理等级时的参数验证，根据操作类型动态调整唯一性规则
    基础验证规则：
    name（等级名称）：必填，字符串，最大 100 字符
    code（等级编码）：必填，字符串，最大 50 字符，默认需唯一
    description（等级描述）：可选，字符串
    status（状态）：必填，整数，仅允许 0（禁用）或 1（启用）
    sort_order（排序值）：可选，整数，最小值 0
    level_name（层级名称）：必填，字符串，最大 100 字符
    level_code（层级编码）：必填，字符串，最大 50 字符
    commission_rate（佣金比例）：可选，数值型，范围 0-100
    动态规则说明：
    新增操作（$isUpdate=false）：code 需在 agent_levels 表中唯一
    更新操作（$isUpdate=true）：code 需唯一（排除当前更新记录的 ID）
    @param bool $isUpdate 是否为更新操作，默认 false（新增操作）
    @return array 组合后的完整验证规则数组
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'level_name' => 'required|string|max:100',
            'level_code' => 'required|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:agent_levels,code,' . $id;
        } else {
            $rules['code'] .= '|unique:agent_levels,code';
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
