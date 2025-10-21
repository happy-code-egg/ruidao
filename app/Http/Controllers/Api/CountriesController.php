<?php

namespace App\Http\Controllers\Api;

use App\Models\Countries;
use App\Models\User;

class CountriesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return Countries::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'country_name' => 'required|string|max:100',
            'country_name_en' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:countries,code,' . $id;
        } else {
            $rules['code'] .= '|unique:countries,code';
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