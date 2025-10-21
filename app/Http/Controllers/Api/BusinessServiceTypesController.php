<?php

namespace App\Http\Controllers\Api;

use App\Models\BusinessServiceTypes;
use Illuminate\Http\Request;

class BusinessServiceTypesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return BusinessServiceTypes::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // 添加特定字段验证
        $rules['category'] = 'required|string|max:50';

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:business_service_types,code,' . $id;
        } else {
            $rules['code'] .= '|unique:business_service_types,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '业务服务类型名称不能为空',
            'code.unique' => '业务服务类型编码已存在',
        ]);
    }

    /**
     * 重写 index 以支持 name/category/status 搜索
     */
    public function index(Request $request)
    {
        try {
            $query = BusinessServiceTypes::query();

            // 按名称模糊查询
            if ($request->has('name') && $request->name !== '' && $request->name !== null) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            // 按业务类别精确筛选
            if ($request->has('category') && $request->category !== '' && $request->category !== null) {
                $query->where('category', $request->category);
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页
            $page = max(1, (int) $request->get('page', 1));
            $limit = max(1, min(100, (int) $request->get('limit', 15)));

            $total = $query->count();
            $list = $query->orderBy('sort_order')
                          ->orderBy('id')
                          ->offset(($page - 1) * $limit)
                          ->limit($limit)
                          ->get();

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ]);
        } catch (\Exception $e) {
            log_exception($e, '获取业务服务类型列表失败');
            return json_fail('获取列表失败');
        }
    }
}
