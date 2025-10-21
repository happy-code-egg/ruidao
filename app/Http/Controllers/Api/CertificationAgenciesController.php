<?php

namespace App\Http\Controllers\Api;

use App\Models\CertificationAgencies;
use App\Models\User;

class CertificationAgenciesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CertificationAgencies::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'agency_name' => 'required|string|max:200',
            'agency_code' => 'nullable|string|max:50',
            'contact_info' => 'nullable|string',
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:certification_agencies,code,' . $id;
        } else {
            $rules['code'] .= '|unique:certification_agencies,code';
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