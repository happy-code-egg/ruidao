<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessTypes;
use Illuminate\Http\Request;

class ProcessTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ProcessTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['category'] = 'required|string|max:50';

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:process_types,code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '处理事项类型名称不能为空',
        ]);
    }
    
}
