<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 处理事项状态控制器
 *
 * 功能:
 * - 统一管理处理事项状态（名称、代码、触发规则、启用状态等）
 * - 提供列表查询、详情、创建、更新、选项获取等接口
 *
 * 路由说明:
 * - GET /api/data-config/process-statuses              列表 index
 * - GET /api/data-config/process-statuses/options      选项 options
 * - POST /api/data-config/process-statuses             创建 store
 * - GET /api/data-config/process-statuses/{id}         详情 show
 * - PUT /api/data-config/process-statuses/{id}         更新 update
 * - DELETE /api/data-config/process-statuses/{id}      删除 destroy（父类）
 * - POST /api/data-config/process-statuses/batch-status 批量启用/停用 batchUpdateStatus（父类）
 *
 * 统一响应:
 * - 成功: json_success(message, data)
 * - 失败: json_fail(message)
 *
 * 依赖:
 * - 模型 App\Models\ProcessStatus
 * - 验证 Validator
 * - 日志 log_exception 与 $this->log
 *
 * 内部说明:
 * - 部分方法从 BaseDataConfigController 继承（例如 destroy、batchUpdateStatus）
 * - 本控制器重写 options、index、store、update、show 以适配字段与业务
 */
class ProcessStatusController extends BaseDataConfigController
{
    /**
     * 功能: 返回当前控制器所管理的数据模型类名
     * 返回参数:
     * - string 模型类名 `ProcessStatus::class`
     * 内部说明:
     * - 供父类通用逻辑使用（如删除、批量操作等）
     *
     * @return string
     */
    protected function getModelClass()
    {
        return ProcessStatus::class;
    }

    /**
     * 功能: 定义创建/更新时的参数校验规则
     * 路由说明:
     * - POST /api/data-config/process-statuses             创建
     * - PUT  /api/data-config/process-statuses/{id}        更新
     * 请求参数:
     * - sort integer 可空，>=1
     * - status_name string 必填，<=100
     * - status_code string 必填，<=50，唯一
     * - trigger_rule boolean 可空
     * - is_valid boolean 可空
     * - updater string 可空，<=100
     * 返回参数:
     * - array 验证规则数组
     * 内部说明:
     * - 更新场景下唯一性规则排除当前记录：unique:process_statuses,status_code,{id}
     *
     * @param bool $isUpdate
     * @return array
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
     * 功能: 返回用于表单验证的中文提示消息
     * 返回参数:
     * - array 验证消息数组，包含 status_name.required、status_code.required 等
     * 内部说明:
     * - 与父类消息合并，确保通用提示不丢失
     *
     * @return array
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
     * 功能: 获取启用状态的处理事项状态下拉选项
     * 路由说明:
     * - GET /api/data-config/process-statuses/options
     * 请求参数:
     * - 无（如需扩展可支持关键字过滤）
     * 返回参数:
     * - 列表: [{id, name, code}]
     * - message: 获取选项成功
     * 异常处理:
     * - 捕获异常并记录日志，返回 json_fail
     * 内部说明:
     * - 依赖模型作用域 enabled()、ordered()
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * 功能: 列表查询，支持条件过滤与分页
     * 路由说明:
     * - GET /api/data-config/process-statuses
     * 请求参数:
     * - status_name string 可空，模糊查询
     * - status_code string 可空，模糊查询
     * - trigger_rule boolean 可空，精确匹配
     * - is_valid boolean 可空，精确匹配
     * - page integer 可空，默认1，>=1
     * - limit integer 可空，默认10，范围1-100
     * 返回参数:
     * - list: [{id, sort, statusName, statusCode, triggerRule, isValid, updater, updateTime}]
     * - total, page, limit, pages
     * 异常处理:
     * - 记录详细日志（title、error、status、request），返回 json_fail
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * 功能: 创建处理事项状态
     * 路由说明:
     * - POST /api/data-config/process-statuses
     * 请求参数:
     * - status_name string 必填
     * - status_code string 必填，唯一
     * - sort integer 可空，默认1
     * - trigger_rule boolean 可空，默认false
     * - is_valid boolean 可空，默认true
     * - updater string 可空，默认“系统管理员”
     * 返回参数:
     * - {id, sort, statusName, statusCode, triggerRule, isValid, updater, updateTime}
     * 异常处理:
     * - 返回第一条验证错误；失败时记录日志并返回 json_fail
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * 功能: 更新处理事项状态
     * 路由说明:
     * - PUT /api/data-config/process-statuses/{id}
     * 请求参数:
     * - id path 必填
     * - 其他字段同创建；status_code 唯一性排除当前 id
     * 返回参数:
     * - 同创建返回结构
     * 异常处理:
     * - 记录日志并返回 json_fail
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
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
     * 功能: 获取处理事项状态详情
     * 路由说明:
     * - GET /api/data-config/process-statuses/{id}
     * 请求参数:
     * - id path 必填
     * 返回参数:
     * - {id, sort, statusName, statusCode, triggerRule, isValid, updater, updateTime}
     * 异常处理:
     * - 记录日志并返回 json_fail
     *
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
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