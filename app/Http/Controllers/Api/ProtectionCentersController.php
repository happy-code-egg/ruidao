<?php

namespace App\Http\Controllers\Api;

use App\Models\ProtectionCenters;
use Illuminate\Http\Request;

class ProtectionCentersController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return ProtectionCenters::class;
    }

    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'name' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50',
            'center_name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:protection_centers,code,' . $id . ',id';
        }

        return $rules;
    }

    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '保护中心名称不能为空'
        ]);
    }

    /**
     * 重写 index 支持 center_name 与 keyword/status 筛选
     */
    public function index(Request $request)
    {
        try {
            $query = ProtectionCenters::query();

            if ($request->has('center_name') && $request->center_name !== '') {
                $name = $request->center_name;
                $query->where(function ($q) use ($name) {
                    $q->where('center_name', 'like', "%{$name}%")
                      ->orWhere('name', 'like', "%{$name}%");
                });
            }

            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('center_name', 'like', "%{$keyword}%");
                });
            }

            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            $total = $query->count();

            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'sort' => $item->sort,
                                'name' => $item->name,
                                'code' => $item->code,
                                'center_name' => $item->center_name,
                                'description' => $item->description,
                                'status' => $item->status,
                                'status_text' => $item->status_text,
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
            log_exception($e, '获取保护中心列表失败');
            return json_fail('获取列表失败');
        }
    }
}
