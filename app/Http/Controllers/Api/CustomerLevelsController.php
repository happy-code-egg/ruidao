<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerLevel;

class CustomerLevelsController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return CustomerLevel::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'level_name' => 'required|string|max:100',
            'level_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['level_code'] .= '|unique:customer_levels,level_code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'level_name.required' => '客户等级名称不能为空',
            'level_name.max' => '客户等级名称长度不能超过100个字符',
            'level_code.nullable' => '客户等级编码不能为空',
            'level_code.max' => '客户等级编码长度不能超过50个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }

    /**
     * 重写index方法以支持特定的搜索条件
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = CustomerLevel::query();

            // 客户等级名称搜索
            if ($request->has('level_name') && !empty($request->level_name)) {
                $query->where('level_name', 'like', '%' . $request->level_name . '%');
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
            $data = $query->orderBy('sort')
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
            log_exception($e, '获取客户等级列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            $data = CustomerLevel::where('is_valid', 1)
                ->orderBy('sort')
                ->orderBy('id')
                ->select('id as value', 'level_name as label', 'level_code')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            return json_fail('获取选项列表失败');
        }
    }
}
