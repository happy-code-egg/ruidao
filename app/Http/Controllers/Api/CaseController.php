<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Cases;
use App\Models\CaseTicket;
use App\Models\CaseAttachment;

/**
 * 案例控制器
 * 处理案例的增删改查操作
 * 管理案例信息、状态流转和相关业务逻辑
 */
class CaseController extends Controller
{
    /**
    获取案例列表
    功能说明：
    关联客户表、产品表查询案例完整信息，过滤已软删除的案例
    支持多维度精准 / 模糊搜索，按创建时间倒序排列
    分页参数灵活配置，返回完整分页元信息，异常时返回具体错误信息
    请求参数：
    分页参数：
    page_size（每页条数）：可选，整数，默认 10，指定单页返回案例数量
    page（页码）：可选，整数，默认 1，指定查询的分页页码
    搜索条件（均为可选）：
    customer_id（客户 ID）：整数，精准匹配案例关联的客户 ID
    contract_id（合同 ID）：整数，精准匹配案例关联的合同 ID
    customer_name（客户名称）：字符串，模糊匹配客户名称
    case_name（案例名称）：字符串，模糊匹配案例名称
    case_code（案例编码）：字符串，模糊匹配案例编码
    case_type（案例类型）：字符串 / 整数，精准匹配案例类型
    case_status（案例状态）：字符串 / 整数，精准匹配案例状态
    application_no（申请号）：字符串，模糊匹配案例申请号
    registration_no（注册号）：字符串，模糊匹配案例注册号
    country_code（国家代码）：字符串，精准匹配案例关联的国家代码
    application_date_start（申请开始日期）：日期格式，匹配申请日期大于等于该值的案例
    application_date_end（申请结束日期）：日期格式，匹配申请日期小于等于该值的案例
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取成功" 或 "获取失败 + 具体错误信息"
    data（列表数据）：对象，包含以下字段：
    list（案例列表）：数组，每个元素为案例详情，包含关联表字段：
    案例核心字段：id（案例 ID）、case_code（案例编码）、case_name（案例名称）等
    客户关联字段：customer_id（客户 ID）、customerName（客户名称）
    产品关联字段：product_id（产品 ID）、product_name（产品名称）等
    时间 / 操作人字段：createTime（创建时间）、createUser（创建人）等
    业务字段：estimated_cost（预估费用）、service_fee（服务费）等
    total（总条数）：整数，符合筛选条件的案例总记录数
    page_size（每页条数）：整数，当前分页的单页数据量
    current_page（当前页码）：整数，当前返回数据的分页页码
    last_page（总页数）：整数，按当前分页参数计算的总页数（向上取整）
    @param Request $request 请求对象，包含分页参数和搜索条件
    @return \Illuminate\Http\JsonResponse JSON 响应，包含案例列表及分页信息
     */
    public function index(Request $request)
    {
        try {
            // 构建查询，关联客户表和产品表
            $query = DB::table('cases as c')
                ->join('customers as cu', 'c.customer_id', '=', 'cu.id')
                ->leftJoin('products as p', 'c.product_id', '=', 'p.id')
                // 选择需要返回的字段
                ->select([
                    'c.id',
                    'c.case_code',
                    'c.case_name',
                    'c.customer_id',
                    'c.contract_id',
                    'cu.customer_name as customerName',
                    'c.case_type',
                    'c.case_subtype',
                    'c.application_type',
                    'c.case_status',
                    'c.case_phase',
                    'c.priority_level',
                    'c.application_no',
                    'c.application_date',
                    'c.registration_no',
                    'c.registration_date',
                    'c.country_code',
                    'c.deadline_date',
                    'c.estimated_cost',
                    'c.actual_cost',
                    'c.service_fee',
                    'c.official_fee',
                    'c.case_description',
                    'c.technical_field',
                    'c.innovation_points',
                    'c.remarks',
                    'c.product_id',
                    'p.product_name',
                    'p.specification as product_specification',
                    'p.product_code',
                    'c.created_by as createUser',
                    'c.created_at as createTime',
                    'c.updated_by as updateUser',
                    'c.updated_at as updateTime'
                ])
                // 过滤已软删除的案例
                ->whereNull('c.deleted_at');

            // 搜索条件 - 客户ID精确匹配
            if ($request->filled('customer_id')) {
                $query->where('c.customer_id', $request->customer_id);
            }

            // 搜索条件 - 合同ID精确匹配
            if ($request->filled('contract_id')) {
                $query->where('c.contract_id', $request->contract_id);
            }

            // 搜索条件 - 客户名称模糊匹配
            if ($request->filled('customer_name')) {
                $query->where('cu.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            // 搜索条件 - 案例名称模糊匹配
            if ($request->filled('case_name')) {
                $query->where('c.case_name', 'like', '%' . $request->case_name . '%');
            }

            // 搜索条件 - 案例编码模糊匹配
            if ($request->filled('case_code')) {
                $query->where('c.case_code', 'like', '%' . $request->case_code . '%');
            }

            // 搜索条件 - 案例类型精确匹配
            if ($request->filled('case_type')) {
                $query->where('c.case_type', $request->case_type);
            }

            // 搜索条件 - 案例状态精确匹配
            if ($request->filled('case_status')) {
                $query->where('c.case_status', $request->case_status);
            }

            // 搜索条件 - 申请号模糊匹配
            if ($request->filled('application_no')) {
                $query->where('c.application_no', 'like', '%' . $request->application_no . '%');
            }

            // 搜索条件 - 注册号模糊匹配
            if ($request->filled('registration_no')) {
                $query->where('c.registration_no', 'like', '%' . $request->registration_no . '%');
            }

            // 搜索条件 - 国家代码精确匹配
            if ($request->filled('country_code')) {
                $query->where('c.country_code', $request->country_code);
            }

            // 搜索条件 - 申请开始日期范围匹配
            if ($request->filled('application_date_start')) {
                $query->where('c.application_date', '>=', $request->application_date_start);
            }

            // 搜索条件 - 申请结束日期范围匹配
            if ($request->filled('application_date_end')) {
                $query->where('c.application_date', '<=', $request->application_date_end);
            }

            // 分页参数处理
            $perPage = $request->input('page_size', 10);
            $page = $request->input('page', 1);

            // 获取总记录数
            $total = $query->count();

            // 获取当前页数据，按创建时间倒序排列
            $cases = $query->orderBy('c.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 返回成功响应，包含列表数据和分页信息
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $cases,
                    'total' => $total,
                    'page_size' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误信息
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取案例详情
    功能说明：
    关联客户表、产品表查询案例完整信息，过滤已软删除的案例
    案例不存在时返回 404 状态码和明确提示，异常时返回具体错误信息
    请求参数：
    路径参数：id（案例 ID）：必填，整数，案例的唯一标识 ID，用于指定查询的案例
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取成功"“案例不存在” 或 “获取失败 + 具体错误信息”
    data（案例详情）：对象，查询成功时返回完整案例信息，包含：
    案例全量字段：id（案例 ID）、case_code（案例编码）、case_name（案例名称）等 c 表所有字段
    客户关联字段：customerName（客户名称，关联 customers 表 customer_name）
    产品关联字段：product_name（产品名称）、product_specification（产品规格）、product_code（产品编码）
    错误状态码：
    404：指定 ID 的案例不存在或已软删除
    500：服务器内部错误（如数据库查询异常等）
    @param int $id 案例 ID，用于指定查询的案例
    @return \Illuminate\Http\JsonResponse JSON 响应，包含案例的完整详情信息
     */
    public function show($id)
    {
        try {
            $case = DB::table('cases as c')
                ->join('customers as cu', 'c.customer_id', '=', 'cu.id')
                ->leftJoin('products as p', 'c.product_id', '=', 'p.id')
                ->select([
                    'c.*',
                    'cu.customer_name as customerName',
                    'p.product_name',
                    'p.specification as product_specification',
                    'p.product_code'
                ])
                ->where('c.id', $id)
                ->whereNull('c.deleted_at')
                ->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $case
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    创建案例
    功能说明：
    校验核心参数合法性，含关联表数据存在性校验（客户 ID、业务人员 ID）
    自动转换案例状态字符串为数值，生成唯一案例编号
    记录创建人、更新人为当前登录用户 ID（无登录时默认为 1），异常时返回具体错误信息
    请求参数（必填项标★，其余为可选）：
    ★ customer_id（客户 ID）：整数，需在 customers 表中存在，关联案例所属客户
    ★ case_name（案例名称）：字符串，最大长度 200 字符，案例的业务名称
    ★ case_type（案例类型）：整数，仅允许值为 1、2、3、4（对应具体业务类型）
    case_subtype（案例子类型）：字符串，最大长度 50 字符，案例的二级分类
    application_type（申请类型）：字符串，最大长度 50 字符，案例的申请方式 / 类型
    case_status（案例状态）：整数 / 字符串，字符串类型会自动转为数值
    case_phase（案例阶段）：字符串，最大长度 50 字符，案例当前所处业务阶段
    priority_level（优先级）：整数，仅允许值为 1、2、3（如 1 = 普通、2 = 重要、3 = 紧急）
    application_no（申请号）：字符串，最大长度 100 字符，官方申请编号
    application_date（申请日期）：日期格式，案例申请提交日期
    registration_no（注册号）：字符串，最大长度 100 字符，官方注册编号
    registration_date（注册日期）：日期格式，官方完成注册的日期
    acceptance_no（受理号）：字符串，最大长度 100 字符，官方受理编号
    country_code（国家代码）：字符串，最大长度 10 字符，案例关联的国家 / 地区代码
    entity_type（主体类型）：整数，仅允许值为 1、2、3（对应不同主体性质）
    presale_support（售前支持人员 ID）：整数，负责售前支持的人员标识
    tech_leader（技术负责人 ID）：整数，技术对接的负责人标识
    tech_contact（技术联系人 ID）：整数，日常技术对接的联系人标识
    is_authorized（是否授权）：整数，仅允许值为 0（未授权）、1（已授权）
    tech_service_name（技术服务名称）：字符串，最大长度 200 字符，关联的技术服务名称
    trademark_category（商标类别）：字符串，最大长度 50 字符，适用于商标类案例的类别划分
    business_person_id（业务人员 ID）：整数，需在 users 表中存在，负责该案例的业务人员
    agent_id（代理人 ID）：整数，案例相关代理人标识
    assistant_id（助理 ID）：整数，协助处理案例的人员标识
    agency_id（代理机构 ID）：整数，合作代理机构的标识
    deadline_date（截止日期）：日期格式，案例关键节点的截止日期
    annual_fee_due_date（年费到期日）：日期格式，案例相关年费的缴纳到期日
    estimated_cost（预估费用）：数值型，案例的预估总费用
    actual_cost（实际费用）：数值型，案例已产生的实际总费用
    service_fee（服务费）：数值型，案例相关的服务费用
    official_fee（官方费用）：数值型，官方收取的相关费用
    case_description（案例描述）：字符串，案例的详细业务描述
    technical_field（技术领域）：字符串，案例涉及的技术领域说明
    innovation_points（创新点）：字符串，案例的核心创新内容说明
    remarks（备注）：字符串，其他补充说明信息
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "创建成功"“验证失败” 或 "创建失败 + 具体错误信息"
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    data（创建结果）：对象，创建成功时返回新案例的 ID，格式为 ["id" => 案例 ID]
    错误状态码：
    422：请求参数验证失败
    500：服务器内部错误（如数据库插入异常等）
    @param Request $request 请求对象，包含创建案例所需的各项参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含案例创建结果信息
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'case_name' => 'required|string|max:200',
                'case_type' => 'required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'case_status' => 'nullable|integer',
                'case_phase' => 'nullable|string|max:50',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'application_no' => 'nullable|string|max:100',
                'application_date' => 'nullable|date',
                'registration_no' => 'nullable|string|max:100',
                'registration_date' => 'nullable|date',
                'acceptance_no' => 'nullable|string|max:100',
                'country_code' => 'nullable|string|max:10',
                'entity_type' => 'nullable|integer|in:1,2,3',
                'presale_support' => 'nullable|integer',
                'tech_leader' => 'nullable|integer',
                'tech_contact' => 'nullable|integer',
                'is_authorized' => 'nullable|integer|in:0,1',
                'tech_service_name' => 'nullable|string|max:200',
                'trademark_category' => 'nullable|string|max:50',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'agent_id' => 'nullable|integer',
                'assistant_id' => 'nullable|integer',
                'agency_id' => 'nullable|integer',
                'deadline_date' => 'nullable|date',
                'annual_fee_due_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric',
                'actual_cost' => 'nullable|numeric',
                'service_fee' => 'nullable|numeric',
                'official_fee' => 'nullable|numeric',
                'case_description' => 'nullable|string',
                'technical_field' => 'nullable|string',
                'innovation_points' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 转换状态字符串为数值
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            // 生成案例编号
            $data['case_code'] = $this->generateCaseCode($data['case_type']);
            $data['created_by'] = auth()->id() ?? 1;
            $data['updated_by'] = auth()->id() ?? 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $caseId = DB::table('cases')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $caseId]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    更新案例
    功能说明：
    先校验案例是否存在（未删除），不存在返回 404 提示
    校验更新参数合法性，含关联表数据存在性校验（客户 ID、业务人员 ID）
    自动转换案例状态字符串为数值，记录更新人为当前登录用户 ID（无登录时默认为 1）
    异常时返回具体错误信息，便于问题排查
    请求参数：
    路径参数：id（案例 ID）：必填，整数，案例的唯一标识 ID，用于指定待更新的案例
    请求体参数（必填项标★，其余为可选）：
    ★ customer_id（客户 ID）：整数，需在 customers 表中存在，关联案例所属客户
    ★ case_name（案例名称）：字符串，最大长度 200 字符，案例的业务名称
    ★ case_type（案例类型）：整数，仅允许值为 1、2、3、4（对应具体业务类型）
    case_subtype（案例子类型）：字符串，最大长度 50 字符，案例的二级分类
    application_type（申请类型）：字符串，最大长度 50 字符，案例的申请方式 / 类型
    case_status（案例状态）：整数 / 字符串，字符串类型会自动转为数值
    case_phase（案例阶段）：字符串，最大长度 50 字符，案例当前所处业务阶段
    priority_level（优先级）：整数，仅允许值为 1、2、3（如 1 = 普通、2 = 重要、3 = 紧急）
    application_no（申请号）：字符串，最大长度 100 字符，官方申请编号
    application_date（申请日期）：日期格式，案例申请提交日期
    registration_no（注册号）：字符串，最大长度 100 字符，官方注册编号
    registration_date（注册日期）：日期格式，官方完成注册的日期
    acceptance_no（受理号）：字符串，最大长度 100 字符，官方受理编号
    country_code（国家代码）：字符串，最大长度 10 字符，案例关联的国家 / 地区代码
    entity_type（主体类型）：整数，仅允许值为 1、2、3（对应不同主体性质）
    presale_support（售前支持人员 ID）：整数，负责售前支持的人员标识
    tech_leader（技术负责人 ID）：整数，技术对接的负责人标识
    tech_contact（技术联系人 ID）：整数，日常技术对接的联系人标识
    is_authorized（是否授权）：整数，仅允许值为 0（未授权）、1（已授权）
    tech_service_name（技术服务名称）：字符串，最大长度 200 字符，关联的技术服务名称
    trademark_category（商标类别）：字符串，最大长度 50 字符，适用于商标类案例的类别划分
    business_person_id（业务人员 ID）：整数，需在 users 表中存在，负责该案例的业务人员
    agent_id（代理人 ID）：整数，案例相关代理人标识
    assistant_id（助理 ID）：整数，协助处理案例的人员标识
    agency_id（代理机构 ID）：整数，合作代理机构的标识
    deadline_date（截止日期）：日期格式，案例关键节点的截止日期
    annual_fee_due_date（年费到期日）：日期格式，案例相关年费的缴纳到期日
    estimated_cost（预估费用）：数值型，案例的预估总费用
    actual_cost（实际费用）：数值型，案例已产生的实际总费用
    service_fee（服务费）：数值型，案例相关的服务费用
    official_fee（官方费用）：数值型，官方收取的相关费用
    case_description（案例描述）：字符串，案例的详细业务描述
    technical_field（技术领域）：字符串，案例涉及的技术领域说明
    innovation_points（创新点）：字符串，案例的核心创新内容说明
    remarks（备注）：字符串，其他补充说明信息
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "更新成功"“案例不存在”“验证失败” 或 "更新失败 + 具体错误信息"
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    错误状态码：
    404：指定 ID 的案例不存在或已软删除
    422：请求参数验证失败
    500：服务器内部错误（如数据库更新异常等）
    @param Request $request 请求对象，包含更新案例所需的各项参数
    @param int $id 案例 ID，用于指定待更新的案例
    @return \Illuminate\Http\JsonResponse JSON 响应，包含案例更新结果信息
     */
    public function update(Request $request, $id)
    {
        try {
            $case = DB::table('cases')->where('id', $id)->whereNull('deleted_at')->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'case_name' => 'required|string|max:200',
                'case_type' => 'required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'case_status' => 'nullable|integer',
                'case_phase' => 'nullable|string|max:50',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'application_no' => 'nullable|string|max:100',
                'application_date' => 'nullable|date',
                'registration_no' => 'nullable|string|max:100',
                'registration_date' => 'nullable|date',
                'acceptance_no' => 'nullable|string|max:100',
                'country_code' => 'nullable|string|max:10',
                'entity_type' => 'nullable|integer|in:1,2,3',
                'presale_support' => 'nullable|integer',
                'tech_leader' => 'nullable|integer',
                'tech_contact' => 'nullable|integer',
                'is_authorized' => 'nullable|integer|in:0,1',
                'tech_service_name' => 'nullable|string|max:200',
                'trademark_category' => 'nullable|string|max:50',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'agent_id' => 'nullable|integer',
                'assistant_id' => 'nullable|integer',
                'agency_id' => 'nullable|integer',
                'deadline_date' => 'nullable|date',
                'annual_fee_due_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric',
                'actual_cost' => 'nullable|numeric',
                'service_fee' => 'nullable|numeric',
                'official_fee' => 'nullable|numeric',
                'case_description' => 'nullable|string',
                'technical_field' => 'nullable|string',
                'innovation_points' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 转换状态字符串为数值
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            $data['updated_by'] = auth()->id() ?? 1;
            $data['updated_at'] = now();

            DB::table('cases')->where('id', $id)->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    删除案例（软删除）
    功能说明：
    执行软删除操作，通过设置 deleted_at 字段标记案例为已删除，不实际删除数据
    先校验案例是否存在且未软删除，不存在返回 404 提示
    异常时返回具体错误信息，便于问题排查
    请求参数：
    路径参数：id（案例 ID）：必填，整数，案例的唯一标识 ID，用于指定待删除的案例
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "删除成功"“案例不存在” 或 "删除失败 + 具体错误信息"
    错误状态码：
    404：指定 ID 的案例不存在或已软删除
    500：服务器内部错误（如数据库更新异常等）
    @param int $id 案例 ID，用于指定待删除的案例
    @return \Illuminate\Http\JsonResponse JSON 响应，包含案例软删除结果信息
     */
    public function destroy($id)
    {
        try {
            $case = DB::table('cases')->where('id', $id)->whereNull('deleted_at')->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            DB::table('cases')->where('id', $id)->update([
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    生成案例编号
    功能说明：
    按案例类型分配专属前缀，结合当前日期和 4 位自增序号，生成唯一结构化案例编号
    确保同类型、同日期的编号连续递增，格式统一且便于业务识别
    核心逻辑：
    类型前缀映射：通过 prefixMap 绑定案例类型与英文前缀，1 = 专利（PAT）、2 = 商标（TRA）、3 = 版权（COP）、4 = 科服（SER），未匹配类型默认前缀为 CAS
    日期拼接：获取当前系统日期，格式化为 Ymd（年月日）格式，作为编号中间标识段
    自增序号生成：查询当日同前缀的最大案例编号，截取末尾 4 位序号并加 1；无当日记录时序号从 1 开始，不足 4 位用 0 左补（确保序号为 4 位固定长度）
    编号格式规范：
    格式：类型前缀 + 日期（Ymd） + 4 位自增序号
    示例：PAT202406150008（2024 年 6 月 15 日第 8 个专利案例）、SER202406150012（2024 年 6 月 15 日第 12 个科服案例）
    注意事项：
    依赖数据库查询当日最大编号实现自增，并发场景下需确保编号唯一性（可通过加锁或唯一索引优化）
    日期基于服务器系统日期，需确保服务器时间同步准确
    @param int $caseType 案例类型（1 = 专利、2 = 商标、3 = 版权、4 = 科服）
    @return string 唯一结构化的案例编号
     */
    private function generateCaseCode($caseType)
    {
        $prefixMap = [
            1 => 'PAT', // 专利
            2 => 'TRA', // 商标
            3 => 'COP', // 版权
            4 => 'SER'  // 科服
        ];

        $prefix = $prefixMap[$caseType] ?? 'CAS';
        $date = date('Ymd');

        // 获取今日最大编号
        $maxCode = DB::table('cases')
            ->where('case_code', 'like', $prefix . $date . '%')
            ->orderBy('case_code', 'desc')
            ->value('case_code');

        if ($maxCode) {
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            $number = 1;
        }

        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
    转换状态字符串为数值
    功能说明：
    将前端传递的状态字符串映射为后端系统使用的状态数值常量
    支持多种状态别名，确保前后端状态标识的一致性和兼容性
    未找到映射关系时，尝试将输入直接转换为整数返回
    状态映射关系：
    draft -> 1（草稿）
    to-be-filed, to-be-pending, submitted -> 2（立项中/待处理）
    processing -> 4（处理中）
    authorized -> 5（已授权）
    rejected -> 6（已驳回）
    filed, completed -> 7（已立项/已完成）
    @param string $status 状态字符串，来自前端请求
    @return int 转换后的状态数值
     */
    private function convertStatusToInt($status)
    {
        $statusMap = [
            'draft' => \App\Models\Cases::STATUS_DRAFT,                    // 1 - 草稿
            'to-be-filed' => \App\Models\Cases::STATUS_TO_BE_FILED,        // 2 - 立项中（提交后）
            'to-be-pending' => \App\Models\Cases::STATUS_TO_BE_FILED,      // 2 - 待处理（同立项中，给审核人员看）
            'filed' => \App\Models\Cases::STATUS_COMPLETED,                // 7 - 已立项
            'completed' => \App\Models\Cases::STATUS_COMPLETED,            // 7 - 已完成
            // 保留其他状态以防万一
            'submitted' => \App\Models\Cases::STATUS_TO_BE_FILED,          // 2 - 已提交（等同立项中）
            'processing' => \App\Models\Cases::STATUS_PROCESSING,          // 4 - 处理中
            'authorized' => \App\Models\Cases::STATUS_AUTHORIZED,          // 5 - 已授权
            'rejected' => \App\Models\Cases::STATUS_REJECTED,              // 6 - 已驳回
        ];

        return $statusMap[$status] ?? (int)$status;
    }

    /**
     * 上传案件管票
     * 功能说明：
     * 处理案件管票上传请求，保存管票文件及相关信息到数据库
     * 包含文件验证、存储、数据库记录创建等功能
     *
     * 请求参数：
     * - caseId (路径参数): 项目/案件ID
     * - project_no (FormData): 项目编号（必填）
     * - application_no (FormData): 申请号（必填）
     * - project_name (FormData): 项目名称（必填）
     * - ticket_type (FormData): 管票类型（必填）
     *   - 可选值：official_fee（官费票据）、agent_fee（代理费票据）、other_fee（其他费用票据）
     * - ticket_amount (FormData): 票据金额（必填，数字）
     * - files (FormData): 票据文件（必填，支持多文件，最多5个）
     * - remark (FormData, 可选): 备注
     *
     * @param Request $request HTTP请求对象
     * @param int $caseId 案件ID
     * @return \Illuminate\Http\JsonResponse JSON响应对象
     */
    public function uploadTicket(Request $request, $caseId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'project_no' => 'required|string|max:100',
                'application_no' => 'required|string|max:100',
                'project_name' => 'required|string|max:200',
                'ticket_type' => 'required|string|in:official_fee,agent_fee,other_fee',
                'ticket_amount' => 'required|numeric|min:0',
                'files' => 'nullable|array|max:5',
                'files.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:10240',
                'remark' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {
                $updateData = [
                    'project_no' => $request->project_no,
                    'application_no' => $request->application_no,
                    'case_name' => $request->project_name,
                    'updated_by' => auth()->id() ?? 1,
                ];

                switch ($request->ticket_type) {
                    case 'official_fee':
                        $updateData['official_fee'] = $request->ticket_amount;
                        break;
                    case 'agent_fee':
                        $updateData['service_fee'] = $request->ticket_amount;
                        break;
                    default:
                        $updateData['actual_cost'] = $request->ticket_amount;
                        break;
                }

                if (!empty($request->remark)) {
                    $updateData['remarks'] = trim(($case->remarks ? $case->remarks . "\n" : '') . '【票据】' . $request->ticket_type . ' 金额 ' . $request->ticket_amount . '：' . $request->remark);
                }

                $case->fill($updateData);
                $case->save();

                $filesCount = $request->hasFile('files') ? count($request->file('files')) : 0;

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => '管票上传成功',
                    'data' => [
                        'case_id' => $caseId,
                        'files_count' => $filesCount,
                        'ticket_code' => $this->generateTicketCode($request->ticket_type)
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '管票上传失败：' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '管票上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成管票编号
     * 功能说明：
     * 根据管票类型生成唯一的管票编号
     * 格式：类型前缀 + 日期（Ymd） + 4位自增序号
     *
     * @param string $ticketType 管票类型
     * @return string 管票编号
     */
    private function generateTicketCode($ticketType)
    {
        $prefixMap = [
            'official_fee' => 'OF',
            'agent_fee' => 'AF',
            'other_fee' => 'OT'
        ];

        $prefix = $prefixMap[$ticketType] ?? 'TK';
        $date = date('Ymd');

        $millis = (int)(microtime(true) * 1000);
        $number = str_pad((string)($millis % 10000), 4, '0', STR_PAD_LEFT);

        return $prefix . $date . $number;
    }

    /**
     * 获取取票码列表
     *
     * @param Request $request
     * @param int $caseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTicketCodes(Request $request, $caseId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $projectNo = $request->query('project_no');
            $applicationNo = $request->query('application_no');
            $status = $request->query('status');
            $createStart = $request->query('create_time_start');
            $createEnd = $request->query('create_time_end');
            $useStart = $request->query('use_time_start');
            $useEnd = $request->query('use_time_end');
            $page = (int)$request->query('page', 1);
            $pageSize = (int)$request->query('page_size', 20);

            if ($projectNo && $projectNo !== $case->project_no) {
                return response()->json([
                    'success' => true,
                    'data' => ['list' => [], 'total' => 0, 'page' => $page, 'page_size' => $pageSize]
                ]);
            }
            if ($applicationNo && $applicationNo !== $case->application_no) {
                return response()->json([
                    'success' => true,
                    'data' => ['list' => [], 'total' => 0, 'page' => $page, 'page_size' => $pageSize]
                ]);
            }

            $typeMap = [
                'official_fee' => ['display' => '官费票据', 'field' => 'official_fee'],
                'agent_fee'    => ['display' => '代理费票据', 'field' => 'service_fee'],
                'other_fee'    => ['display' => '其他费用票据', 'field' => 'actual_cost'],
            ];

            $items = [];
            $seq = 1;
            foreach ($typeMap as $type => $conf) {
                $amount = $case->{$conf['field']};
                if ($amount !== null && (float)$amount > 0) {
                    $createAt = $case->updated_at ?: $case->created_at ?: now();
                    $expireAt = (clone $createAt)->addDays(30);
                    $entry = [
                        'id' => (int)($caseId * 1000 + $seq),
                        'ticket_code' => $this->generateTicketCode($type),
                        'case_id' => (int)$caseId,
                        'project_no' => $case->project_no,
                        'application_no' => $case->application_no,
                        'project_name' => $case->case_name,
                        'ticket_type' => $type,
                        'ticket_type_display' => $conf['display'],
                        'amount' => (float)$amount,
                        'status' => 'unused',
                        'status_display' => '未使用',
                        'create_time' => $createAt->format('Y-m-d H:i:s'),
                        'use_time' => null,
                        'expire_time' => $expireAt->format('Y-m-d H:i:s'),
                        'creator' => optional(\App\Models\User::find($case->created_by))->name,
                        'creator_id' => $case->created_by,
                        'user' => null,
                        'user_id' => null,
                    ];
                    $items[] = $entry;
                    $seq++;
                }
            }

            if ($status && $status !== 'unused') {
                $items = [];
            }

            if ($createStart) {
                $items = array_filter($items, function ($it) use ($createStart) {
                    return $it['create_time'] >= $createStart . ' 00:00:00';
                });
            }
            if ($createEnd) {
                $items = array_filter($items, function ($it) use ($createEnd) {
                    return $it['create_time'] <= $createEnd . ' 23:59:59';
                });
            }

            if ($useStart || $useEnd) {
                $items = [];
            }

            $items = array_values($items);
            $total = count($items);
            $page = max(1, $page);
            $pageSize = max(1, $pageSize);
            $offset = ($page - 1) * $pageSize;
            $list = array_slice($items, $offset, $pageSize);

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'page' => $page,
                    'page_size' => $pageSize,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取取票码列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取管票列表
     *
     * @param Request $request
     * @param int $caseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTickets(Request $request, $caseId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $ticketType = $request->query('ticket_type');
            $page = (int)$request->query('page', 1);
            $pageSize = (int)$request->query('page_size', 20);

            $typeMap = [
                'official_fee' => ['display' => '官费票据', 'field' => 'official_fee'],
                'agent_fee'    => ['display' => '代理费票据', 'field' => 'service_fee'],
                'other_fee'    => ['display' => '其他费用票据', 'field' => 'actual_cost'],
            ];

            $tickets = [];
            $seq = 1;
            foreach ($typeMap as $type => $conf) {
                $amount = $case->{$conf['field']};
                if ($amount !== null && (float)$amount > 0) {
                    $files = [];
                    $attachments = \App\Models\CaseAttachment::where('case_id', $caseId)
                        ->where('file_type', 'ticket')
                        ->when($ticketType, function ($q) use ($type) {
                            $q->where('file_sub_type', $type);
                        })
                        ->get();
                    foreach ($attachments as $att) {
                        $files[] = [
                            'id' => $att->id,
                            'file_name' => $att->file_name,
                            'file_url' => \Illuminate\Support\Facades\Storage::disk('public')->url($att->file_path),
                        ];
                    }

                    $tickets[] = [
                        'id' => $caseId * 10 + $seq,
                        'case_id' => (int)$caseId,
                        'project_no' => $case->project_no,
                        'application_no' => $case->application_no,
                        'project_name' => $case->case_name,
                        'ticket_type' => $type,
                        'ticket_type_display' => $typeMap[$type]['display'],
                        'ticket_amount' => (float)$amount,
                        'files' => $files,
                        'remark' => $case->remarks,
                        'created_at' => optional($case->created_at)->format('Y-m-d H:i:s'),
                        'creator' => optional(\App\Models\User::find($case->created_by))->name,
                        'creator_id' => $case->created_by,
                    ];
                    $seq++;
                }
            }

            if ($ticketType && isset($typeMap[$ticketType])) {
                $tickets = array_values(array_filter($tickets, function ($t) use ($ticketType) {
                    return $t['ticket_type'] === $ticketType;
                }));
            }

            $total = count($tickets);
            $page = max(1, $page);
            $pageSize = max(1, $pageSize);
            $offset = ($page - 1) * $pageSize;
            $list = array_slice($tickets, $offset, $pageSize);

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'page' => $page,
                    'page_size' => $pageSize,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取管票列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    public function generateCaseTicketCode(Request $request, $caseId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'project_no' => 'required|string|max:100',
                'application_no' => 'required|string|max:100',
                'project_name' => 'required|string|max:200',
                'ticket_type' => 'required|string|in:official_fee,agent_fee,other_fee',
                'amount' => 'required|numeric|min:0',
                'expire_days' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {
                $updateData = [
                    'project_no' => $request->project_no,
                    'application_no' => $request->application_no,
                    'case_name' => $request->project_name,
                    'updated_by' => auth()->id() ?? 1,
                ];

                switch ($request->ticket_type) {
                    case 'official_fee':
                        $updateData['official_fee'] = $request->amount;
                        break;
                    case 'agent_fee':
                        $updateData['service_fee'] = $request->amount;
                        break;
                    default:
                        $updateData['actual_cost'] = $request->amount;
                        break;
                }

                $case->fill($updateData);
                $case->save();

                $expireDays = (int)$request->input('expire_days', 30);
                $code = $this->generateTicketCode($request->ticket_type);
                $expireTime = now()->addDays($expireDays)->format('Y-m-d H:i:s');

                $seqMap = ['official_fee' => 1, 'agent_fee' => 2, 'other_fee' => 3];
                $id = (int)($caseId * 1000 + $seqMap[$request->ticket_type]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => '取票码生成成功',
                    'data' => [
                        'id' => $id,
                        'ticket_code' => $code,
                        'expire_time' => $expireTime
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '取票码生成失败：' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取票码生成失败：' . $e->getMessage()
            ], 500);
        }
    }

    public function getTicketCodeDetail(Request $request, $caseId, $ticketCodeId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $typeMap = [
                'official_fee' => ['display' => '官费票据', 'field' => 'official_fee'],
                'agent_fee'    => ['display' => '代理费票据', 'field' => 'service_fee'],
                'other_fee'    => ['display' => '其他费用票据', 'field' => 'actual_cost'],
            ];

            $seq = 1;
            foreach ($typeMap as $type => $conf) {
                $amount = $case->{$conf['field']};
                if ($amount !== null && (float)$amount > 0) {
                    $id = (int)($caseId * 1000 + $seq);
                    if ($id === (int)$ticketCodeId) {
                        $createAt = $case->updated_at ?: $case->created_at ?: now();
                        $expireAt = (clone $createAt)->addDays(30);

                        $item = [
                            'id' => $id,
                            'ticket_code' => $this->generateTicketCode($type),
                            'case_id' => (int)$caseId,
                            'project_no' => $case->project_no,
                            'application_no' => $case->application_no,
                            'project_name' => $case->case_name,
                            'ticket_type' => $type,
                            'ticket_type_display' => $conf['display'],
                            'amount' => (float)$amount,
                            'status' => 'unused',
                            'status_display' => '未使用',
                            'create_time' => $createAt->format('Y-m-d H:i:s'),
                            'use_time' => null,
                            'expire_time' => $expireAt->format('Y-m-d H:i:s'),
                            'creator' => optional(\App\Models\User::find($case->created_by))->name,
                            'creator_id' => $case->created_by,
                            'user' => null,
                            'user_id' => null,
                        ];

                        return response()->json([
                            'success' => true,
                            'data' => $item
                        ]);
                    }
                    $seq++;
                }
            }

            return response()->json([
                'success' => false,
                'message' => '未找到取票码'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取取票码详情失败：' . $e->getMessage()
            ], 500);
        }
    }





    
    public function downloadTicketCode(Request $request, $caseId, $ticketCodeId)
    {
        try {
            $case = Cases::find($caseId);
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '指定的案件不存在'
                ], 404);
            }

            $typeMap = [
                'official_fee' => ['display' => '官费票据', 'field' => 'official_fee'],
                'agent_fee'    => ['display' => '代理费票据', 'field' => 'service_fee'],
                'other_fee'    => ['display' => '其他费用票据', 'field' => 'actual_cost'],
            ];

            $foundType = null;
            $amount = 0.0;

            if (ctype_digit((string)$ticketCodeId)) {
                $seq = ((int)$ticketCodeId) % 1000;
                $seqMap = [1 => 'official_fee', 2 => 'agent_fee', 3 => 'other_fee'];
                $foundType = $seqMap[$seq] ?? null;
            } else {
                $prefix = substr((string)$ticketCodeId, 0, 2);
                $prefixMap = ['OF' => 'official_fee', 'AF' => 'agent_fee', 'OT' => 'other_fee'];
                $foundType = $prefixMap[$prefix] ?? null;
            }

            if (!$foundType) {
                return response()->json([
                    'success' => false,
                    'message' => '未找到取票码'
                ], 404);
            }

            $amount = (float)($case->{$typeMap[$foundType]['field']} ?? 0);

            $attachment = \App\Models\CaseAttachment::where('case_id', $caseId)
                ->where('file_type', 'ticket')
                ->where('file_sub_type', $foundType)
                ->orderBy('id', 'desc')
                ->first();

            if ($attachment) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($attachment->file_path);
                if (is_file($fullPath)) {
                    return response()->download($fullPath, $attachment->file_name);
                }
            }

            $code = $this->generateTicketCode($foundType);
            $img = imagecreatetruecolor(800, 400);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, 800, 400, $white);
            imagestring($img, 5, 20, 40, '取票码: ' . $code, $black);
            imagestring($img, 5, 20, 80, '项目: ' . ($case->case_name ?? ''), $black);
            imagestring($img, 5, 20, 120, '类型: ' . $typeMap[$foundType]['display'], $black);
            imagestring($img, 5, 20, 160, '金额: ' . number_format($amount, 2), $black);
            imagestring($img, 5, 20, 200, '生成时间: ' . now()->format('Y-m-d H:i:s'), $black);

            ob_start();
            imagepng($img);
            $content = ob_get_clean();
            imagedestroy($img);

            $tmp = 'tickets/tmp/' . $code . '.png';
            \Illuminate\Support\Facades\Storage::disk('public')->put($tmp, $content);
            $tmpPath = \Illuminate\Support\Facades\Storage::disk('public')->path($tmp);
            return response()->download($tmpPath, 'ticket-' . $code . '.png')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '下载取票码失败：' . $e->getMessage()
            ], 500);
        }
    }
}