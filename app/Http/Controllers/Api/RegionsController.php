<?php

namespace App\Http\Controllers\Api;

use App\Models\Regions;
use App\Models\User;

class RegionsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return Regions::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'region_name' => 'required|string|max:100',
            'region_code' => 'nullable|string|max:50',
            'parent_id' => 'nullable|integer',
            'level' => 'nullable|integer|min:1|max:5',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:regions,code,' . $id;
        } else {
            $rules['code'] .= '|unique:regions,code';
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