<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 数据配置基础控制器
 */
abstract class BaseDataConfigController extends Controller
{
    /**
     * 获取模型类名
     */
    abstract protected function getModelClass();

    /**
     * 获取验证规则
     */
    abstract protected function getValidationRules($isUpdate = false);

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return [
            'name.required' => '名称不能为空',
            'name.max' => '名称长度不能超过100个字符',
            'code.required' => '编码不能为空',
            'code.unique' => '编码已存在',
            'code.max' => '编码长度不能超过50个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值无效',
            'sort_order.integer' => '排序必须是整数',
        ];
    }

    /**
    获取数据列表（通用分页查询）
    通用列表查询方法，支持关键字搜索、状态筛选及分页，适用于多种数据模型的列表查询场景
    请求参数：
    keyword（关键字）：可选，字符串，用于模糊匹配 name、code、description 字段
    status（状态）：可选，字符串 / 整数，用于筛选指定状态的记录（非空非 null 时生效）
    page（页码）：可选，整数，默认 1，最小 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 15，范围 1-100，每页显示的记录数
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（获取列表成功 / 获取列表失败）
    data：对象，查询成功时返回列表数据及分页信息：
    list：数组，查询到的记录列表（具体字段由模型决定）
    total：整数，符合条件的总记录数
    page：整数，当前页码
    limit：整数，每页条数
    pages：整数，总页数（ceil (total/limit) 计算）
    说明：
    模型动态获取：通过 getModelClass () 方法获取当前操作的数据模型类，实现通用化
    关键字搜索：同时匹配 name、code、description 三个字段，采用 OR 逻辑
    分页控制：page 和 limit 做了边界处理（page≥1，1≤limit≤100），避免无效参数
    排序规则：默认按 sort_order（排序号）升序，再按 id 升序排列
    异常处理：查询失败时记录异常日志，并返回统一错误提示
    @param Request $request 请求对象，包含查询参数
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含列表数据及分页信息*/
    public function index(Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页
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
            log_exception($e, '获取数据配置列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
    获取单条记录详情（通用详情查询）
    通用详情查询方法，根据记录 ID 查询指定数据模型的单条记录详情
    请求参数：
    id（记录 ID）：必填，整数 / 字符串，通过 URL 路径传递，指定待查询的记录唯一标识
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（获取详情成功 / 记录不存在 / 获取详情失败）
    data：对象，查询成功时返回记录详情（具体字段由模型决定）
    说明：
    模型动态获取：通过 getModelClass () 方法获取当前操作的数据模型类，实现通用化
    存在性校验：若根据 ID 未查询到记录，返回 "记录不存在" 的错误提示
    异常处理：查询过程中发生异常时，记录异常日志并返回统一错误提示
    @param int/string $id 记录 ID，指定待查询的记录
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含记录详情或错误提示*/
    public function show($id)
    {
        try {
            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '获取数据配置详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
    创建记录（通用创建操作）
    通用记录创建方法，支持自定义字段验证规则，自动填充创建人 / 更新人信息，适用于多种数据模型的新增场景
    请求参数：
    动态字段：根据具体模型的验证规则（通过 getValidationRules () 获取）确定，包含模型所需的各字段值
    验证规则：
    由 getValidationRules () 方法提供当前模型的字段验证规则，getValidationMessages () 提供自定义错误提示
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（创建成功 / 字段验证错误信息 / 创建失败）
    data：对象，创建成功时返回新创建的记录详情（包含自动生成的 ID 等信息）
    说明：
    动态验证：通过 getValidationRules () 和 getValidationMessages () 实现不同模型的字段验证适配
    权限关联：自动填充 created_by 和 updated_by 字段（优先取当前登录用户 ID，默认 1）
    日志预留：预留操作日志记录逻辑（注释部分），可根据需要启用
    异常处理：创建过程中发生异常时，记录异常日志并返回统一错误提示
    @param Request $request 请求对象，包含待创建记录的字段数据
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含创建结果或错误提示*/
    public function store(Request $request)
    {
        try {
            // 使用自定义验证规则和错误消息对请求数据进行验证
            $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

            // 如果验证失败，返回第一个错误消息
            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 获取当前控制器对应的数据模型类名
            $modelClass = $this->getModelClass();

            // 获取所有请求数据
            $data = $request->all();

            // 自动填充创建人ID，如果用户未登录则默认为1
            $data['created_by'] = Auth::user()->id ?? 1;

            // 自动填充更新人ID，与创建人相同
            $data['updated_by'] = $data['created_by'];

            // 使用模型创建新记录
            $item = $modelClass::create($data);

            // 记录操作日志（当前被注释，如需启用可取消注释）
            // $this->logOperation('create', $item->getTable(), $item->id, $request->all());

            // 返回创建成功的响应，包含新创建的记录数据
            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '创建数据配置失败');

            // 返回创建失败的响应
            return json_fail('创建失败');
        }
    }

    /**
    更新记录（通用更新操作）
    通用记录更新方法，支持基于 ID 的记录更新，包含记录存在性校验、自定义字段验证，自动维护更新人信息
    请求参数：
    id（记录 ID）：必填，整数 / 字符串，通过 URL 路径传递，指定待更新的记录唯一标识
    动态字段：根据具体模型的验证规则（通过 getValidationRules (true) 获取）确定，包含待更新的字段值
    验证规则：
    由 getValidationRules (true) 方法提供当前模型的更新场景验证规则（可能与创建场景不同），getValidationMessages () 提供自定义错误提示
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（更新成功 / 记录不存在 / 字段验证错误信息 / 更新失败）
    data：对象，更新成功时返回更新后的记录详情
    说明：
    存在性校验：先根据 ID 查询记录，不存在则返回 "记录不存在" 错误
    动态验证：更新场景的验证规则通过 getValidationRules (true) 获取，支持与创建场景差异化校验
    字段控制：强制排除 created_by 字段（不允许更新创建人），自动填充 updated_by（当前登录用户 ID，默认 1）
    日志预留：预留操作日志记录逻辑（注释部分），支持对比更新前后数据，可根据需要启用
    异常处理：更新过程中发生异常时，记录异常日志并返回统一错误提示
    @param Request $request 请求对象，包含待更新的字段数据
    @param int/string $id 记录 ID，指定待更新的记录
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含更新结果或错误提示*/
    public function update(Request $request, $id)
    {
        try {
            // 获取当前控制器对应的数据模型类名
            $modelClass = $this->getModelClass();

            // 根据ID查找记录，如果不存在则返回错误
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            // 使用更新场景的验证规则和错误消息对请求数据进行验证
            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            // 如果验证失败，返回第一个错误消息
            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 保存更新前的数据用于日志记录
            $oldData = $item->toArray();

            // 获取所有请求数据
            $data = $request->all();

            // 移除created_by字段，防止创建人被修改
            unset($data['created_by']);

            // 自动填充更新人ID，如果用户未登录则默认为1
            $data['updated_by'] = Auth::user()->id ?? 1;

            // 更新记录
            $item->update($data);

            // 记录操作日志（当前被注释，如需启用可取消注释）
            // $this->logOperation('update', $item->getTable(), $item->id, $request->all(), $oldData);

            // 返回更新成功的响应，包含更新后的记录数据
            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '更新数据配置失败');

            // 返回更新失败的响应
            return json_fail('更新失败');
        }
    }

    /**
    删除记录（通用删除操作）
    通用记录删除方法，根据记录 ID 执行删除操作，包含记录存在性校验，支持日志追踪删除前数据
    请求参数：
    id（记录 ID）：必填，整数 / 字符串，通过 URL 路径传递，指定待删除的记录唯一标识
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（删除成功 / 记录不存在 / 删除失败）
    说明：
    存在性校验：先根据 ID 查询记录，不存在则返回 "记录不存在" 错误
    数据备份：删除前通过 $oldData 保存记录原始数据，用于日志追溯（配合注释中的日志逻辑）
    物理删除：直接执行 delete () 方法（若模型启用软删除，则为逻辑删除）
    日志预留：预留操作日志记录逻辑（注释部分），可记录删除前的原始数据，便于数据恢复追溯
    异常处理：删除过程中发生异常时，记录异常日志并返回统一错误提示
    @param int/string $id 记录 ID，指定待删除的记录
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含删除结果或错误提示*/
    public function destroy($id)
    {
        try {
            $modelClass = $this->getModelClass();
            $item = $modelClass::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $oldData = $item->toArray();
            $item->delete();

            // 记录操作日志
            // $this->logOperation('delete', $item->getTable(), $id, [], $oldData);

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除数据配置失败');
            return json_fail('删除失败');
        }
    }

    /**
    批量更新记录状态（通用批量状态操作）
    通用批量状态更新方法，支持根据 ID 列表批量修改记录的状态（启用 / 禁用），适用于需要批量操作状态的场景
    请求参数：
    ids：必填，数组，元素为整数，需包含至少一个待更新状态的记录 ID
    status：必填，整数，仅限 0 或 1，代表目标状态（如 0 = 禁用，1 = 启用）
    验证规则：
    ids.required：ID 列表不能为空
    ids.array：ID 列表必须为数组格式
    ids.*.integer：数组元素必须为整数（记录 ID）
    status.required：状态值不能为空
    status.in:0,1：状态值只能是 0 或 1
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（批量更新成功及更新条数 / 参数验证错误信息 / 批量更新失败）
    说明：
    批量操作：通过 whereIn ('id', $request->ids) 实现多记录同时更新，高效处理批量场景
    状态限制：仅支持 0 或 1 两种状态值，确保状态更新符合业务规范
    结果反馈：返回实际更新的记录条数，便于前端展示操作效果
    日志预留：预留操作日志记录逻辑（注释部分），可记录批量操作的 ID 列表及状态值
    异常处理：批量更新过程中发生异常时，记录异常日志并返回统一错误提示
    @param Request $request 请求对象，包含待更新的 ID 列表和目标状态
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含批量更新结果或错误提示
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer',
                'status' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $modelClass = $this->getModelClass();
            $updated = $modelClass::whereIn('id', $request->ids)
                                 ->update(['status' => $request->status]);

            // 记录操作日志
            // $this->logOperation('batch_update_status', (new $modelClass)->getTable(), $request->ids, $request->all());

            return json_success("批量更新成功，共更新{$updated}条记录");

        } catch (\Exception $e) {
            log_exception($e, '批量更新状态失败');
            return json_fail('批量更新失败');
        }
    }

    /**
     * 获取选项列表（通用下拉选择数据接口）
     * 通用选项列表查询方法，返回启用状态的记录精简信息，适配前端下拉框、单选框等选择组件的数据需求
     * 请求参数：无
     * 返回参数：
     * code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
     * message：字符串，操作结果描述（获取选项成功 / 获取选项列表失败）
     * data：数组，选项列表数据，每个元素包含：
     * id：整数，记录 ID（用于选择后的值绑定）
     * name：字符串，记录名称（用于前端显示）
     * code：字符串，记录编码（可选用于标识或辅助判断）
     */
    public function options(Request $request)
    {
        try {
            $modelClass = $this->getModelClass();
            $query = $modelClass::enabled()->ordered();

            $data = $query->select('id', 'name', 'code')->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }

    /**
    记录操作日志（通用操作日志记录方法）
    统一记录用户对数据的各类操作（新增、更新、删除、批量操作等），包含操作人、操作类型、涉及数据及上下文信息，用于操作追溯和审计
    参数说明：
    $action：字符串，操作类型标识（如 "create"/"update"/"delete"/"batch_update_status" 等）
    $table：字符串，操作涉及的数据表名
    $recordId：整数 / 字符串 / 数组，操作涉及的记录 ID（单条记录为 ID，多条为数组，自动转为逗号分隔字符串）
    $newData：数组，可选，新数据（如更新后的字段值、新增的记录数据）
    $oldData：数组，可选，旧数据（如更新前的字段值、删除前的记录数据）
    日志记录内容：
    user_id：操作用户 ID（当前登录用户，通过 auth ()->id () 获取）
    action：操作类型
    table_name：操作的数据表名
    record_id：操作的记录 ID（数组转为逗号分隔字符串）
    old_data：旧数据 JSON 字符串（含中文不转义）
    new_data：新数据 JSON 字符串（含中文不转义）
    ip_address：操作 IP 地址（通过 request ()->ip () 获取）
    user_agent：客户端浏览器标识（通过 request ()->userAgent () 获取）
    说明：
    异常容错：记录日志过程中发生异常时，仅记录错误日志（不影响主流程），确保业务操作不受日志系统影响
    数据格式：新旧数据通过 json_encode 序列化，使用 JSON_UNESCAPED_UNICODE 保留中文原样
    兼容性：支持单条记录 ID（整数 / 字符串）和多条记录 ID（数组），自动处理为统一格式
    上下文信息：记录 IP 和用户代理，便于定位操作来源
    @param string $action 操作类型
    @param string $table 数据表名
    @param int|string|array $recordId 操作的记录 ID
    @param array $newData 新数据（可选）
    @param array $oldData 旧数据（可选）
     */
    protected function logOperation($action, $table, $recordId, $newData = [], $oldData = [])
    {
        try {
            \App\Models\OperationLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'table_name' => $table,
                'record_id' => is_array($recordId) ? implode(',', $recordId) : $recordId,
                'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('记录操作日志失败: ' . $e->getMessage());
        }
    }
}
