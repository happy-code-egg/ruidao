<?php

namespace App\Http\Controllers\Api;

use App\Models\FollowUpMethod;
use Illuminate\Http\Request;

/**
 * 跟进方式控制器
 */
class FollowUpMethodController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return FollowUpMethod::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id') ?? request()->route('follow_up_method');
            $rules['code'] .= '|unique:follow_up_methods,code,' . $id;
        } else {
            $rules['code'] .= '|unique:follow_up_methods,code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '跟进方式名称不能为空',
            'code.required' => '跟进方式编码不能为空',
            'code.unique' => '跟进方式编码已存在',
        ]);
    }

    /**
     * 重写 index 支持 name/status 搜索
     */
    public function index(Request $request)
    {
        try {
            $query = FollowUpMethod::query();

            if ($request->has('name') && $request->name !== '') {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

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
            log_exception($e, '获取跟进方式列表失败');
            return json_fail('获取列表失败');
        }
    }
}
