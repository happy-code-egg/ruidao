<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseProcess;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CaseProcessController extends Controller
{
    /**
    获取处理事项列表
    功能说明：
    多条件筛选案例处理事项，支持项目 ID、状态、负责人、优先级等精准筛选及关键字模糊搜索
    关联案例、处理人等关联数据，格式化时间字段与状态文本，分页返回结构化列表
    异常时记录错误日志，返回统一错误提示，保障接口稳定性
    请求参数（均为可选）：
    case_id：项目 ID，整数，精准筛选指定案例的处理事项（依赖 byCase 作用域）
    process_status：处理状态，按状态筛选（依赖 byStatus 作用域）
    assigned_to：负责人 ID，整数，筛选指定负责人的处理事项（依赖 byAssignedTo 作用域）
    priority_level：优先级，筛选对应优先级的处理事项（依赖 byPriority 作用域）
    keyword：关键字，字符串，模糊匹配处理事项名称、描述、备注字段
    page：页码，整数，默认 1，最小值 1
    limit：每页条数，整数，默认 15，取值范围 1-100
    核心逻辑：
    筛选构建：通过模型作用域（byCase/byStatus 等）和闭包实现多维度筛选，关键字支持多字段模糊匹配
    排序规则：依赖 ordered 作用域实现默认排序（需在 CaseProcess 模型中定义排序逻辑）
    关联加载：按需加载案例、负责人、创建人等关联数据，仅返回必要字段，优化查询性能
    数据格式化：转换时间字段为 "Y-m-d H:i:s" 格式，补充状态文本、优先级文本，计算是否逾期
    分页计算：手动计算总页数，返回分页核心参数（total/page/limit/pages）
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "获取列表成功" 或 "获取列表失败"
    data：对象，包含以下字段：
    list：数组，处理事项列表，每个元素包含基础信息、关联信息、时间信息、状态信息等
    total：整数，符合条件的处理事项总条数
    page：整数，当前页码
    limit：整数，每页条数
    pages：整数，总页数
    依赖说明：
    依赖 CaseProcess 模型的 byCase、byStatus、byAssignedTo、byPriority、ordered 作用域
    依赖 CaseProcess 模型的 status_text、priority_text 访问器（用于状态 / 优先级文本转换）
    依赖 CaseProcess 模型的 isOverdue 方法（用于判断是否逾期）
    @param Request $request 请求对象，包含筛选参数和分页参数
    @return \Illuminate\Http\JsonResponse 处理事项列表及分页信息响应
     */
    public function index(Request $request)
    {
        try {
            $query = CaseProcess::query();

            // 按项目ID筛选
            if ($request->filled('case_id')) {
                $query->byCase($request->case_id);
            }

            // 按状态筛选
            if ($request->filled('process_status')) {
                $query->byStatus($request->process_status);
            }

            // 按负责人筛选
            if ($request->filled('assigned_to')) {
                $query->byAssignedTo($request->assigned_to);
            }

            // 按优先级筛选
            if ($request->filled('priority_level')) {
                $query->byPriority($request->priority_level);
            }

            // 关键字搜索
            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('process_name', 'like', "%{$keyword}%")
                      ->orWhere('process_description', 'like', "%{$keyword}%")
                      ->orWhere('process_remark', 'like', "%{$keyword}%");
                });
            }

            // 排序
            $query->ordered();

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->with([
                'case:id,case_name,case_code',
                'assignedUser:id,real_name',
                'assigneeUser:id,real_name',
                'reviewerUser:id,real_name',
                'creator:id,real_name',
                'updater:id,real_name'
            ])
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'case_id' => $item->case_id,
                    'case_name' => $item->case->case_name ?? '',
                    'case_code' => $item->case->case_code ?? '',
                    'process_code' => $item->process_code,
                    'process_name' => $item->process_name,
                    'process_type' => $item->process_type,
                    'process_status' => $item->process_status,
                    'process_status_text' => $item->status_text,
                    'priority_level' => $item->priority_level,
                    'priority_text' => $item->priority_text,
                    'assigned_to' => $item->assigned_to,
                    'assigned_user_name' => $item->assignedUser->real_name ?? '',
                    'assignee' => $item->assignee,
                    'assignee_user_name' => $item->assigneeUser->real_name ?? '',
                    'is_assign' => $item->is_assign,
                    'due_date' => $item->due_date,
                    'internal_deadline' => $item->internal_deadline,
                    'official_deadline' => $item->official_deadline,
                    'customer_deadline' => $item->customer_deadline,
                    'expected_complete_date' => $item->expected_complete_date,
                    'completion_date' => $item->completion_date,
                    'process_coefficient' => $item->process_coefficient,
                    'process_description' => $item->process_description,
                    'process_result' => $item->process_result,
                    'process_remark' => $item->process_remark,
                    'is_overdue' => $item->isOverdue(),
                    'created_by' => $item->creator->real_name ?? '',
                    'updated_by' => $item->updater->real_name ?? '',
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
    创建处理事项
    功能说明：
    校验处理事项相关参数合法性，含关联表数据存在性校验（项目 ID、用户 ID 等）
    自动生成处理事项编码，记录创建人 / 更新人为当前登录用户 ID
    支持关联父处理事项、配置费用信息和附件，异常时记录错误日志并返回统一提示
    请求参数（必填项标★，其余为可选）：
    ★ case_id（项目 ID）：整数，需在 cases 表中存在，关联处理事项所属案例
    ★ process_name（处理事项名称）：字符串，最大长度 200 字符，处理事项核心名称
    process_type（处理事项类型）：字符串，最大长度 100 字符，事项分类标识
    process_status（处理状态）：整数，仅允许值为 1、2、3、4（对应具体业务状态）
    priority_level（优先级）：整数，仅允许值为 1、2、3（如 1 = 普通、2 = 重要、3 = 紧急）
    assigned_to（负责人 ID）：整数，需在 users 表中存在，事项主要负责人
    assignee（配案人 ID）：整数，需在 users 表中存在，负责分配事项的人员
    reviewer（审核人 ID）：整数，需在 users 表中存在，事项审核人员
    is_assign（是否分配）：布尔值，标识事项是否已完成分配
    due_date（到期日期）：日期格式，事项整体到期时间
    internal_deadline（内部截止日期）：日期格式，内部处理截止时间
    official_deadline（官方截止日期）：日期格式，官方要求的截止时间
    customer_deadline（客户截止日期）：日期格式，向客户承诺的截止时间
    expected_complete_date（预计完成日期）：日期格式，预估的事项完成时间
    issue_date（接收日期）：日期格式，事项接收 / 发起日期
    case_stage（案例阶段）：字符串，最大长度 50 字符，事项所属案例当前阶段
    contract_code（合同编码）：字符串，最大长度 100 字符，关联合同的编码
    process_coefficient（处理系数）：字符串，最大长度 100 字符，事项处理相关系数配置
    process_description（处理描述）：字符串，事项详细处理要求或说明
    process_remark（备注）：字符串，其他补充说明信息
    service_fees（服务费列表）：数组，存储服务费相关配置（格式需符合业务要求）
    official_fees（官费列表）：数组，存储官费相关配置（格式需符合业务要求）
    attachments（附件列表）：数组，存储附件相关信息（如文件 ID、名称等）
    parent_process_id（父处理事项 ID）：整数，需在 case_processes 表中存在，关联的上级处理事项
    核心逻辑：
    参数校验：通过 Validator 校验必填项、数据格式、关联数据存在性，自定义错误提示
    编码生成：未传入 process_code 时，通过 generateProcessCode 方法生成唯一事项编码
    数据补充：自动填充 created_by 和 updated_by 为当前登录用户 ID
    数据创建：通过 CaseProcess 模型创建处理事项记录，返回创建结果
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "创建成功" 或 "创建失败"，参数校验失败时返回具体错误信息
    data：创建成功时返回 CaseProcess 模型实例，包含完整的处理事项数据
    依赖说明：
    依赖 Auth 类获取当前登录用户 ID
    依赖 generateProcessCode 方法生成处理事项编码
    关联表（cases、users、case_processes）需存在且字段匹配
    @param Request $request 请求对象，包含创建处理事项所需的各项参数
    @return \Illuminate\Http\JsonResponse 处理事项创建结果响应
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'case_id' => 'required|integer|exists:cases,id',
                'process_name' => 'required|string|max:200',
                'process_type' => 'nullable|string|max:100',
                'process_status' => 'nullable|integer|in:1,2,3,4',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'assignee' => 'nullable|integer|exists:users,id',
                'reviewer' => 'nullable|integer|exists:users,id',
                'is_assign' => 'nullable|boolean',
                'due_date' => 'nullable|date',
                'internal_deadline' => 'nullable|date',
                'official_deadline' => 'nullable|date',
                'customer_deadline' => 'nullable|date',
                'expected_complete_date' => 'nullable|date',
                'issue_date' => 'nullable|date',
                'case_stage' => 'nullable|string|max:50',
                'contract_code' => 'nullable|string|max:100',
                'process_coefficient' => 'nullable|string|max:100',
                'process_description' => 'nullable|string',
                'process_remark' => 'nullable|string',
                'service_fees' => 'nullable|array',
                'official_fees' => 'nullable|array',
                'attachments' => 'nullable|array',
                'parent_process_id' => 'nullable|integer|exists:case_processes,id',
            ], [
                'case_id.required' => '项目ID不能为空',
                'case_id.exists' => '项目不存在',
                'process_name.required' => '处理事项名称不能为空',
                'process_name.max' => '处理事项名称不能超过200个字符',
                'assigned_to.exists' => '负责人不存在',
                'assignee.exists' => '配案人不存在',
                'parent_process_id.exists' => '父处理事项不存在',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            // 生成处理事项编码
            if (empty($data['process_code'])) {
                $data['process_code'] = $this->generateProcessCode($data['case_id']);
            }

            $processItem = CaseProcess::create($data);

            return json_success('创建成功', $processItem);

        } catch (\Exception $e) {
            log_exception($e, '创建处理事项失败');
            return json_fail('创建失败');
        }
    }

    /**
    获取处理事项详情
    功能说明：
    根据处理事项 ID 查询完整详情，关联案例、负责人、创建人等关联数据
    格式化时间字段为标准格式，补充状态文本、优先级文本，计算是否逾期
    校验数据存在性，不存在时返回明确提示，异常时记录错误日志
    请求参数：
    id（处理事项 ID）：必填，整数，处理事项的唯一标识 ID（通过 URL 路径传入）
    核心逻辑：
    数据查询：通过 CaseProcess 模型查询指定 ID 的记录，关联加载案例、相关用户等必要数据
    存在性校验：查询结果为空时，返回 "处理事项不存在" 提示
    数据格式化：
    时间字段转换为 "Y-m-d H:i:s" 格式，适配前端展示
    补充 status_text（状态文本）、priority_text（优先级文本）等关联文本信息
    调用 isOverdue 方法判断事项是否逾期
    整合关联数据的名称字段（如案例名称、负责人姓名）
    响应返回：返回格式化后的完整详情数据
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "获取详情成功"“处理事项不存在” 或 "获取详情失败"
    data：对象，包含处理事项的完整信息，涵盖：
    基础标识：id、case_id、process_code 等
    关联信息：case_name（案例名称）、assigned_user_name（负责人姓名）等
    状态信息：process_status、process_status_text、is_overdue 等
    时间信息：due_date、internal_deadline、created_at 等（均为标准格式）
    配置信息：service_fees（服务费）、official_fees（官费）、attachments（附件）等
    描述信息：process_description、process_result、process_remark 等
    依赖说明：
    依赖 CaseProcess 模型的 status_text、priority_text 访问器（状态 / 优先级文本转换）
    依赖 CaseProcess 模型的 isOverdue 方法（逾期判断逻辑）
    关联表（cases、users）需存在且字段匹配，确保关联数据正常加载
    @param int $id 处理事项 ID
    @return \Illuminate\Http\JsonResponse 处理事项详情响应
     */
    public function show($id)
    {
        try {
            $processItem = CaseProcess::with([
                'case:id,case_name,case_code',
                'assignedUser:id,real_name',
                'assigneeUser:id,real_name',
                'reviewerUser:id,real_name',
                'creator:id,real_name',
                'updater:id,real_name'
            ])->find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            $result = [
                'id' => $processItem->id,
                'case_id' => $processItem->case_id,
                'case_name' => $processItem->case->case_name ?? '',
                'case_code' => $processItem->case->case_code ?? '',
                'process_code' => $processItem->process_code,
                'process_name' => $processItem->process_name,
                'process_type' => $processItem->process_type,
                'process_status' => $processItem->process_status,
                'process_status_text' => $processItem->status_text,
                'priority_level' => $processItem->priority_level,
                'priority_text' => $processItem->priority_text,
                'assigned_to' => $processItem->assigned_to,
                'assigned_user_name' => $processItem->assignedUser->real_name ?? '',
                'assignee' => $processItem->assignee,
                'assignee_user_name' => $processItem->assigneeUser->real_name ?? '',
                'reviewer' => $processItem->reviewer,
                'reviewer_user_name' => $processItem->reviewerUser->real_name ?? '',
                'is_assign' => $processItem->is_assign,
                'due_date' => $processItem->due_date,
                'internal_deadline' => $processItem->internal_deadline,
                'official_deadline' => $processItem->official_deadline,
                'customer_deadline' => $processItem->customer_deadline,
                'expected_complete_date' => $processItem->expected_complete_date,
                'completion_date' => $processItem->completion_date,
                'issue_date' => $processItem->issue_date,
                'case_stage' => $processItem->case_stage,
                'contract_code' => $processItem->contract_code,
                'estimated_hours' => $processItem->estimated_hours,
                'actual_hours' => $processItem->actual_hours,
                'process_coefficient' => $processItem->process_coefficient,
                'process_description' => $processItem->process_description,
                'process_result' => $processItem->process_result,
                'process_remark' => $processItem->process_remark,
                'service_fees' => $processItem->service_fees,
                'official_fees' => $processItem->official_fees,
                'attachments' => $processItem->attachments,
                'parent_process_id' => $processItem->parent_process_id,
                'is_overdue' => $processItem->isOverdue(),
                'created_by' => $processItem->creator->real_name ?? '',
                'updated_by' => $processItem->updater->real_name ?? '',
                'created_at' => $processItem->created_at ? $processItem->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $processItem->updated_at ? $processItem->updated_at->format('Y-m-d H:i:s') : '',
                'completed_time' => $processItem->completed_time ? $processItem->completed_time->format('Y-m-d H:i:s') : '',
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
    更新处理事项
    功能说明：
    先校验处理事项是否存在，不存在返回明确提示
    校验更新参数合法性，含关联用户 ID 存在性校验，自定义错误提示
    自动记录更新人为当前登录用户 ID，状态变更为 “已完成” 时自动填充完成时间
    支持更新事项基本信息、状态、时间、费用、附件等数据，异常时记录错误日志
    请求参数：
    路径参数：id（处理事项 ID）：必填，整数，处理事项的唯一标识，指定待更新的记录
    请求体参数（必填项标★，其余为可选）：
    ★ process_name（处理事项名称）：字符串，最大长度 200 字符，事项核心名称
    process_type（处理事项类型）：字符串，最大长度 100 字符，事项分类标识
    process_status（处理状态）：整数，仅允许值为 1、2、3、4（对应业务状态，如 4 = 已完成）
    priority_level（优先级）：整数，仅允许值为 1、2、3（1 = 普通、2 = 重要、3 = 紧急）
    assigned_to（负责人 ID）：整数，需在 users 表中存在，事项主要负责人
    assignee（配案人 ID）：整数，需在 users 表中存在，负责分配事项的人员
    reviewer（审核人 ID）：整数，需在 users 表中存在，事项审核人员
    is_assign（是否分配）：布尔值，标识事项是否已完成分配
    due_date（到期日期）：日期格式，事项整体到期时间
    internal_deadline（内部截止日期）：日期格式，内部处理截止时间
    official_deadline（官方截止日期）：日期格式，官方要求的截止时间
    customer_deadline（客户截止日期）：日期格式，向客户承诺的截止时间
    expected_complete_date（预计完成日期）：日期格式，预估完成时间
    completion_date（完成日期）：日期格式，事项实际完成日期
    issue_date（接收日期）：日期格式，事项接收 / 发起日期
    case_stage（案例阶段）：字符串，最大长度 50 字符，事项所属案例当前阶段
    contract_code（合同编码）：字符串，最大长度 100 字符，关联合同编码
    process_coefficient（处理系数）：字符串，最大长度 100 字符，事项处理相关系数
    process_description（处理描述）：字符串，事项详细处理要求或说明
    process_result（处理结果）：字符串，事项处理完成后的结果记录
    process_remark（备注）：字符串，其他补充说明信息
    service_fees（服务费列表）：数组，服务费相关配置（格式需符合业务要求）
    official_fees（官费列表）：数组，官费相关配置（格式需符合业务要求）
    attachments（附件列表）：数组，附件相关信息（如文件 ID、名称等）
    核心逻辑：
    存在性校验：查询指定 ID 的处理事项，不存在则返回 “处理事项不存在”
    参数校验：通过 Validator 校验必填项、数据格式、关联数据存在性，返回具体错误提示
    数据补充：自动填充 updated_by 为当前登录用户 ID
    状态联动：若更新后状态为 “已完成”（CaseProcess::STATUS_COMPLETED）且原状态未完成，自动设置 completed_time 为当前时间，未传 completion_date 时填充为当前日期
    数据更新：通过模型实例更新处理事项记录，返回更新结果
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 “更新成功”“处理事项不存在”“更新失败”，参数校验失败时返回具体错误信息
    data：更新成功时返回更新后的 CaseProcess 模型实例，包含完整的处理事项数据
    依赖说明：
    依赖 Auth 类获取当前登录用户 ID
    依赖 CaseProcess 模型的 STATUS_COMPLETED 常量（已完成状态码）和 isCompleted 方法（判断是否已完成）
    关联表 users 需存在且字段匹配，确保用户 ID 校验有效
    @param Request $request 请求对象，包含更新所需的各项参数
    @param int $id 处理事项 ID，指定待更新的记录
    @return \Illuminate\Http\JsonResponse 处理事项更新结果响应
     */
    public function update(Request $request, $id)
    {
        try {
            $processItem = CaseProcess::find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            $validator = Validator::make($request->all(), [
                'process_name' => 'required|string|max:200',
                'process_type' => 'nullable|string|max:100',
                'process_status' => 'nullable|integer|in:1,2,3,4',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'assignee' => 'nullable|integer|exists:users,id',
                'reviewer' => 'nullable|integer|exists:users,id',
                'is_assign' => 'nullable|boolean',
                'due_date' => 'nullable|date',
                'internal_deadline' => 'nullable|date',
                'official_deadline' => 'nullable|date',
                'customer_deadline' => 'nullable|date',
                'expected_complete_date' => 'nullable|date',
                'completion_date' => 'nullable|date',
                'issue_date' => 'nullable|date',
                'case_stage' => 'nullable|string|max:50',
                'contract_code' => 'nullable|string|max:100',
                'process_coefficient' => 'nullable|string|max:100',
                'process_description' => 'nullable|string',
                'process_result' => 'nullable|string',
                'process_remark' => 'nullable|string',
                'service_fees' => 'nullable|array',
                'official_fees' => 'nullable|array',
                'attachments' => 'nullable|array',
            ], [
                'process_name.required' => '处理事项名称不能为空',
                'process_name.max' => '处理事项名称不能超过200个字符',
                'assigned_to.exists' => '负责人不存在',
                'assignee.exists' => '配案人不存在',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();

            // 如果状态变为已完成，设置完成时间
            if (isset($data['process_status']) && $data['process_status'] == CaseProcess::STATUS_COMPLETED && !$processItem->isCompleted()) {
                $data['completed_time'] = now();
                if (!isset($data['completion_date'])) {
                    $data['completion_date'] = now()->toDateString();
                }
            }

            $processItem->update($data);

            return json_success('更新成功', $processItem);

        } catch (\Exception $e) {
            log_exception($e, '更新处理事项失败');
            return json_fail('更新失败');
        }
    }

    /**
    删除处理事项
    功能说明：
    支持删除指定 ID 的处理事项，删除前校验数据存在性和关联子事项，避免数据关联异常
    异常时记录错误日志并返回统一提示，保障数据操作安全性
    请求参数：
    id（处理事项 ID）：必填，整数，处理事项的唯一标识 ID（通过 URL 路径传入）
    核心逻辑：
    存在性校验：查询指定 ID 的处理事项，不存在则返回 “处理事项不存在” 提示
    关联校验：检查是否存在父 ID 为当前事项 ID 的子处理事项，存在则禁止删除并返回提示
    数据删除：无关联子事项时，执行删除操作（默认支持软删除，需模型开启 SoftDeletes）
    响应返回：删除成功返回成功提示，失败返回错误信息
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 “删除成功”“处理事项不存在”“该处理事项下还有子事项，无法删除” 或 “删除失败”
    依赖说明：
    若需软删除，CaseProcess 模型需开启 SoftDeletes trait 并添加 deleted_at 字段
    子事项通过 parent_process_id 字段与当前事项关联，需确保关联字段定义一致
    @param int $id 处理事项 ID
    @return \Illuminate\Http\JsonResponse 处理事项删除结果响应
     */
    public function destroy($id)
    {
        try {
            $processItem = CaseProcess::find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            // 检查是否有子处理事项
            $hasChildren = CaseProcess::where('parent_process_id', $id)->exists();
            if ($hasChildren) {
                return json_fail('该处理事项下还有子事项，无法删除');
            }

            $processItem->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除处理事项失败');
            return json_fail('删除失败');
        }
    }

    /**
    根据项目 ID 获取处理事项列表
    功能说明：
    按指定项目 ID（caseId）查询关联的所有处理事项，关联负责人、配案人等用户信息
    格式化状态文本、用户信息等数据，按默认排序返回列表，异常时记录错误日志
    请求参数：
    caseId（项目 ID）：必填，整数，通过 URL 路径传入，指定要查询的案例 ID
    核心逻辑：
    筛选查询：通过 byCase 模型作用域精准筛选指定项目 ID 的处理事项
    关联加载：按需加载负责人、配案人、审核人的用户信息，仅返回 id 和 real_name 字段
    排序规则：依赖 ordered 作用域实现默认排序（需在 CaseProcess 模型中定义排序逻辑）
    数据格式化：
    补充 process_status_text（状态文本）、is_overdue（是否逾期）等衍生字段
    整合用户完整信息（assigned_user）和用户名（assigned_user_name），适配不同展示场景
    保留费用、附件等配置信息，返回结构化数据
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "获取处理事项列表成功" 或 "获取处理事项列表失败"
    data：数组，包含指定项目下的所有处理事项，每个元素包含基础信息、关联用户信息、时间信息、配置信息等
    依赖说明：
    依赖 CaseProcess 模型的 byCase 作用域（按 case_id 筛选逻辑）和 ordered 作用域（排序逻辑）
    依赖 CaseProcess 模型的 status_text 访问器（状态文本转换）和 isOverdue 方法（逾期判断）
    关联表 users 需存在且字段匹配，确保用户信息正常加载
    @param int $caseId 项目 ID（案例 ID）
    @return \Illuminate\Http\JsonResponse 指定项目下的处理事项列表响应
     */
    public function getByCaseId($caseId)
    {
        try {
            $processItems = CaseProcess::byCase($caseId)
                ->with([
                    'assignedUser:id,real_name',
                    'assigneeUser:id,real_name',
                    'reviewerUser:id,real_name'
                ])
                ->ordered()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'process_name' => $item->process_name,
                        'process_type' => $item->process_type,
                        'process_status' => $item->process_status,
                        'process_status_text' => $item->status_text,
                        'assigned_to' => $item->assigned_to,
                        'assigned_user' => $item->assignedUser,
                        'assigned_user_name' => $item->assignedUser->real_name ?? '',
                        'assignee' => $item->assignee,
                        'assignee_user' => $item->assigneeUser,
                        'assignee_user_name' => $item->assigneeUser->real_name ?? '',
                        'reviewer' => $item->reviewer,
                        'reviewer_user' => $item->reviewerUser,
                        'reviewer_user_name' => $item->reviewerUser->real_name ?? '',
                        'is_assign' => $item->is_assign,
                        'due_date' => $item->due_date,
                        'internal_deadline' => $item->internal_deadline,
                        'official_deadline' => $item->official_deadline,
                        'customer_deadline' => $item->customer_deadline,
                        'expected_complete_date' => $item->expected_complete_date,
                        'issue_date' => $item->issue_date,
                        'case_stage' => $item->case_stage,
                        'contract_code' => $item->contract_code,
                        'process_coefficient' => $item->process_coefficient,
                        'process_description' => $item->process_description,
                        'process_remark' => $item->process_remark,
                        'service_fees' => $item->service_fees,
                        'official_fees' => $item->official_fees,
                        'attachments' => $item->attachments,
                        'is_overdue' => $item->isOverdue(),
                    ];
                });

            return json_success('获取处理事项列表成功', $processItems);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取处理事项列表失败');
        }
    }

    /**
    获取需要更新处理事项的项目列表
    功能说明：
    筛选存在待更新处理事项的项目，关联客户、处理事项、相关用户表，统计核心指标
    支持多条件模糊搜索，分页返回结构化数据，包含项目基础信息、处理事项统计、操作人信息等
    异常时记录错误日志并返回具体提示，适配前端列表展示场景
    请求参数（均为可选）：
    page：页码，整数，默认 1，指定分页页码
    limit：每页条数，整数，默认 10，指定单页返回记录数
    ourRefNumber：我方参考号，字符串，模糊匹配项目的 our_ref_number 字段
    applicationNo：申请号，字符串，模糊匹配项目的 application_no 字段
    clientName：客户名称，字符串，模糊匹配客户表的 customer_name 字段
    caseName：项目名称，字符串，模糊匹配项目的 case_name 字段
    updateStatus：更新状态，暂未实现筛选逻辑，预留用于后续状态筛选扩展
    核心逻辑：
    多表关联：关联合同项目表（contract_cases）、客户表（customers）、处理事项表（case_processes）、用户表（users）
    字段筛选与统计：
    基础字段：项目 ID、我方参考号、申请号、项目名称、客户名称
    统计字段：处理事项总数（process_count）、处理中事项数（processing_count）、最新更新时间（update_time）
    关联字段：创建人姓名（creator_name）、处理人姓名（processor_name）（多用户用逗号拼接）
    数据过滤：仅查询存在处理事项（cp.id 不为空）的项目
    搜索条件：支持我方参考号、申请号、客户名称、项目名称的模糊匹配
    分页排序：按最新更新时间（update_time）倒序排列，手动计算分页参数
    数据格式化：补充序号（serialNo）、更新类型（updateType）、更新状态（updateStatus），适配前端展示
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "查询成功" 或 "查询失败 + 具体错误信息"
    data：对象，包含以下字段：
    list：数组，项目列表，每个元素包含序号、项目信息、统计信息、操作人信息、更新状态等
    total：整数，符合条件的项目总条数
    currentPage：整数，当前页码
    pageSize：整数，每页条数
    关键说明：
    更新状态（updateStatus）：processing_count>0 时为 "processing"（有处理中事项），否则为 "pending"
    多用户拼接：创建人、处理人支持多个，通过 GROUP_CONCAT 去重后用逗号分隔
    最新更新时间：取该项目下所有处理事项的最新更新时间（MAX (cp.updated_at)）
    @param Request $request 请求对象，包含筛选参数和分页参数
    @return \Illuminate\Http\JsonResponse 待更新处理事项的项目列表及分页信息响应
     */
    public function getUpdateList(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $ourRefNumber = $request->get('ourRefNumber');
            $applicationNo = $request->get('applicationNo');
            $clientName = $request->get('clientName');
            $caseName = $request->get('caseName');
            $updateStatus = $request->get('updateStatus');

            // 构建查询 - 获取有处理事项需要更新的项目
            $query = \DB::table('contract_cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('case_processes as cp', 'cc.id', '=', 'cp.case_id')
                ->leftJoin('users as creator', 'cp.created_by', '=', 'creator.id')
                ->leftJoin('users as processor', 'cp.assigned_to', '=', 'processor.id')
                ->select([
                    'cc.id',
                    'cc.our_ref_number',
                    'cc.application_no',
                    'cc.case_name',
                    'c.customer_name as client_name',
                    \DB::raw('COUNT(cp.id) as process_count'),
                    \DB::raw('SUM(CASE WHEN cp.process_status = 2 THEN 1 ELSE 0 END) as processing_count'),
                    \DB::raw('MAX(cp.updated_at) as update_time'),
                    \DB::raw('GROUP_CONCAT(DISTINCT creator.name) as creator_name'),
                    \DB::raw('GROUP_CONCAT(DISTINCT processor.name) as processor_name')
                ])
                ->whereNotNull('cp.id')
                ->groupBy('cc.id', 'cc.our_ref_number', 'cc.application_no', 'cc.case_name', 'c.customer_name');

            // 添加搜索条件
            if ($ourRefNumber) {
                $query->where('cc.our_ref_number', 'like', "%{$ourRefNumber}%");
            }
            if ($applicationNo) {
                $query->where('cc.application_no', 'like', "%{$applicationNo}%");
            }
            if ($clientName) {
                $query->where('c.customer_name', 'like', "%{$clientName}%");
            }
            if ($caseName) {
                $query->where('cc.case_name', 'like', "%{$caseName}%");
            }

            // 获取总数
            $total = $query->get()->count();

            // 分页查询
            $list = $query->orderBy('update_time', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get()
                ->map(function ($item, $index) use ($page, $limit) {
                    return [
                        'id' => $item->id,
                        'serialNo' => ($page - 1) * $limit + $index + 1,
                        'ourRefNumber' => $item->our_ref_number,
                        'applicationNo' => $item->application_no,
                        'clientName' => $item->client_name,
                        'caseName' => $item->case_name,
                        'updateType' => '更新处理事项',
                        'updateStatus' => $item->processing_count > 0 ? 'processing' : 'pending',
                        'createTime' => $item->update_time,
                        'creator' => $item->creator_name,
                        'updateTime' => $item->update_time,
                        'processor' => $item->processor_name
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'currentPage' => (int)$page,
                    'pageSize' => (int)$limit
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('获取项目更新列表失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取项目处理详情
    功能说明：
    根据项目 ID（caseId）查询项目基本信息及关联的所有处理事项详情
    关联客户表、用户表（负责人 / 审核人）补充关联数据，异常时记录错误日志并返回明确提示
    请求参数：
    caseId（项目 ID）：必填，整数，通过 URL 路径传入，指定要查询的项目唯一标识
    核心逻辑：
    项目信息查询：关联合同项目表（contract_cases）和客户表（customers），获取项目基础信息及客户名称
    存在性校验：项目查询结果为空时，返回 404 状态码和 “项目不存在” 提示
    处理事项查询：关联处理事项表（case_processes）和用户表（users），获取该项目下所有处理事项及负责人、审核人姓名
    数据整合：返回项目基本信息和处理事项列表的组合数据，适配前端详情展示场景
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "查询成功"“项目不存在” 或 "查询失败 + 具体错误信息"
    data：对象，包含以下字段：
    case：对象，项目基本信息（含合同项目表所有字段及 client_name（客户名称））
    process_items：数组，处理事项列表，每个元素包含处理事项完整字段及 processor（负责人姓名）、reviewer_name（审核人姓名）
    关联表说明：
    contract_cases（项目表）：存储项目核心信息，通过 id 关联处理事项表
    customers（客户表）：存储客户信息，通过 customer_id 与项目表关联，补充客户名称
    case_processes（处理事项表）：存储项目关联的处理事项，通过 case_id 与项目表关联
    users（用户表）：存储用户信息，通过 assigned_to（负责人 ID）、reviewer（审核人 ID）与处理事项表关联
    @param int $caseId 项目 ID
    @return \Illuminate\Http\JsonResponse 项目基本信息及处理事项详情响应
     */
    public function getCaseDetail($caseId)
    {
        try {
            // 获取项目基本信息
            $case = \DB::table('contract_cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->where('cc.id', $caseId)
                ->select([
                    'cc.*',
                    'c.customer_name as client_name'
                ])
                ->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '项目不存在'
                ], 404);
            }

            // 获取处理事项列表
            $processItems = \DB::table('case_processes as cp')
                ->leftJoin('users as assigned', 'cp.assigned_to', '=', 'assigned.id')
                ->leftJoin('users as reviewer', 'cp.reviewer', '=', 'reviewer.id')
                ->where('cp.case_id', $caseId)
                ->select([
                    'cp.*',
                    'assigned.name as processor',
                    'reviewer.name as reviewer_name'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => [
                    'case' => $case,
                    'process_items' => $processItems
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('获取项目处理详情失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取项目的处理事项列表
    功能说明：
    根据项目 ID（caseId）查询该项目关联的所有处理事项，关联负责人和审核人的用户信息
    直接返回处理事项完整字段及关联用户姓名，异常时记录错误日志并返回具体提示
    请求参数：
    caseId（项目 ID）：必填，整数，通过 URL 路径传入，指定要查询的项目唯一标识
    核心逻辑：
    多表关联：关联处理事项表（case_processes）与用户表（users），分别通过 assigned_to（负责人 ID）、reviewer（审核人 ID）关联
    条件筛选：精准匹配 case_id 为指定项目 ID 的处理事项
    字段选择：查询处理事项表所有字段，同时获取负责人姓名（processor）、审核人姓名（reviewer_name）
    响应返回：直接返回查询结果集合，适配前端列表展示或数据二次处理场景
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "查询成功" 或 "查询失败 + 具体错误信息"
    data：数组，包含指定项目下的所有处理事项，每个元素包含：
    处理事项完整字段（case_processes 表所有字段）
    processor：负责人姓名（关联 users 表 name 字段）
    reviewer_name：审核人姓名（关联 users 表 name 字段）
    关联表说明：
    case_processes（处理事项表）：存储处理事项核心信息，case_id 字段关联项目
    users（用户表）：存储用户基础信息，通过 ID 关联获取负责人、审核人姓名
    @param int $caseId 项目 ID
    @return \Illuminate\Http\JsonResponse 项目关联的处理事项列表响应
     */
    public function getCaseProcesses($caseId)
    {
        try {
            $processItems = \DB::table('case_processes as cp')
                ->leftJoin('users as assigned', 'cp.assigned_to', '=', 'assigned.id')
                ->leftJoin('users as reviewer', 'cp.reviewer', '=', 'reviewer.id')
                ->where('cp.case_id', $caseId)
                ->select([
                    'cp.*',
                    'assigned.name as processor',
                    'reviewer.name as reviewer_name'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => $processItems
            ]);

        } catch (\Exception $e) {
            \Log::error('获取处理事项列表失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    更新项目处理事项
    功能说明：
    支持批量更新指定项目下的处理事项，可选择特定事项或全量更新，字段映射前端表单参数
    开启数据库事务保障数据一致性，异常时回滚事务并记录错误日志
    请求参数：
    路径参数：caseId（项目 ID）：必填，整数，指定待更新处理事项所属的项目唯一标识
    请求体参数（均为可选，需配合使用）：
    processForm：对象，存储待更新的字段值，键为前端参数名，值为对应更新内容
    包含字段：processStatus（处理状态）、completionDate（完成日期）、clientDeadline（客户截止日期）等
    selectedProcessItems：数组，整数类型，指定要更新的处理事项 ID 集合（为空时更新项目下所有处理事项）
    核心逻辑：
    事务开启：启动数据库事务，确保批量更新操作的原子性
    条件分支：
    有选中事项（selectedProcessItems 非空）：循环遍历每个事项 ID，精准匹配 ID 和项目 ID 进行更新
    无选中事项：直接更新该项目下所有处理事项（按 caseId 筛选）
    字段更新：映射前端表单参数到数据库字段，自动填充更新时间（updated_at）和更新人 ID（updated_by）
    事务提交：所有更新操作完成后提交事务，返回成功提示
    异常处理：出现错误时回滚事务，记录错误日志并返回具体失败信息
    字段映射规则：
    | 前端参数名 | 数据库字段名 | 说明 |
    |---------------------|----------------------|--------------------------|
    | processStatus | process_status | 处理状态 |
    | completionDate | completion_date | 完成日期 |
    | clientDeadline | customer_deadline | 客户截止日期 |
    | expectedCompletionDate | expected_complete_date | 预计完成日期 |
    | topicDate | issue_date | 接收日期 |
    | contractCode | contract_code | 合同编码 |
    | processQuantity | process_coefficient | 处理系数 |
    | contentDeadline | internal_deadline | 内部截止日期 |
    | updateReason | process_remark | 备注（更新原因） |
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "更新成功" 或 "更新失败 + 具体错误信息"
    依赖说明：
    依赖 auth ()->id () 获取当前登录用户 ID，未获取到时默认更新人 ID 为 1
    前端参数名与数据库字段名的映射需保持一致，确保更新字段匹配
    @param int $caseId 项目 ID
    @param Request $request 请求对象，包含更新字段表单和选中事项 ID 集合
    @return \Illuminate\Http\JsonResponse 处理事项批量更新结果响应
     */
    public function updateCaseProcesses($caseId, Request $request)
    {
        try {
            \DB::beginTransaction();

            $processForm = $request->get('processForm', []);
            $selectedProcessItems = $request->get('selectedProcessItems', []);

            // 如果有选中的处理事项，更新它们
            if (!empty($selectedProcessItems)) {
                foreach ($selectedProcessItems as $processId) {
                    \DB::table('case_processes')
                        ->where('id', $processId)
                        ->where('case_id', $caseId)
                        ->update([
                            'process_status' => $processForm['processStatus'] ?? null,
                            'completion_date' => $processForm['completionDate'] ?? null,
                            'customer_deadline' => $processForm['clientDeadline'] ?? null,
                            'expected_complete_date' => $processForm['expectedCompletionDate'] ?? null,
                            'issue_date' => $processForm['topicDate'] ?? null,
                            'contract_code' => $processForm['contractCode'] ?? null,
                            'process_coefficient' => $processForm['processQuantity'] ?? null,
                            'internal_deadline' => $processForm['contentDeadline'] ?? null,
                            'process_remark' => $processForm['updateReason'] ?? null,
                            'updated_at' => now(),
                            'updated_by' => auth()->id() ?? 1
                        ]);
                }
            } else {
                // 如果没有选中特定处理事项，更新该项目的所有处理事项
                \DB::table('case_processes')
                    ->where('case_id', $caseId)
                    ->update([
                        'process_status' => $processForm['processStatus'] ?? null,
                        'completion_date' => $processForm['completionDate'] ?? null,
                        'customer_deadline' => $processForm['clientDeadline'] ?? null,
                        'expected_complete_date' => $processForm['expectedCompletionDate'] ?? null,
                        'issue_date' => $processForm['topicDate'] ?? null,
                        'contract_code' => $processForm['contractCode'] ?? null,
                        'process_coefficient' => $processForm['processQuantity'] ?? null,
                        'internal_deadline' => $processForm['contentDeadline'] ?? null,
                        'process_remark' => $processForm['updateReason'] ?? null,
                        'updated_at' => now(),
                        'updated_by' => auth()->id() ?? 1
                    ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新成功'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('更新处理事项失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    批量更新处理事项
    功能说明：
    支持批量更新多个处理事项的自定义字段，每个事项可独立设置更新内容
    开启数据库事务保障批量操作原子性，异常时回滚事务并记录错误日志
    请求参数：
    updates（批量更新数组）：必填，数组类型，每个元素为单个处理事项的更新配置
    单个元素结构：
    id：整数，必填，处理事项的唯一标识 ID
    data：对象，必填，存储该事项需更新的字段键值对（键为数据库字段名）
    核心逻辑：
    事务开启：启动数据库事务，确保所有更新操作要么全部成功，要么全部回滚
    循环更新：遍历 updates 数组，对每个含有效 ID 和 data 的元素执行更新
    字段补充：自动为每个更新添加 updated_at（当前时间）和 updated_by（更新人 ID）字段
    事务提交：所有更新完成后提交事务，返回成功提示
    异常处理：出现错误时回滚事务，记录错误日志并返回具体失败信息
    字段说明：
    data 对象支持的字段：与 case_processes 表字段一致（如 process_status、due_date、process_remark 等）
    updated_by：优先获取当前登录用户 ID（auth ()->id ()），未获取到时默认值为 1
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 "批量更新成功" 或 "批量更新失败 + 具体错误信息"
    依赖说明：
    依赖 auth ()->id () 获取当前登录用户 ID，未登录时使用默认更新人 ID
    data 对象中的字段名需与 case_processes 表字段完全匹配，否则更新无效
    @param Request $request 请求对象，包含批量更新的配置数组
    @return \Illuminate\Http\JsonResponse 批量更新结果响应
     */
    public function batchUpdate(Request $request)
    {
        try {
            \DB::beginTransaction();

            $updates = $request->get('updates', []);

            foreach ($updates as $update) {
                if (isset($update['id']) && isset($update['data'])) {
                    \DB::table('case_processes')
                        ->where('id', $update['id'])
                        ->update(array_merge($update['data'], [
                            'updated_at' => now(),
                            'updated_by' => auth()->id() ?? 1
                        ]));
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => '批量更新成功'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('批量更新处理事项失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '批量更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    生成处理事项编码
    功能说明：
    基于所属项目的案例编码生成唯一处理事项编码，项目不存在时生成默认编码，保障编码可读性和唯一性
    编码规则：
    项目存在时：案例编码 + "-P" + 3 位顺序号（不足 3 位补 0）
    项目不存在时：固定前缀 "PROC" + 时间戳（YmdHis 格式） + 3 位随机数（100-999）
    生成逻辑：
    查询指定 ID 的项目（Cases 模型），获取案例编码（case_code）
    统计该项目下已存在的处理事项数量，顺序号 = 统计数 + 1
    用 str_pad 方法将顺序号补为 3 位（左侧补 0），拼接案例编码和 "-P" 生成最终编码
    项目查询失败时，按默认规则生成编码，避免业务阻塞
    编码示例：
    项目存在（案例编码为 CASE202406）：CASE202406-P001、CASE202406-P002
    项目不存在：PROC20240620153045678、PROC20240620153122345
    依赖说明：
    依赖 Cases 模型查询项目信息，需确保 case_code 字段存在且有值
    依赖 CaseProcess 模型统计项目下的处理事项数量，关联字段为 case_id
    @param int $caseId 处理事项所属的项目 ID
    @return string 唯一的处理事项编码
     */
    private function generateProcessCode($caseId)
    {
        $case = Cases::find($caseId);
        if (!$case) {
            return 'PROC' . date('YmdHis') . rand(100, 999);
        }

        $caseCode = $case->case_code;
        $count = CaseProcess::where('case_id', $caseId)->count() + 1;

        return $caseCode . '-P' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
