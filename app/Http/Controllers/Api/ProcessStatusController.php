<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 处理事项状态控制器
 */
class ProcessStatusController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return ProcessStatus::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'status_name' => 'required|string|max:100',
            'status_code' => 'required|string|max:50',
            'trigger_rule' => 'nullable|boolean',
            'is_valid' => 'nullable|boolean',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id');
            $rules['status_code'] .= '|unique:process_statuses,status_code,' . $id;
        } else {
            $rules['status_code'] .= '|unique:process_statuses,status_code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'status_name.required' => '处理事项状态名称不能为空',
            'status_code.required' => '处理状态代码不能为空',
            'status_code.unique' => '处理状态代码已存在',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }

    /**
     * 获取选项列表（重写父类方法以适应字段名）
     */
    public function options(Request $request)
    {
        try {
            $data = ProcessStatus::enabled()->ordered()
                ->select('id', 'status_name as name', 'status_code as code')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取处理状态选项失败');
            return json_fail('获取选项列表失败');
        }
    }

    /**
     * 获取列表 - 重写以支持特定的搜索条件
     */
    public function index(Request $request)
    {
        try {
            $query = ProcessStatus::query();

            // 状态名称搜索
            if ($request->has('status_name') && !empty($request->status_name)) {
                $query->where('status_name', 'like', "%{$request->status_name}%");
            }

            // 状态代码搜索
            if ($request->has('status_code') && !empty($request->status_code)) {
                $query->where('status_code', 'like', "%{$request->status_code}%");
            }

            // 是否触发完成规则搜索
            if ($request->has('trigger_rule') && $request->trigger_rule !== '' && $request->trigger_rule !== null) {
                $query->where('trigger_rule', $request->trigger_rule);
            }

            // 是否有效搜索
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据，按排序字段排序
            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort,
                                 'statusName' => $item->status_name,
                                 'statusCode' => $item->status_code,
                                 'triggerRule' => (bool)$item->trigger_rule,
                                 'isValid' => (bool)$item->is_valid,
                                 'updater' => $item->updater,
                                 'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                             ];
                         });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项状态列表失败', [
                'title' => '处理事项状态',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'request' => $request->all(),
            ]);
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updater'] = $data['updater'] ?? '系统管理员';
            $data['sort'] = $data['sort'] ?? 1;
            $data['trigger_rule'] = isset($data['trigger_rule']) ? (bool)$data['trigger_rule'] : false;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;

            $item = ProcessStatus::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'statusName' => $item->status_name,
                'statusCode' => $item->status_code,
                'triggerRule' => (bool)$item->trigger_rule,
                'isValid' => (bool)$item->is_valid,
                'updater' => $item->updater,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "创建处理事项状态失败：{$e->getMessage()}", [
                'title' => '处理事项状态',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $item = ProcessStatus::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updater'] = $data['updater'] ?? '系统管理员';
            $data['trigger_rule'] = isset($data['trigger_rule']) ? (bool)$data['trigger_rule'] : $item->trigger_rule;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

            $item->update($data);

            return json_success('更新成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'statusName' => $item->status_name,
                'statusCode' => $item->status_code,
                'triggerRule' => (bool)$item->trigger_rule,
                'isValid' => (bool)$item->is_valid,
                'updater' => $item->updater,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "更新处理事项状态失败：{$e->getMessage()}", [
                'title' => '处理事项状态',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = ProcessStatus::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'statusName' => $item->status_name,
                'statusCode' => $item->status_code,
                'triggerRule' => (bool)$item->trigger_rule,
                'isValid' => (bool)$item->is_valid,
                'updater' => $item->updater,
                'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ]);

        } catch (\Exception $e) {
            $this->log(8, "获取处理事项状态详情失败：{$e->getMessage()}", [
                'title' => '处理事项状态',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取详情失败');
        }
    }
}