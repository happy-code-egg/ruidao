<?php

namespace App\Http\Controllers\Api;

use App\Models\ParkConfig;

class ParksConfigController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ParkConfig::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'park_name' => 'required|string|max:200',
            'park_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['park_code'] .= '|unique:parks_config,park_code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'park_name.required' => '园区名称不能为空',
            'park_name.max' => '园区名称长度不能超过200个字符',
            'park_code.max' => '园区编码长度不能超过50个字符',
            'address.max' => '园区地址长度不能超过500个字符',
            'contact_person.max' => '联系人长度不能超过100个字符',
            'contact_phone.max' => '联系电话长度不能超过50个字符',
            'sort_order.required' => '排序值不能为空',
            'sort_order.integer' => '排序值必须是整数',
            'sort_order.min' => '排序值不能小于0',
        ]);
    }

    /**
     * 重写index方法以支持特定的搜索条件
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = ParkConfig::query();

            // 园区名称搜索
            if ($request->has('park_name') && !empty($request->park_name)) {
                $query->where('park_name', 'like', '%' . $request->park_name . '%');
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
            $this->log(
                8,
                "获取园区配置列表失败：{$e->getMessage()}",
                [
                    'title' => '园区配置列表',
                    'error' => $e->getMessage(),
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]
            );
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            $data = ParkConfig::where('is_valid', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->select('id as value', 'park_name as label')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            return json_fail('获取选项列表失败');
        }
    }
}
