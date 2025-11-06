<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    /**
    获取代理机构列表
    请求参数：
    agencyName（机构名称 / 编码）：可选，字符串，用于模糊搜索机构中文名、英文名或编码
    country（国家）：可选，用于筛选特定国家的机构
    isValid（有效性）：可选，整数，0 = 无效，1 = 有效，用于筛选机构有效性状态
    page（页码）：可选，整数，默认 1，分页查询的页码
    pageSize（每页条数）：可选，整数，默认 10，分页查询的每页记录数
    返回参数：
    code：整数，200 表示成功，500 表示失败
    message：字符串，操作结果描述
    data：对象，包含列表数据及分页信息
    list：数组，代理机构列表，每个元素包含机构的详细字段（如 id、agency_name_cn、agency_name_en、agency_code、country、is_valid、sort 等）
    total：整数，符合条件的总记录数
    page：整数，当前页码
    pageSize：整数，当前每页条数
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function index(Request $request)
    {
        try {
            $query = Agency::query();

            // 搜索条件
            if ($request->has('agencyName') && !empty(trim($request->agencyName))) {
                $keyword = trim($request->agencyName);
                $query->where(function ($q) use ($keyword) {
                    $q->where('agency_name_cn', 'like', "%{$keyword}%")
                      ->orWhere('agency_name_en', 'like', "%{$keyword}%")
                      ->orWhere('agency_code', 'like', "%{$keyword}%");
                });
            }

            // 国家筛选
            if ($request->has('country') && !empty($request->country)) {
                $query->where('country', $request->country);
            }

            // 有效性筛选
            if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
                $query->where('is_valid', $request->isValid);
            }

            // 分页参数
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 10);

            $total = $query->count();
            $data = $query->orderBy('sort', 'asc')
                         ->orderBy('id', 'asc')
                         ->offset(($page - 1) * $pageSize)
                         ->limit($pageSize)
                         ->get();

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
                'message' => '获取代理机构列表失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    创建代理机构
    请求参数：
    sort（排序值）：必填，整数，最小值 1，用于列表排序
    agencyNameCn（代理机构中文名称）：必填，字符串，最大 200 字符
    agencyNameEn（代理机构英文名称）：必填，字符串，最大 200 字符
    country（所属国家）：必填，字符串，最大 100 字符
    agencyCode（机构代码）：可选，字符串，最大 50 字符，需唯一
    socialCreditCode（社会信用代码）：可选，字符串，最大 100 字符
    createTime（创建时间）：可选，日期格式
    agentType（代理类型）：可选，字符串，最大 100 字符
    isValid（是否有效）：可选，布尔值，默认 true
    isSupplier（是否为供应商）：可选，布尔值，默认 false
    account（账号）：可选，未明确限制
    password（密码）：可选，未明确限制
    province（省份）：可选，未明确限制
    city（城市）：可选，未明确限制
    provinceEn（省份英文）：可选，未明确限制
    cityEn（城市英文）：可选，未明确限制
    addressCn（中文地址）：可选，未明确限制
    addressEn（英文地址）：可选，未明确限制
    postcode（邮政编码）：可选，未明确限制
    manager（负责人）：可选，未明确限制
    contact（联系方式）：可选，未明确限制
    modifier（修改人）：可选，未明确限制
    requirements（要求）：可选，未明确限制
    remark（备注）：可选，未明确限制
    返回参数：
    code：整数，200 表示成功，400 表示参数错误，500 表示失败
    message：字符串，操作结果描述
    data：对象，创建成功时返回代理机构详情
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1', // 排序值：必填、整数、最小值1
            'agencyNameCn' => 'required|string|max:200', // 中文名称：必填、字符串、最大长度200
            'agencyNameEn' => 'required|string|max:200', // 英文名称：必填、字符串、最大长度200
            'country' => 'required|string|max:100', // 所属国家：必填、字符串、最大长度100
            'agencyCode' => 'nullable|string|max:50|unique:agencies,agency_code', // 机构代码：可选、字符串、最大长度50、在agencies表的agency_code字段中唯一
            'socialCreditCode' => 'nullable|string|max:100', // 社会信用代码：可选、字符串、最大长度100
            'createTime' => 'nullable|date', // 创建时间：可选、日期格式
            'agentType' => 'nullable|string|max:100', // 代理类型：可选、字符串、最大长度100
            'isValid' => 'nullable|boolean', // 是否有效：可选、布尔值
            'isSupplier' => 'nullable|boolean' // 是否供应商：可选、布尔值
        ], [
            // 自定义验证错误提示信息
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'agencyNameCn.required' => '代理机构中文名称不能为空',
            'agencyNameEn.required' => '代理机构英文名称不能为空',
            'country.required' => '所属国家不能为空',
            'agencyCode.unique' => '机构代码已存在'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $now = now();

            $agency = Agency::create([
                'sort' => $request->sort,
                'agency_name_cn' => $request->agencyNameCn,
                'agency_name_en' => $request->agencyNameEn,
                'country' => $request->country,
                'social_credit_code' => $request->socialCreditCode,
                'agency_code' => $request->agencyCode,
                'create_time' => $request->createTime,
                'account' => $request->account,
                'password' => $request->password,
                'province' => $request->province,
                'city' => $request->city,
                'province_en' => $request->provinceEn,
                'city_en' => $request->cityEn,
                'address_cn' => $request->addressCn,
                'address_en' => $request->addressEn,
                'postcode' => $request->postcode,
                'manager' => $request->manager,
                'contact' => $request->contact,
                'modifier' => $request->modifier,
                'agent_type' => $request->agentType,
                'is_valid' => $request->get('isValid', true),
                'is_supplier' => $request->get('isSupplier', false),
                'requirements' => $request->requirements,
                'remark' => $request->remark,
                'creator' => $user ? $user->name : '',
                'creation_time' => $now,
                'created_by' => $user ? $user->id : null,
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构创建成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构创建失败: ' . $e->getMessage()
            ]);
        }
    }
    /**
    获取代理机构详情
    请求参数：
    id（代理机构 ID）：必填，整数，代理机构的唯一标识
    返回参数：
    code：整数，200 表示成功，404 表示机构不存在，500 表示失败
    message：字符串，操作结果描述
    data：对象，代理机构详情，包含 id、sort、agency_name_cn、agency_name_en、country、agency_code、social_credit_code 等所有机构相关字段
    @param int $id 代理机构 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function show($id)
    {
        try {
            $agency = Agency::find($id);

            if (!$agency) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理机构不存在'
                ]);
            }

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取代理机构详情失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    更新代理机构
    请求参数：
    id（代理机构 ID）：必填，整数，代理机构的唯一标识（通过 URL 路径传递）
    sort（排序值）：必填，整数，最小值 1，用于列表排序
    agencyNameCn（代理机构中文名称）：必填，字符串，最大 200 字符
    agencyNameEn（代理机构英文名称）：必填，字符串，最大 200 字符
    country（所属国家）：必填，字符串，最大 100 字符
    agencyCode（机构代码）：可选，字符串，最大 50 字符，需唯一（排除当前机构 ID）
    socialCreditCode（社会信用代码）：可选，字符串，最大 100 字符
    createTime（创建时间）：可选，日期格式
    agentType（代理类型）：可选，字符串，最大 100 字符
    isValid（是否有效）：可选，布尔值，默认 true
    isSupplier（是否为供应商）：可选，布尔值，默认 false
    account（账号）：可选，未明确限制
    password（密码）：可选，未明确限制
    province（省份）：可选，未明确限制
    city（城市）：可选，未明确限制
    provinceEn（省份英文）：可选，未明确限制
    cityEn（城市英文）：可选，未明确限制
    addressCn（中文地址）：可选，未明确限制
    addressEn（英文地址）：可选，未明确限制
    postcode（邮政编码）：可选，未明确限制
    manager（负责人）：可选，未明确限制
    contact（联系方式）：可选，未明确限制
    modifier（修改人）：可选，未明确限制（实际由当前登录用户填充）
    requirements（要求）：可选，未明确限制
    remark（备注）：可选，未明确限制
    返回参数：
    code：整数，200 表示成功，400 表示参数错误，404 表示机构不存在，500 表示失败
    message：字符串，操作结果描述
    data：对象，更新成功时返回更新后的代理机构详情
    @param Request $request 请求对象
    @param int $id 代理机构 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function update(Request $request, $id)
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return response()->json([
                'code' => 404,
                'message' => '代理机构不存在'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'sort' => 'required|integer|min:1',
            'agencyNameCn' => 'required|string|max:200',
            'agencyNameEn' => 'required|string|max:200',
            'country' => 'required|string|max:100',
            'agencyCode' => 'nullable|string|max:50|unique:agencies,agency_code,' . $id,
            'socialCreditCode' => 'nullable|string|max:100',
            'createTime' => 'nullable|date',
            'agentType' => 'nullable|string|max:100',
            'isValid' => 'nullable|boolean',
            'isSupplier' => 'nullable|boolean'
        ], [
            'sort.required' => '排序值不能为空',
            'sort.min' => '排序值必须大于0',
            'agencyNameCn.required' => '代理机构中文名称不能为空',
            'agencyNameEn.required' => '代理机构英文名称不能为空',
            'country.required' => '所属国家不能为空',
            'agencyCode.unique' => '机构代码已存在'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误: ' . $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $now = now();

            $agency->update([
                'sort' => $request->sort,
                'agency_name_cn' => $request->agencyNameCn,
                'agency_name_en' => $request->agencyNameEn,
                'country' => $request->country,
                'social_credit_code' => $request->socialCreditCode,
                'agency_code' => $request->agencyCode,
                'create_time' => $request->createTime,
                'account' => $request->account,
                'password' => $request->password,
                'province' => $request->province,
                'city' => $request->city,
                'province_en' => $request->provinceEn,
                'city_en' => $request->cityEn,
                'address_cn' => $request->addressCn,
                'address_en' => $request->addressEn,
                'postcode' => $request->postcode,
                'manager' => $request->manager,
                'contact' => $request->contact,
                'modifier' => $user ? $user->name : '',
                'agent_type' => $request->agentType,
                'is_valid' => $request->get('isValid', true),
                'is_supplier' => $request->get('isSupplier', false),
                'requirements' => $request->requirements,
                'remark' => $request->remark,
                'update_time' => $now,
                'updated_by' => $user ? $user->id : null,
            ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构更新成功',
                'data' => $agency
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构更新失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    删除代理机构
    请求参数：
    id（代理机构 ID）：必填，整数，代理机构的唯一标识（通过 URL 路径传递）
    返回参数：
    code：整数，200 表示成功，404 表示机构不存在，500 表示失败
    message：字符串，操作结果描述
    @param int $id 代理机构 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function destroy($id)
    {
        try {
            $agency = Agency::find($id);

            if (!$agency) {
                return response()->json([
                    'code' => 404,
                    'message' => '代理机构不存在'
                ]);
            }

            DB::beginTransaction();

            $agencyName = $agency->agency_name_cn;
            $agency->delete();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '代理机构删除成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '代理机构删除失败: ' . $e->getMessage()
            ]);
        }
    }

    /**
    获取国家选项列表
    请求参数：无
    返回参数：
    code：整数，200 表示成功，500 表示失败
    message：字符串，操作结果描述
    data：数组，国家选项列表，每个元素包含：
    value：字符串，国家 / 地区值（用于筛选等业务逻辑）
    label：字符串，国家 / 地区显示名称（用于前端展示）
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function getCountries()
    {
        try {
            $countries = [
                ['value' => '中国', 'label' => '中国'],
                ['value' => '美国', 'label' => '美国'],
                ['value' => '日本', 'label' => '日本'],
                ['value' => '韩国', 'label' => '韩国'],
                ['value' => '欧洲', 'label' => '欧洲'],
                ['value' => '英国', 'label' => '英国'],
                ['value' => '德国', 'label' => '德国'],
                ['value' => '法国', 'label' => '法国'],
                ['value' => '加拿大', 'label' => '加拿大'],
                ['value' => '澳大利亚', 'label' => '澳大利亚'],
                ['value' => '新加坡', 'label' => '新加坡'],
                ['value' => '香港', 'label' => '香港'],
                ['value' => '台湾', 'label' => '台湾'],
                ['value' => '澳门', 'label' => '澳门'],
            ];

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取国家列表失败: ' . $e->getMessage()
            ]);
        }
    }
}
