<?php

namespace App\Http\Controllers\Api;

use App\Models\Currencies;
use App\Models\User;

class CurrenciesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return Currencies::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'currency_name' => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
            'symbol' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:currencies,code,' . $id;
        } else {
            $rules['code'] .= '|unique:currencies,code';
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