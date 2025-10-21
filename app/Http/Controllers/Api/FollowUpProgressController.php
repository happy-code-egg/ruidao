<?php

namespace App\Http\Controllers\Api;

use App\Models\FollowUpProgress;
use Illuminate\Http\Request;

/**
 * 跟进进度控制器
 */
class FollowUpProgressController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return FollowUpProgress::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'percentage' => 'required|integer|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id') ?? request()->route('follow_up_progress');
            $rules['code'] .= '|unique:follow_up_progresses,code,' . $id;
        } else {
            $rules['code'] .= '|unique:follow_up_progresses,code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '跟进进度名称不能为空',
            'code.required' => '跟进进度编码不能为空',
            'code.unique' => '跟进进度编码已存在',
            'percentage.required' => '进度百分比不能为空',
            'percentage.integer' => '进度百分比必须是整数',
            'percentage.min' => '进度百分比不能小于0',
            'percentage.max' => '进度百分比不能大于100',
        ]);
    }

    /**
     * 重写 index 支持 name/status 搜索
     */
    public function index(Request $request)
    {
        try {
            $query = FollowUpProgress::query();

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
            log_exception($e, '获取跟进进度列表失败');
            return json_fail('获取列表失败');
        }
    }
}
