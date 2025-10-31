<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    /**
    获取代理师列表
    请求参数：
    name（姓名）：可选，字符串，用于模糊搜索代理师中文姓名或英文姓名
    licenseNumber（执业证号）：可选，字符串，用于模糊搜索代理师执业证号
    agency（所属机构）：可选，字符串，用于模糊搜索代理师所属机构
    page（页码）：可选，整数，默认 1，分页查询的页码
    pageSize（每页条数）：可选，整数，默认 10，分页查询的每页记录数
    返回参数：
    code：整数，200 表示成功，500 表示失败
    message：字符串，操作结果描述
    data：对象，包含列表数据及分页信息
    list：数组，代理师列表，每个元素包含以下字段：
    id：整数，代理师 ID
    sort：整数，排序值
    nameCn：字符串，中文姓名
    nameEn：字符串，英文姓名
    lastNameCn：字符串，中文姓氏
    firstNameCn：字符串，中文名字
    lastNameEn：字符串，英文姓氏
    firstNameEn：字符串，英文名字
    licenseNumber：字符串，执业证号
    qualificationNumber：字符串，资格证号
    licenseDate：日期，领证日期
    phone：字符串，联系电话
    email：字符串，电子邮箱
    agency：字符串，所属机构
    gender：字符串，性别
    licenseExpiry：日期，执业证有效期
    specialty：字符串，专业领域
    isDefaultAgent：布尔值，是否为默认代理师
    isValid：布尔值，是否有效
    creditRating：字符串，信用评级
    creator：字符串，创建人
    creationTime：时间戳，创建时间
    modifier：字符串，修改人
    updateTime：时间戳，更新时间
    total：整数，符合条件的总记录数
    page：整数，当前页码
    pageSize：整数，当前每页条数
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function index(Request $request)
    {
        try {
            $query = Agent::query();

            // 搜索条件 - 支持前端的搜索参数
            if ($request->has('name') && !empty(trim($request->name))) {
                $keyword = trim($request->name);
                $query->where(function ($q) use ($keyword) {
                    $q->where('name_cn', 'like', "%{$keyword}%")
                      ->orWhere('name_en', 'like', "%{$keyword}%");
                });
            }

            // 执业证号搜索
            if ($request->has('licenseNumber') && !empty(trim($request->licenseNumber))) {
                $query->where('license_number', 'like', "%" . trim($request->licenseNumber) . "%");
            }

            // 所属机构搜索
            if ($request->has('agency') && !empty(trim($request->agency))) {
                $query->where('agency', 'like', "%" . trim($request->agency) . "%");
            }

            // 分页参数
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 10);

            $total = $query->count();
            $data = $query->orderBy('sort', 'asc')
                         ->orderBy('id', 'asc')
                         ->offset(($page - 1) * $pageSize)
                         ->limit($pageSize)
                         ->get()
                         ->map(function ($agent) {
                             return [
                                 'id' => $agent->id,
                                 'sort' => $agent->sort,
                                 'nameCn' => $agent->name_cn,
                                 'nameEn' => $agent->name_en,
                                 'lastNameCn' => $agent->last_name_cn,
                                 'firstNameCn' => $agent->first_name_cn,
                                 'lastNameEn' => $agent->last_name_en,
                                 'firstNameEn' => $agent->first_name_en,
                                 'licenseNumber' => $agent->license_number,
                                 'qualificationNumber' => $agent->qualification_number,
                                 'licenseDate' => $agent->license_date,
                                 'phone' => $agent->phone,
                                 'email' => $agent->email,
                                 'agency' => $agent->agency,
                                 'gender' => $agent->gender,
                                 'licenseExpiry' => $agent->license_expiry,
                                 'specialty' => $agent->specialty,
                                 'isDefaultAgent' => $agent->is_default_agent,
                                 'isValid' => $agent->is_valid,
                                 'creditRating' => $agent->credit_rating,
                                 'creator' => $agent->creator,
                                 'creationTime' => $agent->creation_time,
                                 'modifier' => $agent->modifier,
                                 'updateTime' => $agent->update_time
                             ];
                         });

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => [
                    'list' => $data,
                    'total' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理师列表失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    创建代理师
    请求参数：
    sort（排序值）：必填，整数，最小值 1，用于列表排序
    nameCn（中文姓名）：必填，字符串，最大 100 字符
    nameEn（英文姓名）：可选，字符串，最大 100 字符
    lastNameCn（中文姓氏）：可选，字符串，最大 50 字符
    firstNameCn（中文名字）：可选，字符串，最大 50 字符
    lastNameEn（英文姓氏）：可选，字符串，最大 50 字符
    firstNameEn（英文名字）：可选，字符串，最大 50 字符
    licenseNumber（执业证号）：必填，字符串，最大 100 字符，需唯一
    qualificationNumber（资格证号）：必填，字符串，最大 100 字符，需唯一
    licenseDate（领证日期）：可选，日期格式
    agency（所属机构）：必填，字符串，最大 200 字符
    phone（联系电话）：可选，字符串，最大 50 字符
    email（电子邮箱）：可选，邮箱格式，最大 255 字符
    licenseExpiry（执业证有效期）：可选，日期格式
    specialty（专业领域）：可选，字符串，最大 255 字符
    isDefaultAgent（是否为默认代理师）：可选，布尔值，默认 false
    isValid（是否有效）：可选，布尔值，默认 true
    creditRating（信用评级）：可选，字符串，最大 50 字符
    gender（性别）：可选，字符串，默认 "男"
    返回参数：
    code：整数，200 表示成功，400 表示参数错误，500 表示失败
    message：字符串，操作结果描述
    data：对象，创建成功时返回代理师详情（含自动填充的 status、creator、creation_time、created_by 等字段）
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'nameCn' => 'required|string|max:100',
            'nameEn' => 'nullable|string|max:100',
            'lastNameCn' => 'nullable|string|max:50',
            'firstNameCn' => 'nullable|string|max:50',
            'lastNameEn' => 'nullable|string|max:50',
            'firstNameEn' => 'nullable|string|max:50',
            'licenseNumber' => 'required|string|max:100|unique:agents,license_number',
            'qualificationNumber' => 'required|string|max:100|unique:agents,qualification_number',
            'licenseDate' => 'nullable|date',
            'agency' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'licenseExpiry' => 'nullable|date',
            'specialty' => 'nullable|string|max:255',
            'isDefaultAgent' => 'nullable|boolean',
            'isValid' => 'nullable|boolean',
            'creditRating' => 'nullable|string|max:50'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'nameCn.required' => '代理师中文姓名不能为空',
            'licenseNumber.required' => '执业证号不能为空',
            'licenseNumber.unique' => '执业证号已存在',
            'qualificationNumber.required' => '资格证号不能为空',
            'qualificationNumber.unique' => '资格证号已存在',
            'agency.required' => '请输入所属机构',
            'email.email' => '邮箱格式不正确',
            'gender.in' => '请选择正确的性别'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $agent = Agent::create([
                'sort' => $request->sort,
                'name_cn' => $request->nameCn,
                'name_en' => $request->nameEn,
                'last_name_cn' => $request->lastNameCn,
                'first_name_cn' => $request->firstNameCn,
                'last_name_en' => $request->lastNameEn,
                'first_name_en' => $request->firstNameEn,
                'license_number' => $request->licenseNumber,
                'qualification_number' => $request->qualificationNumber,
                'license_date' => $request->licenseDate,
                'agency' => $request->agency,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->get('gender', '男'),
                'license_expiry' => $request->licenseExpiry,
                'specialty' => $request->specialty,
                'is_default_agent' => $request->get('isDefaultAgent', false),
                'is_valid' => $request->get('isValid', true),
                'credit_rating' => $request->creditRating,
                'status' => 1,
                'creator' => auth()->user()->name ?? 'system',
                'creation_time' => now(),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理师创建成功',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理师创建失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    获取代理师详情
    功能说明：
    根据代理师 ID 查询完整详情，包含基本信息、资质信息、联系信息等核心字段
    校验代理师存在性，不存在返回 404 提示，异常时返回具体错误信息
    请求参数：
    id（代理师 ID）：必填，整数，代理师的唯一标识 ID（通过 URL 路径传入）
    核心逻辑：
    数据查询：通过 Agent 模型查询指定 ID 的代理师记录
    存在性校验：查询结果为空时，返回 404 状态码和 “代理师不存在” 提示
    数据格式化：将模型字段映射为前端友好的字段名（如下划线转驼峰），整合所有核心信息
    响应返回：返回标准化 JSON 响应，包含状态码、提示信息和格式化后的详情数据
    返回参数：
    code：整数，状态码（200 = 成功，404 = 代理师不存在，500 = 服务器错误）
    message：字符串，返回 “获取成功”“代理师不存在” 或 “获取代理师详情失败 + 具体错误信息”
    data：对象，代理师详情，包含以下字段：
    基础标识：id（ID）、sort（排序号）
    姓名信息：nameCn（中文全名）、nameEn（英文全名）、lastNameCn（中文姓）、firstNameCn（中文名）、lastNameEn（英文姓）、firstNameEn（英文名）
    资质信息：licenseNumber（执业证号）、qualificationNumber（资格证号）、licenseDate（领证日期）、licenseExpiry（执业证有效期）、specialty（专业领域）、creditRating（信用等级）
    联系信息：phone（电话）、email（邮箱）、agency（所属机构）
    基础属性：gender（性别）、isDefaultAgent（是否默认代理师）、isValid（是否有效）
    操作信息：creator（创建人）、creationTime（创建时间）、modifier（修改人）、updateTime（更新时间）
    依赖说明：
    依赖 Agent 模型，需确保模型字段（name_cn、license_number 等）与数据库表一致
    字段映射规则：数据库下划线命名（如 name_cn）转为前端驼峰命名（nameCn），适配前端数据处理习惯
    @param int $id 代理师 ID
    @return \Illuminate\Http\JsonResponse 代理师详情响应
     */
    public function show($id)
    {
        try {
            $agent = Agent::find($id);

            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $data = [
                'id' => $agent->id,
                'sort' => $agent->sort,
                'nameCn' => $agent->name_cn,
                'nameEn' => $agent->name_en,
                'lastNameCn' => $agent->last_name_cn,
                'firstNameCn' => $agent->first_name_cn,
                'lastNameEn' => $agent->last_name_en,
                'firstNameEn' => $agent->first_name_en,
                'licenseNumber' => $agent->license_number,
                'qualificationNumber' => $agent->qualification_number,
                'licenseDate' => $agent->license_date,
                'phone' => $agent->phone,
                'email' => $agent->email,
                'agency' => $agent->agency,
                'gender' => $agent->gender,
                'licenseExpiry' => $agent->license_expiry,
                'specialty' => $agent->specialty,
                'isDefaultAgent' => $agent->is_default_agent,
                'isValid' => $agent->is_valid,
                'creditRating' => $agent->credit_rating,
                'creator' => $agent->creator,
                'creationTime' => $agent->creation_time,
                'modifier' => $agent->modifier,
                'updateTime' => $agent->update_time
            ];

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理师详情失败: ' . $e->getMessage()
            ]);
        }
    }
    /**
    更新代理师
    请求参数：
    id（代理师 ID）：必填，整数，代理师的唯一标识（通过 URL 路径传递）
    sort（排序值）：必填，整数，最小值 1，用于列表排序
    nameCn（中文姓名）：必填，字符串，最大 100 字符
    nameEn（英文姓名）：可选，字符串，最大 100 字符
    lastNameCn（中文姓氏）：可选，字符串，最大 50 字符
    firstNameCn（中文名字）：可选，字符串，最大 50 字符
    lastNameEn（英文姓氏）：可选，字符串，最大 50 字符
    firstNameEn（英文名字）：可选，字符串，最大 50 字符
    licenseNumber（执业证号）：必填，字符串，最大 100 字符，需唯一（排除当前代理师 ID）
    qualificationNumber（资格证号）：必填，字符串，最大 100 字符，需唯一（排除当前代理师 ID）
    licenseDate（领证日期）：可选，日期格式
    agency（所属机构）：必填，字符串，最大 200 字符
    phone（联系电话）：可选，字符串，最大 50 字符
    email（电子邮箱）：可选，邮箱格式，最大 255 字符
    licenseExpiry（执业证有效期）：可选，日期格式
    specialty（专业领域）：可选，字符串，最大 255 字符
    isDefaultAgent（是否为默认代理师）：可选，布尔值，默认 false
    isValid（是否有效）：可选，布尔值，默认 true
    creditRating（信用评级）：可选，字符串，最大 50 字符
    gender（性别）：可选，字符串，默认 "男"
    返回参数：
    code：整数，200 表示成功，400 表示参数错误，404 表示代理师不存在，500 表示失败
    message：字符串，操作结果描述
    data：对象，更新成功时返回更新后的代理师详情（含自动填充的 modifier、update_time、updated_by 等字段）
    @param Request $request 请求对象
    @param int $id 代理师 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'nameCn' => 'required|string|max:100',
            'nameEn' => 'nullable|string|max:100',
            'lastNameCn' => 'nullable|string|max:50',
            'firstNameCn' => 'nullable|string|max:50',
            'lastNameEn' => 'nullable|string|max:50',
            'firstNameEn' => 'nullable|string|max:50',
            'licenseNumber' => 'required|string|max:100|unique:agents,license_number,' . $id,
            'qualificationNumber' => 'required|string|max:100|unique:agents,qualification_number,' . $id,
            'licenseDate' => 'nullable|date',
            'agency' => 'required|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'licenseExpiry' => 'nullable|date',
            'specialty' => 'nullable|string|max:255',
            'isDefaultAgent' => 'nullable|boolean',
            'isValid' => 'nullable|boolean',
            'creditRating' => 'nullable|string|max:50'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'nameCn.required' => '代理师中文姓名不能为空',
            'licenseNumber.required' => '执业证号不能为空',
            'licenseNumber.unique' => '执业证号已存在',
            'qualificationNumber.required' => '资格证号不能为空',
            'qualificationNumber.unique' => '资格证号已存在',
            'agency.required' => '请输入所属机构',
            'email.email' => '邮箱格式不正确',
            'gender.in' => '请选择正确的性别'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $agent = Agent::find($id);

            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $agent->update([
                'sort' => $request->sort,
                'name_cn' => $request->nameCn,
                'name_en' => $request->nameEn,
                'last_name_cn' => $request->lastNameCn,
                'first_name_cn' => $request->firstNameCn,
                'last_name_en' => $request->lastNameEn,
                'first_name_en' => $request->firstNameEn,
                'license_number' => $request->licenseNumber,
                'qualification_number' => $request->qualificationNumber,
                'license_date' => $request->licenseDate,
                'agency' => $request->agency,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => $request->get('gender', '男'),
                'license_expiry' => $request->licenseExpiry,
                'specialty' => $request->specialty,
                'is_default_agent' => $request->get('isDefaultAgent', false),
                'is_valid' => $request->get('isValid', true),
                'credit_rating' => $request->creditRating,
                'modifier' => auth()->user()->name ?? 'system',
                'update_time' => now(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '更新成功',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '更新代理师失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    删除代理师
    请求参数：
    id（代理师 ID）：必填，整数，代理师的唯一标识（通过 URL 路径传递）
    返回参数：
    code：整数，200 表示成功，404 表示代理师不存在，500 表示失败
    message：字符串，操作结果描述
    @param int $id 代理师 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function destroy($id)
    {
        try {
            $agent = Agent::find($id);

            if (!$agent) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理师不存在'
                ]);
            }

            $agent->delete();

            return response()->json([
                'code' => 200,
                'message' => '删除成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '删除代理师失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    获取代理机构选项列表
    请求参数：无
    返回参数：
    code：整数，200 表示成功，500 表示失败
    message：字符串，操作结果描述
    data：数组，有效代理机构选项列表，每个元素包含：
    value：整数，代理机构 ID（用于业务关联逻辑）
    label：字符串，代理机构中文名称（用于前端展示）
    说明：仅返回状态为 “有效”（is_valid=true）的代理机构，按排序值和 ID 升序排列
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function getAgencies()
    {
        try {
            $agencies = Agency::where('is_valid', true)
                            ->select('id as value', 'agency_name_cn as label')
                            ->orderBy('sort', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $agencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理机构列表失败: ' . $e->getMessage()
            ]);
        }
    }
}
