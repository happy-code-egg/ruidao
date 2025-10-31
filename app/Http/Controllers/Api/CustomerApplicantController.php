<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 客户申请人管理控制器
 * 
 * 提供客户申请人信息的增删改查功能
 * 包括申请人基本信息、费减信息、地址信息等的管理
 * 
 * @package App\Http\Controllers\Api
 * @author 系统管理员
 */
class CustomerApplicantController extends Controller
{
    /**
     * 获取申请人列表
     * 
     * 请求参数：
     * - customer_id（客户ID）：可选，整数，筛选指定客户的申请人
     * - customer_name（客户名称）：可选，字符串，模糊搜索客户名称
     * - applicant_name_cn（申请人中文名）：可选，字符串，模糊搜索申请人中文名
     * - applicant_name_en（申请人英文名）：可选，字符串，模糊搜索申请人英文名
     * - country（国家）：可选，字符串，筛选国家
     * - is_valid（费减有效性）：可选，字符串，1=有效，0=无效
     * - valid_start_date（费减开始日期）：可选，日期，筛选费减开始日期
     * - valid_end_date（费减结束日期）：可选，日期，筛选费减结束日期
     * - sync_start_date（同步开始日期）：可选，日期，筛选同步开始日期
     * - sync_end_date（同步结束日期）：可选，日期，筛选同步结束日期
     * - province（省份）：可选，字符串，筛选省份
     * - city（城市）：可选，字符串，筛选城市
     * - district（区县）：可选，字符串，筛选区县
     * - applicant_type（申请人类型）：可选，字符串，筛选申请人类型
     * - entity_type（实体类型）：可选，字符串，筛选实体类型
     * - business_staff（业务员）：可选，字符串，模糊搜索业务员姓名
     * - per_page（每页数量）：可选，整数，默认10
     * - page（页码）：可选，整数，默认1
     * 
     * 返回参数：
     * - id（申请人ID）：整数，申请人主键ID
     * - customer_id（客户ID）：整数，关联的客户ID
     * - customerName（客户名称）：字符串，客户名称
     * - customerCode（客户编码）：字符串，客户编码
     * - customerNumber（客户编号）：字符串，客户编号
     * - applicantName（申请人中文名）：字符串，申请人中文名称
     * - applicantNameEn（申请人英文名）：字符串，申请人英文名称
     * - applicant_type（申请人类型）：字符串，申请人类型
     * - entity_type（实体类型）：字符串，实体类型
     * - applicant_code（申请人编码）：字符串，申请人唯一编码
     * - id_type（证件类型）：字符串，证件类型
     * - id_number（证件号码）：字符串，证件号码
     * - totalConditionNo（总条件编号）：字符串，总条件编号
     * - fee_reduction（费减标识）：布尔值，是否享受费减
     * - feeReductionStartDate（费减开始日期）：日期，费减开始日期
     * - feeReductionEndDate（费减结束日期）：日期，费减结束日期
     * - syncDate（同步日期）：日期，数据同步日期
     * - country（国家）：字符串，国家
     * - province（省份）：字符串，省份
     * - city（城市）：字符串，城市
     * - district（区县）：字符串，区县
     * - street（街道）：字符串，街道地址
     * - postalCode（邮政编码）：字符串，邮政编码
     * - addressEn（英文地址）：字符串，英文地址
     * - business_location（营业地址）：字符串，营业地址
     * - remark（备注）：字符串，备注信息
     * - email（邮箱）：字符串，邮箱地址
     * - phone（电话）：字符串，联系电话
     * - administrativeArea（行政区域）：字符串，省/市/区组合
     * - businessStaff（业务员）：字符串，业务员姓名
     * - businessStaffId（业务员ID）：整数，业务员用户ID
     * - createUser（创建人）：字符串，创建人姓名
     * - createUserId（创建人ID）：整数，创建人用户ID
     * - createTime（创建时间）：时间戳，创建时间
     * - updateUser（更新人）：字符串，更新人姓名
     * - updateUserId（更新人ID）：整数，更新人用户ID
     * - updateTime（更新时间）：时间戳，更新时间
     * - createDate（创建日期）：日期，创建日期
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function index(Request $request)
    {
        try {
            // 构建基础查询，关联客户表和用户表
            $query = DB::table('customer_applicants as ca')
                ->join('customers as c', 'ca.customer_id', '=', 'c.id')
                ->leftJoin('users as bs', 'ca.business_staff_id', '=', 'bs.id')
                ->leftJoin('users as creator', 'ca.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'ca.updated_by', '=', 'updater.id')
                ->select([
                    'ca.id',
                    'ca.customer_id',
                    'c.customer_name as customerName',
                    'c.customer_code as customerCode',
                    'c.customer_no as customerNumber',
                    'ca.applicant_name_cn as applicantName',
                    'ca.applicant_name_en as applicantNameEn',
                    'ca.applicant_type',
                    'ca.entity_type',
                    'ca.applicant_code',
                    'ca.id_type',
                    'ca.id_number',
                    'ca.total_condition_no as totalConditionNo',
                    'ca.fee_reduction',
                    'ca.fee_reduction_start_date as feeReductionStartDate',
                    'ca.fee_reduction_end_date as feeReductionEndDate',
                    'ca.sync_date as syncDate',
                    'ca.country',
                    'ca.province',
                    'ca.city',
                    'ca.district',
                    'ca.street',
                    'ca.postal_code as postalCode',
                    'ca.address_en as addressEn',
                    'ca.business_location',
                    'ca.remark',
                    'ca.email',
                    'ca.phone',
                    DB::raw("CONCAT_WS('/', ca.province, ca.city, ca.district) as administrativeArea"), // 拼接行政区域
                    'bs.real_name as businessStaff',
                    'ca.business_staff_id as businessStaffId',
                    'creator.real_name as createUser',
                    'ca.created_by as createUserId',
                    'ca.created_at as createTime',
                    'updater.real_name as updateUser',
                    'ca.updated_by as updateUserId',
                    'ca.updated_at as updateTime',
                    DB::raw("DATE(ca.created_at) as createDate") // 提取创建日期
                ])
                ->whereNull('ca.deleted_at'); // 排除已删除的记录

            // 搜索条件处理
            // 重要：只显示指定客户的申请人
            if ($request->filled('customer_id')) {
                $query->where('ca.customer_id', $request->customer_id);
            }

            // 客户名称模糊搜索
            if ($request->filled('customer_name')) {
                $query->where('c.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            // 申请人中文名模糊搜索
            if ($request->filled('applicant_name_cn')) {
                $query->where('ca.applicant_name_cn', 'like', '%' . $request->applicant_name_cn . '%');
            }

            // 申请人英文名模糊搜索
            if ($request->filled('applicant_name_en')) {
                $query->where('ca.applicant_name_en', 'like', '%' . $request->applicant_name_en . '%');
            }

            // 国家筛选
            if ($request->filled('country')) {
                $query->where('ca.country', $request->country);
            }

            // 费减有效性筛选
            if ($request->filled('is_valid')) {
                $isValid = $request->is_valid == '1';
                if ($isValid) {
                    // 费减有效：费减标识为true且在有效期内
                    $query->where('ca.fee_reduction', true)
                          ->where('ca.fee_reduction_start_date', '<=', now())
                          ->where('ca.fee_reduction_end_date', '>=', now());
                } else {
                    // 费减无效：费减标识为false或不在有效期内
                    $query->where(function($q) {
                        $q->where('ca.fee_reduction', false)
                          ->orWhere('ca.fee_reduction_start_date', '>', now())
                          ->orWhere('ca.fee_reduction_end_date', '<', now())
                          ->orWhereNull('ca.fee_reduction_start_date')
                          ->orWhereNull('ca.fee_reduction_end_date');
                    });
                }
            }

            // 费减开始日期筛选
            if ($request->filled('valid_start_date')) {
                $query->where('ca.fee_reduction_start_date', '>=', $request->valid_start_date);
            }

            // 费减结束日期筛选
            if ($request->filled('valid_end_date')) {
                $query->where('ca.fee_reduction_end_date', '<=', $request->valid_end_date);
            }

            // 同步开始日期筛选
            if ($request->filled('sync_start_date')) {
                $query->where('ca.sync_date', '>=', $request->sync_start_date);
            }

            // 同步结束日期筛选
            if ($request->filled('sync_end_date')) {
                $query->where('ca.sync_date', '<=', $request->sync_end_date);
            }

            // 省份筛选
            if ($request->filled('province')) {
                $query->where('ca.province', $request->province);
            }

            // 城市筛选
            if ($request->filled('city')) {
                $query->where('ca.city', $request->city);
            }

            // 区县筛选
            if ($request->filled('district')) {
                $query->where('ca.district', $request->district);
            }

            // 申请人类型筛选
            if ($request->filled('applicant_type')) {
                $query->where('ca.applicant_type', $request->applicant_type);
            }

            // 实体类型筛选
            if ($request->filled('entity_type')) {
                $query->where('ca.entity_type', $request->entity_type);
            }

            // 业务员姓名模糊搜索
            if ($request->filled('business_staff')) {
                $query->where('bs.real_name', 'like', '%' . $request->business_staff . '%');
            }

            // 分页参数处理
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            // 获取总数和分页数据
            $total = $query->count();
            $applicants = $query->orderBy('ca.created_at', 'desc') // 按创建时间倒序
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $applicants,
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取申请人详情
     * 
     * 请求参数：
     * - id（申请人ID）：必填，整数，申请人主键ID
     * 
     * 返回参数：
     * - 申请人完整信息，包含关联的客户信息和用户信息
     * - customerName（客户名称）：字符串，关联客户名称
     * - customerCode（客户编码）：字符串，关联客户编码
     * - customerNumber（客户编号）：字符串，关联客户编号
     * - businessStaff（业务员）：字符串，业务员姓名
     * - createUser（创建人）：字符串，创建人姓名
     * - updateUser（更新人）：字符串，更新人姓名
     * - 其他申请人基本字段
     * 
     * @param int $id 申请人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function show($id)
    {
        try {
            // 查询申请人详情，关联相关表获取完整信息
            $applicant = DB::table('customer_applicants as ca')
                ->join('customers as c', 'ca.customer_id', '=', 'c.id')
                ->leftJoin('users as bs', 'ca.business_staff_id', '=', 'bs.id')
                ->leftJoin('users as creator', 'ca.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'ca.updated_by', '=', 'updater.id')
                ->select([
                    'ca.*', // 申请人所有字段
                    'c.customer_name as customerName',
                    'c.customer_code as customerCode',
                    'c.customer_no as customerNumber',
                    'bs.real_name as businessStaff',
                    'creator.real_name as createUser',
                    'updater.real_name as updateUser'
                ])
                ->where('ca.id', $id)
                ->whereNull('ca.deleted_at') // 排除已删除的记录
                ->first();

            // 检查申请人是否存在
            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $applicant
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建申请人
     * 
     * 请求参数：
     * - customer_id（客户ID）：必填，整数，必须存在于customers表
     * - applicant_name_cn（申请人中文名）：必填，字符串，最大200字符
     * - applicant_name_en（申请人英文名）：可选，字符串，最大200字符
     * - applicant_type（申请人类型）：可选，字符串，最大50字符
     * - id_type（证件类型）：可选，字符串，最大50字符
     * - id_number（证件号码）：可选，字符串，最大100字符
     * - country（国家）：可选，字符串，最大50字符
     * - business_location（营业地址）：可选，字符串，最大100字符
     * - fee_reduction（费减标识）：可选，布尔值
     * - fee_reduction_start_date（费减开始日期）：可选，日期格式
     * - fee_reduction_end_date（费减结束日期）：可选，日期格式
     * - province（省份）：可选，字符串，最大50字符
     * - city（城市）：可选，字符串，最大50字符
     * - district（区县）：可选，字符串，最大50字符
     * - street（街道）：可选，字符串，最大200字符
     * - postal_code（邮政编码）：可选，字符串，最大20字符
     * - entity_type（实体类型）：可选，字符串，最大50字符
     * - address_en（英文地址）：可选，字符串，最大500字符
     * - total_condition_no（总条件编号）：可选，字符串，最大100字符
     * - sync_date（同步日期）：可选，日期格式
     * - email（邮箱）：可选，邮箱格式，最大100字符
     * - phone（电话）：可选，字符串，最大50字符
     * - business_staff_id（业务员ID）：可选，整数，必须存在于users表
     * - remark（备注）：可选，字符串
     * 
     * 返回参数：
     * - id（申请人ID）：整数，新创建的申请人ID
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function store(Request $request)
    {
        try {
            // 验证请求数据
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',           // 客户ID：必填，必须存在
                'applicant_name_cn' => 'required|string|max:200',         // 申请人中文名：必填
                'applicant_name_en' => 'nullable|string|max:200',         // 申请人英文名：可选
                'applicant_type' => 'nullable|string|max:50',             // 申请人类型：可选
                'id_type' => 'nullable|string|max:50',                    // 证件类型：可选
                'id_number' => 'nullable|string|max:100',                 // 证件号码：可选
                'country' => 'nullable|string|max:50',                    // 国家：可选
                'business_location' => 'nullable|string|max:100',         // 营业地址：可选
                'fee_reduction' => 'nullable|boolean',                    // 费减标识：可选布尔值
                'fee_reduction_start_date' => 'nullable|date',            // 费减开始日期：可选日期
                'fee_reduction_end_date' => 'nullable|date',              // 费减结束日期：可选日期
                'province' => 'nullable|string|max:50',                   // 省份：可选
                'city' => 'nullable|string|max:50',                       // 城市：可选
                'district' => 'nullable|string|max:50',                   // 区县：可选
                'street' => 'nullable|string|max:200',                    // 街道：可选
                'postal_code' => 'nullable|string|max:20',                // 邮政编码：可选
                'entity_type' => 'nullable|string|max:50',                // 实体类型：可选
                'address_en' => 'nullable|string|max:500',                // 英文地址：可选
                'total_condition_no' => 'nullable|string|max:100',        // 总条件编号：可选
                'sync_date' => 'nullable|date',                           // 同步日期：可选日期
                'email' => 'nullable|email|max:100',                      // 邮箱：可选邮箱格式
                'phone' => 'nullable|string|max:50',                      // 电话：可选
                'business_staff_id' => 'nullable|exists:users,id',        // 业务员ID：可选，必须存在
                'remark' => 'nullable|string'                             // 备注：可选
            ]);

            // 验证失败处理
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 准备插入数据
            $data = $request->all();
            
            // 生成申请人编号
            $data['applicant_code'] = $this->generateApplicantCode();
            $data['created_by'] = auth()->id() ?? 1;  // 设置创建人ID
            $data['updated_by'] = auth()->id() ?? 1;  // 设置更新人ID
            $data['created_at'] = now();              // 设置创建时间
            $data['updated_at'] = now();              // 设置更新时间

            // 插入数据并获取ID
            $applicantId = DB::table('customer_applicants')->insertGetId($data);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $applicantId]
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新申请人
     * 
     * 请求参数：
     * - id（申请人ID）：必填，整数，URL路径参数
     * - 其他参数同创建接口，均为可选更新字段
     * 
     * 返回参数：
     * - 无具体数据，仅返回操作结果
     * 
     * @param Request $request HTTP请求对象
     * @param int $id 申请人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function update(Request $request, $id)
    {
        try {
            // 检查申请人是否存在
            $applicant = DB::table('customer_applicants')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

            // 验证请求数据（与创建时相同的规则）
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'applicant_name_cn' => 'required|string|max:200',
                'applicant_name_en' => 'nullable|string|max:200',
                'applicant_type' => 'nullable|string|max:50',
                'id_type' => 'nullable|string|max:50',
                'id_number' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:50',
                'business_location' => 'nullable|string|max:100',
                'fee_reduction' => 'nullable|boolean',
                'fee_reduction_start_date' => 'nullable|date',
                'fee_reduction_end_date' => 'nullable|date',
                'province' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'district' => 'nullable|string|max:50',
                'street' => 'nullable|string|max:200',
                'postal_code' => 'nullable|string|max:20',
                'entity_type' => 'nullable|string|max:50',
                'address_en' => 'nullable|string|max:500',
                'total_condition_no' => 'nullable|string|max:100',
                'sync_date' => 'nullable|date',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:50',
                'business_staff_id' => 'nullable|exists:users,id',
                'remark' => 'nullable|string'
            ]);

            // 验证失败处理
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 准备更新数据
            $data = $request->all();
            // 移除不应该更新的字段（前端可能传递的显示字段）
            unset($data['customerName'], $data['applicantName'], $data['applicantNameEn'],
                  $data['administrativeArea'], $data['createUser'], $data['createTime'],
                  $data['updateUser'], $data['updateTime'], $data['feeReductionStartDate'],
                  $data['feeReductionEndDate'], $data['id'], $data['applicant_code']);

            $data['updated_by'] = auth()->id() ?? 1;  // 设置更新人ID
            $data['updated_at'] = now();              // 设置更新时间

            // 执行更新操作
            DB::table('customer_applicants')->where('id', $id)->update($data);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '更新成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除申请人
     * 
     * 请求参数：
     * - id（申请人ID）：必填，整数，URL路径参数
     * 
     * 返回参数：
     * - 无具体数据，仅返回操作结果
     * 
     * @param int $id 申请人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function destroy($id)
    {
        try {
            // 检查申请人是否存在
            $applicant = DB::table('customer_applicants')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

            // 软删除：设置deleted_at字段
            DB::table('customer_applicants')->where('id', $id)->update([
                'deleted_at' => now()
            ]);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成申请人编号
     * 
     * 生成规则：APP + 年月日(8位) + 4位流水号
     * 例如：APP202401010001
     * 
     * 返回参数：
     * - 申请人编号：字符串，格式为APP+日期+流水号
     * 
     * @return string 申请人编号
     */
    private function generateApplicantCode()
    {
        $prefix = 'APP';                    // 固定前缀
        $date = date('Ymd');               // 当前日期，格式：YYYYMMDD
        
        // 获取今日最大编号，用于生成流水号
        $maxCode = DB::table('customer_applicants')
            ->where('applicant_code', 'like', $prefix . $date . '%')
            ->orderBy('applicant_code', 'desc')
            ->value('applicant_code');
        
        if ($maxCode) {
            // 如果存在记录，提取流水号并加1
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            // 如果不存在记录，从1开始
            $number = 1;
        }
        
        // 返回完整编号：前缀 + 日期 + 4位流水号（左补0）
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}