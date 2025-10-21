<?php

namespace App\Http\Controllers\Api;

use App\Models\ManuscriptScoringItems;

class ManuscriptScoringItemsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ManuscriptScoringItems::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'major_category' => 'required|string|max:100',
            'minor_category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'score' => 'nullable|integer|min:0',
            'max_score' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:manuscript_scoring_items,code,' . $id;
        } else {
            $rules['code'] .= '|unique:manuscript_scoring_items,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '审核打分项名称不能为空',
            'code.required' => '审核打分项编码不能为空',
            'code.unique' => '审核打分项编码已存在',
        ]);
    }
}
