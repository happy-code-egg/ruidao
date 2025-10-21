<?php

namespace App\Http\Controllers\Api;

use App\Models\Cities;
use App\Models\User;

class CitiesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return Cities::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'city_name' => 'required|string|max:100',
            'city_name_en' => 'nullable|string|max:100',
            'short_name' => 'nullable|string|max:10',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:cities,code,' . $id;
        } else {
            $rules['code'] .= '|unique:cities,code';
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