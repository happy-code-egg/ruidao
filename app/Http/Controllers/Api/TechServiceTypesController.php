<?php

namespace App\Http\Controllers\Api;

use App\Models\TechServiceTypes;

class TechServiceTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return TechServiceTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
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
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '科技服务类型名称不能为空'
        ]);
    }
}
