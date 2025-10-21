<?php

namespace App\Http\Controllers\Api;

use App\Models\CaseTypes;
use App\Models\User;

class CaseTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CaseTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'type_name' => 'required|string|max:100',
            'agency_name' => 'nullable|string|max:200',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:case_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:case_types,code';
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