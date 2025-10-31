<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessOpportunity;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessOpportunityController extends Controller
{
    /**
     * 获取商机列表
     *
     * 请求参数：
     * - customer_id（客户ID）：可选，整数，用于筛选特定客户的商机
     * - name（商机名称）：可选，字符串，模糊匹配商机名称
     * - customer_name（客户名称）：可选，字符串，模糊匹配关联客户的名称
     * - business_code（商机编码）：可选，字符串，模糊匹配商机编码
     * - status（状态）：可选，整数，用于筛选特定状态的商机
     * - business_type（商机类型）：可选，整数，用于筛选特定类型的商机
     * - case_type（案件类型）：可选，整数，用于筛选特定案件类型的商机
     * - business_person_id（业务员ID）：可选，整数，用于筛选特定业务员负责的商机
     * - contact_person（联系人）：可选，字符串，模糊匹配联系人姓名
     * - contact_phone（联系电话）：可选，字符串，模糊匹配联系电话
     * - is_contract（是否签约）：可选，整数，0=未签约，1=已签约，用于筛选签约状态的商机
     * - estimated_sign_start_date（预计签约开始时间）：可选，日期，需与estimated_sign_end_date同时使用，筛选预计签约时间在范围内的商机
     * - estimated_sign_end_date（预计签约结束时间）：可选，日期，需与estimated_sign_start_date同时使用，筛选预计签约时间在范围内的商机
     * - next_time_start（下次跟进开始时间）：可选，日期时间，需与next_time_end同时使用，筛选下次跟进时间在范围内的商机
     * - next_time_end（下次跟进结束时间）：可选，日期时间，需与next_time_start同时使用，筛选下次跟进时间在范围内的商机
     * - page_size（每页条数）：可选，整数，默认10，指定分页每页显示的记录数
     * - page（页码）：可选，整数，默认1，指定分页页码
     *
     * 返回参数：
     * - list（商机列表）：数组，包含商机详情对象
     *   - id（主键ID）：整数，商机自增主键
     *   - customer_id（客户ID）：整数，关联客户的ID
     *   - customer_name（客户名称）：字符串，关联客户的名称
     *   - customerName（客户名称）：字符串，同customer_name（驼峰命名）
     *   - business_code（商机编码）：字符串，商机的唯一编码
     *   - businessCode（商机编码）：字符串，同business_code（驼峰命名）
     *   - name（商机名称）：字符串，商机的名称
     *   - businessName（商机名称）：字符串，同name（驼峰命名）
     *   - contact_person（联系人）：字符串，商机的联系人姓名
     *   - contactPerson（联系人）：字符串，同contact_person（驼峰命名）
     *   - contact_phone（联系电话）：字符串，联系人的电话
     *   - contactPhone（联系电话）：字符串，同contact_phone（驼峰命名）
     *   - business_person（业务员姓名）：字符串，负责该商机的业务员姓名
     *   - businessPerson（业务员姓名）：字符串，同business_person（驼峰命名）
     *   - business_person_id（业务员ID）：整数，负责该商机的业务员ID
     *   - businessPersonId（业务员ID）：整数，同business_person_id（驼峰命名）
     *   - business_staff（业务人员）：字符串，同business_person
     *   - businessStaff（业务人员）：字符串，同business_staff（驼峰命名）
     *   - next_time（下次跟进时间）：字符串，格式Y-m-d H:i:s，下次跟进的时间
     *   - nextTime（下次跟进时间）：字符串，同next_time（驼峰命名）
     *   - second_time（二次跟进时间）：字符串，格式Y-m-d，二次跟进的日期
     *   - secondTime（二次跟进时间）：字符串，同second_time（驼峰命名）
     *   - content（商机内容）：字符串，商机的详细内容描述
     *   - case_type（案件类型）：整数，商机关联的案件类型标识
     *   - caseType（案件类型）：整数，同case_type（驼峰命名）
     *   - business_type（商机类型）：整数，商机的类型标识
     *   - businessType（商机类型）：整数，同business_type（驼峰命名）
     *   - estimated_amount（预计金额）：数值，该商机的预计金额
     *   - estimatedAmount（预计金额）：数值，同estimated_amount（驼峰命名）
     *   - estimated_sign_time（预计签约时间）：字符串，格式Y-m-d，预计签约的日期
     *   - estimatedSignTime（预计签约时间）：字符串，同estimated_sign_time（驼峰命名）
     *   - status（状态）：整数，商机的当前状态标识
     *   - is_contract（是否签约）：整数，0=未签约，1=已签约
     *   - isContract（是否签约）：整数，同is_contract（驼峰命名）
     *   - background（背景信息）：字符串，商机的背景描述
     *   - remark（备注）：字符串，商机的备注信息
     *   - create_user（创建人姓名）：字符串，创建该商机的用户姓名
     *   - createUser（创建人姓名）：字符串，同create_user（驼峰命名）
     *   - created_at（创建时间）：字符串，格式Y-m-d H:i:s，商机的创建时间
     *   - create_time（创建时间）：字符串，同created_at
     *   - createTime（创建时间）：字符串，同created_at（驼峰命名）
     *   - update_user（更新人姓名）：字符串，最后更新该商机的用户姓名
     *   - updateUser（更新人姓名）：字符串，同update_user（驼峰命名）
     *   - updated_at（更新时间）：字符串，格式Y-m-d H:i:s，商机的最后更新时间
     *   - update_time（更新时间）：字符串，同updated_at
     *   - updateTime（更新时间）：字符串，同updated_at（驼峰命名）
     * - total（总条数）：整数，符合条件的商机总记录数
     * - per_page（每页条数）：整数，当前分页每页显示的记录数
     * - current_page（当前页码）：整数，当前分页的页码
     * - last_page（最后页码）：整数，分页的最后一页页码
     *
     * @param Request $request 请求对象，包含筛选参数和分页参数
     * @return \Illuminate\Http\JsonResponse JSON响应，包含商机列表及分页信息
     */
    public function index(Request $request)
    {
        try {
            $query = BusinessOpportunity::with([
                'customer',
                'businessPerson',
                'creator',
                'updater'
            ]);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('business_code')) {
                $query->where('business_code', 'like', '%' . $request->business_code . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('business_type')) {
                $query->where('business_type', $request->business_type);
            }

            if ($request->filled('case_type')) {
                $query->where('case_type', $request->case_type);
            }

            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id);
            }

            if ($request->filled('contact_person')) {
                $query->where('contact_person', 'like', '%' . $request->contact_person . '%');
            }

            if ($request->filled('contact_phone')) {
                $query->where('contact_phone', 'like', '%' . $request->contact_phone . '%');
            }

            if ($request->filled('is_contract')) {
                $query->where('is_contract', $request->is_contract);
            }

            // 预计签约时间筛选
            if ($request->filled('estimated_sign_start_date') && $request->filled('estimated_sign_end_date')) {
                $query->whereBetween('estimated_sign_time', [$request->estimated_sign_start_date, $request->estimated_sign_end_date]);
            }

            // 跟进时间筛选
            if ($request->filled('next_time_start') && $request->filled('next_time_end')) {
                $query->whereBetween('next_time', [$request->next_time_start, $request->next_time_end]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $opportunities = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据
            $opportunities->getCollection()->transform(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'customer_id' => $opportunity->customer_id,
                    'customer_name' => $opportunity->customer->customer_name ?? '',
                    'customerName' => $opportunity->customer->customer_name ?? '',
                    'business_code' => $opportunity->business_code,
                    'businessCode' => $opportunity->business_code,
                    'name' => $opportunity->name,
                    'businessName' => $opportunity->name,
                    'contact_person' => $opportunity->contact_person,
                    'contactPerson' => $opportunity->contact_person,
                    'contact_phone' => $opportunity->contact_phone,
                    'contactPhone' => $opportunity->contact_phone,
                    'business_person' => $opportunity->businessPerson->name ?? '',
                    'businessPerson' => $opportunity->businessPerson->name ?? '',
                    'business_person_id' => $opportunity->business_person_id,
                    'businessPersonId' => $opportunity->business_person_id,
                    'business_staff' => $opportunity->businessPerson->name ?? '',
                    'businessStaff' => $opportunity->businessPerson->name ?? '',
                    'next_time' => $opportunity->next_time ? $opportunity->next_time->format('Y-m-d H:i:s') : '',
                    'nextTime' => $opportunity->next_time ? $opportunity->next_time->format('Y-m-d H:i:s') : '',
                    'second_time' => $opportunity->second_time ? $opportunity->second_time->format('Y-m-d') : '',
                    'secondTime' => $opportunity->second_time ? $opportunity->second_time->format('Y-m-d') : '',
                    'content' => $opportunity->content,
                    'case_type' => $opportunity->case_type,
                    'caseType' => $opportunity->case_type,
                    'business_type' => $opportunity->business_type,
                    'businessType' => $opportunity->business_type,
                    'estimated_amount' => $opportunity->estimated_amount,
                    'estimatedAmount' => $opportunity->estimated_amount,
                    'estimated_sign_time' => $opportunity->estimated_sign_time ? $opportunity->estimated_sign_time->format('Y-m-d') : '',
                    'estimatedSignTime' => $opportunity->estimated_sign_time ? $opportunity->estimated_sign_time->format('Y-m-d') : '',
                    'status' => $opportunity->status,
                    'is_contract' => $opportunity->is_contract,
                    'isContract' => $opportunity->is_contract,
                    'background' => $opportunity->background,
                    'remark' => $opportunity->remark,
                    'create_user' => $opportunity->creator->name ?? '',
                    'createUser' => $opportunity->creator->name ?? '',
                    'created_at' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $opportunity->updater->name ?? '',
                    'updateUser' => $opportunity->updater->name ?? '',
                    'updated_at' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $opportunities->items(),
                    'total' => $opportunities->total(),
                    'per_page' => $opportunities->perPage(),
                    'current_page' => $opportunities->currentPage(),
                    'last_page' => $opportunities->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    创建商机
    请求参数：
    customer_id（客户 ID）：必填，整数，需存在于 customers 表中，关联的客户 ID
    name（商机名称）：必填，字符串，最大长度 200 字符，商机的名称
    business_type（商机类型）：必填，字符串，最大长度 50 字符，商机的类型标识
    case_type（案件类型）：可选，字符串，最大长度 50 字符，商机关联的案件类型标识
    business_person_id（业务员 ID）：必填，整数，需存在于 users 表中，负责该商机的业务员 ID
    contact_person（联系人）：可选，字符串，最大长度 100 字符，商机的联系人姓名
    contact_phone（联系电话）：可选，字符串，最大长度 50 字符，联系人的电话
    estimated_amount（预计金额）：可选，数值，最小值 0，该商机的预计金额
    estimated_sign_time（预计签约时间）：可选，日期，格式符合日期规范，预计签约的日期
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，创建结果的描述信息
    data（商机数据）：对象，创建成功时返回新创建的商机详情，包含以下字段：
    id（主键 ID）：整数，新创建商机的自增主键
    customer_id（客户 ID）：整数，关联的客户 ID
    name（商机名称）：字符串，商机的名称
    business_type（商机类型）：字符串，商机的类型标识
    case_type（案件类型）：字符串，商机关联的案件类型标识（可能为 null）
    business_person_id（业务员 ID）：整数，负责该商机的业务员 ID
    contact_person（联系人）：字符串，商机的联系人姓名（可能为 null）
    contact_phone（联系电话）：字符串，联系人的电话（可能为 null）
    estimated_amount（预计金额）：数值，该商机的预计金额（可能为 null）
    estimated_sign_time（预计签约时间）：日期，预计签约的日期（可能为 null）
    business_code（商机编码）：字符串，系统自动生成的商机唯一编码
    created_by（创建人 ID）：整数，创建该商机的用户 ID
    updated_by（更新人 ID）：整数，最后更新该商机的用户 ID（初始为创建人 ID）
    created_at（创建时间）：时间戳，商机的创建时间
    updated_at（更新时间）：时间戳，商机的最后更新时间（初始与创建时间一致）
    errors（错误信息）：对象，验证失败时返回的具体错误信息（仅验证失败时存在）
    @param Request $request 请求对象，包含创建商机所需的参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含创建结果信息
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'name' => 'required|string|max:200',
                'business_type' => 'required|string|max:50',
                'case_type' => 'nullable|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'estimated_amount' => 'nullable|numeric|min:0',
                'estimated_sign_time' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 生成商机编号
            $data['business_code'] = BusinessOpportunity::generateCode();

            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $opportunity = BusinessOpportunity::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取商机详情
    请求参数：
    id（商机 ID）：必填，整数，路径参数，商机的唯一标识 ID
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，获取结果的描述信息
    data（商机详情）：对象，获取成功时返回商机的详细信息，包含以下字段：
    基础信息：id（主键 ID）、customer_id（客户 ID）、name（商机名称）、business_code（商机编码）、business_type（商机类型）、case_type（案件类型）等商机表字段
    关联信息：
    customer（客户信息）：对象，包含关联客户的详细信息（如 customer_name 等）
    businessPerson（业务员信息）：对象，包含负责该商机的业务员详细信息（如 name 等）
    creator（创建人信息）：对象，包含创建该商机的用户详细信息（如 name 等）
    updater（更新人信息）：对象，包含最后更新该商机的用户详细信息（如 name 等）
    时间信息：created_at（创建时间）、updated_at（更新时间）等
    @param int $id 商机 ID，用于查询特定商机的详情
    @return \Illuminate\Http\JsonResponse JSON 响应，包含商机详情信息
     */
    public function show($id)
    {
        try {
            $opportunity = BusinessOpportunity::with(['customer', 'businessPerson', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
    更新商机
    请求参数：
    路径参数：id（商机 ID）：必填，整数，商机的唯一标识 ID，用于指定待更新的商机
    请求体参数：
    customer_id（客户 ID）：必填，整数，需存在于 customers 表中，关联的客户 ID
    name（商机名称）：必填，字符串，最大长度 200 字符，商机的名称
    business_type（商机类型）：必填，字符串，最大长度 50 字符，商机的类型标识
    case_type（案件类型）：可选，字符串，最大长度 50 字符，商机关联的案件类型标识
    business_person_id（业务员 ID）：必填，整数，需存在于 users 表中，负责该商机的业务员 ID
    contact_person（联系人）：可选，字符串，最大长度 100 字符，商机的联系人姓名
    contact_phone（联系电话）：可选，字符串，最大长度 50 字符，联系人的电话
    estimated_amount（预计金额）：可选，数值，最小值 0，该商机的预计金额
    estimated_sign_time（预计签约时间）：可选，日期，格式符合日期规范，预计签约的日期
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，更新结果的描述信息
    data（商机数据）：对象，更新成功时返回更新后的商机详情，包含以下字段：
    id（主键 ID）：整数，待更新商机的自增主键
    customer_id（客户 ID）：整数，关联的客户 ID（更新后的值）
    name（商机名称）：字符串，商机的名称（更新后的值）
    business_type（商机类型）：字符串，商机的类型标识（更新后的值）
    case_type（案件类型）：字符串，商机关联的案件类型标识（更新后的值，可能为 null）
    business_person_id（业务员 ID）：整数，负责该商机的业务员 ID（更新后的值）
    contact_person（联系人）：字符串，商机的联系人姓名（更新后的值，可能为 null）
    contact_phone（联系电话）：字符串，联系人的电话（更新后的值，可能为 null）
    estimated_amount（预计金额）：数值，该商机的预计金额（更新后的值，可能为 null）
    estimated_sign_time（预计签约时间）：日期，预计签约的日期（更新后的值，可能为 null）
    business_code（商机编码）：字符串，系统生成的商机唯一编码（不可修改）
    created_by（创建人 ID）：整数，创建该商机的用户 ID（不可修改）
    updated_by（更新人 ID）：整数，本次更新该商机的用户 ID
    created_at（创建时间）：时间戳，商机的创建时间（不可修改）
    updated_at（更新时间）：时间戳，商机的本次更新时间
    errors（错误信息）：对象，验证失败时返回的具体错误信息（仅验证失败时存在）
    @param Request $request 请求对象，包含更新商机所需的参数
    @param int $id 商机 ID，用于指定待更新的商机
    @return \Illuminate\Http\JsonResponse JSON 响应，包含更新结果信息
     */
    public function update(Request $request, $id)
    {
        try {
            $opportunity = BusinessOpportunity::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'name' => 'required|string|max:200',
                'business_type' => 'required|string|max:50',
                'case_type' => 'nullable|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'estimated_amount' => 'nullable|numeric|min:0',
                'estimated_sign_time' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $opportunity->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    删除商机
    请求参数：
    路径参数：id（商机 ID）：必填，整数，商机的唯一标识 ID，用于指定待删除的商机
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，删除结果的描述信息
    @param int $id 商机 ID，用于指定待删除的商机
    @return \Illuminate\Http\JsonResponse JSON 响应，包含删除结果信息
     */
    public function destroy($id)
    {
        try {
            $opportunity = BusinessOpportunity::findOrFail($id);
            $opportunity->delete();

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
}
