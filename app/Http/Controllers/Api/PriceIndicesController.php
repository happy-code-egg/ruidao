<?php

namespace App\Http\Controllers\Api;

use App\Models\PriceIndices;
use Illuminate\Http\Request;

class PriceIndicesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return PriceIndices::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'index_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'base_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:price_indices,code,' . $id;
        } else {
            $rules['code'] .= '|unique:price_indices,code';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '价格指数名称不能为空'
        ]);
    }

    /**
     * 重写 index 支持 index_level 筛选
     */
    public function index(Request $request)
    {
        try {
            $query = PriceIndices::query();

            // 关键字
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('index_name', 'like', "%{$keyword}%");
                });
            }

            // 状态
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            $total = $query->count();

            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'code' => $item->code,
                                'index_name' => $item->index_name,
                                'description' => $item->description,
                                'base_value' => $item->base_value ?? '',
                                'current_value' => $item->current_value ?? '',
                                'status' => $item->status,
                                'status_text' => $item->status_text,
                                'sort_order' => $item->sort_order,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                                'created_by' => $item->creator->real_name ?? '',
                                'updated_by' => $item->updater->real_name ?? '',
                            ];
                         });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($total / $limit)
            ]);
        } catch (\Exception $e) {
            log_exception($e, '获取价格指数列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            $data = \App\Models\PriceIndices::where('status', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->select('id as value', 'index_name as label')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            return json_fail('获取选项列表失败');
        }
    }
}
