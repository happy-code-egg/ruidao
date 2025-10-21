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