<?php

namespace App\Http\Controllers\Api;

use App\Models\PublicPools;
use App\Models\User;

class PublicPoolsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return PublicPools::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'pool_name' => 'required|string|max:100',
            'pool_type' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:0',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:public_pools,code,' . $id;
        } else {
            $rules['code'] .= '|unique:public_pools,code';
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