<?php

namespace App\Http\Controllers\Api;

use App\Models\RelatedType;

class RelatedTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return RelatedType::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'case_type' => 'required|string|max:100',
            'type_name' => 'required|string|max:100',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['type_code'] .= '|unique:related_types,type_code,' . $id;
        } else {
            $rules['type_code'] .= '|unique:related_types,type_code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'case_type.required' => '项目类型不能为空',
            'case_type.max' => '项目类型长度不能超过100个字符',
            'type_name.required' => '相关类型名称不能为空',
            'type_name.max' => '相关类型名称长度不能超过100个字符',
            'type_code.required' => '相关类型编码不能为空',
            'type_code.unique' => '相关类型编码已存在',
            'type_code.max' => '相关类型编码长度不能超过50个字符',

            'sort_order.integer' => '排序号必须是整数',
            'sort_order.min' => '排序号不能小于0',
        ]);
    }

    /**
     * 重写index方法以支持特定的搜索条件
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = RelatedType::query();

            // 项目类型筛选
            if ($request->has('case_type') && !empty($request->case_type)) {
                $query->where('case_type', $request->case_type);
            }

            // 相关类型名称搜索
            if ($request->has('type_name') && !empty($request->type_name)) {
                $query->where('type_name', 'like', '%' . $request->type_name . '%');
            }

            // 是否有效筛选
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取相关类型列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取项目类型选项
     */
    public function getCaseTypeOptions()
    {
        try {
            $caseTypes = [
                ['value' => '发明专利', 'label' => '发明专利'],
                ['value' => '实用新型', 'label' => '实用新型'],
                ['value' => '外观设计', 'label' => '外观设计'],
                ['value' => '商标', 'label' => '商标'],
                ['value' => '版权', 'label' => '版权'],
                ['value' => '集成电路', 'label' => '集成电路'],
                ['value' => '植物新品种', 'label' => '植物新品种'],
                ['value' => '地理标志', 'label' => '地理标志'],
            ];

            return json_success('获取项目类型选项成功', $caseTypes);

        } catch (\Exception $e) {
            log_exception($e, '获取项目类型选项失败');
            return json_fail('获取项目类型选项失败');
        }
    }
}