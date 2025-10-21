<?php

namespace App\Http\Controllers\Api;

use App\Models\CommissionTypes;

class CommissionTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CommissionTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['rate'] = 'nullable|numeric|min:0|max:100';

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:commission_types,code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '提成类型名称不能为空',
        ]);
    }
}
