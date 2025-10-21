<?php

namespace App\Http\Controllers\Api;

use App\Models\CopyrightExpediteTypes;

class CopyrightExpediteTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CopyrightExpediteTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['days'] = 'required|integer|min:1';
        $rules['extra_fee'] = 'required|numeric|min:0';

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:copyright_expedite_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:copyright_expedite_types,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '版权加快类型名称不能为空',
            'code.required' => '版权加快类型编码不能为空',
            'code.unique' => '版权加快类型编码已存在',
        ]);
    }
}
