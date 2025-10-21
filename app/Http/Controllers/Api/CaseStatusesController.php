<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseStatuses;
use App\Models\User;

class CaseStatusesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CaseStatuses::class;
    }

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