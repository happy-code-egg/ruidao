<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 客户联系人管理控制器
 * 
 * 提供客户联系人信息的增删改查功能
 * 包括联系人基本信息、联系方式、职位信息等的管理
 * 
 * @package App\Http\Controllers\Api
 * @author 系统管理员
 */
class CustomerContactController extends Controller
{
    /**
     * 获取联系人列表
     * 
     * 请求参数：
     * - customer_id（客户ID）：可选，整数，筛选指定客户的联系人
     * - customer_name（客户名称）：可选，字符串，模糊搜索客户名称
     * - name（联系人姓名）：可选，字符串，模糊搜索联系人姓名
     * - contact_type（联系人类型）：可选，字符串，筛选联系人类型
     * - phone（手机号）：可选，字符串，模糊搜索手机号
     * - is_on_job（在职状态）：可选，布尔值，筛选在职状态
     * - department（部门）：可选，字符串，模糊搜索部门
     * - business_staff（业务员姓名）：可选，字符串，模糊搜索业务员姓名
     * - business_staff_id（业务员ID）：可选，整数，筛选业务员ID
     * - per_page（每页数量）：可选，整数，默认20
     * - page（页码）：可选，整数，默认1
     * 
     * 返回参数：
     * - id（联系人ID）：整数，联系人主键ID
     * - customer_id（客户ID）：整数，关联的客户ID
     * - customerName（客户名称）：字符串，客户名称
     * - name（联系人姓名）：字符串，联系人姓名
     * - contactType（联系人类型）：字符串，联系人类型
     * - phone（手机号）：字符串，手机号码
     * - landline（座机）：字符串，座机号码
     * - gender（性别）：字符串，性别（男/女）
     * - isOnJob（在职状态）：布尔值，是否在职
     * - position（职位）：字符串，职位
     * - department（部门）：字符串，部门
     * - title（职称）：字符串，职称
     * - email（邮箱）：字符串，邮箱地址
     * - businessStaffId（业务员ID）：整数，业务员用户ID
     * - businessStaffName（业务员姓名）：字符串，业务员姓名
     * - workAddress（工作地址）：字符串，工作地址
     * - remark（备注）：字符串，备注信息
     * - createUserId（创建人ID）：整数，创建人用户ID
     * - createUserName（创建人姓名）：字符串，创建人姓名
     * - createdAt（创建时间）：时间戳，创建时间
     * - updateUserId（更新人ID）：整数，更新人用户ID
     * - updateUserName（更新人姓名）：字符串，更新人姓名
     * - updatedAt（更新时间）：时间戳，更新时间
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function index(Request $request)
    {
        try {
            // 构建基础查询，关联客户表和用户表
            $query = DB::table('customer_contacts as cc')
                ->join('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as bu', 'cc.business_staff_id', '=', 'bu.id')
                ->leftJoin('users as cu', 'cc.created_by', '=', 'cu.id')
                ->leftJoin('users as uu', 'cc.updated_by', '=', 'uu.id')
                ->select([
                    'cc.id',
                    'cc.customer_id',
                    'c.customer_name as customerName',
                    'cc.contact_name as name',
                    'cc.contact_type_text as contactType',
                    'cc.phone',
                    'cc.telephone as landline',
                    'cc.gender',
                    'cc.is_on_job as isOnJob',
                    'cc.position',
                    'cc.department',
                    'cc.title',
                    'cc.email',
                    'cc.business_staff_id as businessStaffId',
                    'bu.real_name as businessStaffName',
                    'cc.work_address as workAddress',
                    'cc.remarks as remark',
                    'cc.created_by as createUserId',
                    'cu.real_name as createUserName',
                    'cc.created_at as createdAt',
                    'cc.updated_by as updateUserId',
                    'uu.real_name as updateUserName',
                    'cc.updated_at as updatedAt'
                ])
                ->whereNull('cc.deleted_at'); // 排除已删除的记录

            // 搜索条件处理
            // 重要：只显示指定客户的联系人
            if ($request->filled('customer_id')) {
                $query->where('cc.customer_id', $request->customer_id);
            }

            // 客户名称模糊搜索
            if ($request->filled('customer_name')) {
                $query->where('c.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            // 联系人姓名模糊搜索
            if ($request->filled('name')) {
                $query->where('cc.contact_name', 'like', '%' . $request->name . '%');
            }

            // 联系人类型筛选
            if ($request->filled('contact_type')) {
                $query->where('cc.contact_type_text', $request->contact_type);
            }

            // 手机号模糊搜索
            if ($request->filled('phone')) {
                $query->where('cc.phone', 'like', '%' . $request->phone . '%');
            }

            // 在职状态筛选
            if ($request->filled('is_on_job')) {
                $query->where('cc.is_on_job', $request->is_on_job == 'true' || $request->is_on_job == 1);
            }

            // 部门模糊搜索
            if ($request->filled('department')) {
                $query->where('cc.department', 'like', '%' . $request->department . '%');
            }

            // 业务员姓名模糊搜索
            if ($request->filled('business_staff')) {
                $query->where('bu.real_name', 'like', '%' . $request->business_staff . '%');
            }

            // 业务员ID筛选
            if ($request->filled('business_staff_id')) {
                $query->where('cc.business_staff_id', $request->business_staff_id);
            }

            // 分页参数处理
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            
            // 获取总数和分页数据
            $total = $query->count();
            $contacts = $query->orderBy('cc.created_at', 'desc') // 按创建时间倒序
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $contacts,
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
     * 获取联系人详情
     * 
     * 请求参数：
     * - id（联系人ID）：必填，整数，联系人主键ID
     * 
     * 返回参数：
     * - 联系人完整信息，包含关联的客户信息和用户信息
     * - customerName（客户名称）：字符串，关联客户名称
     * - business_staff_name（业务员姓名）：字符串，业务员姓名
     * - create_user_name（创建人姓名）：字符串，创建人姓名
     * - update_user_name（更新人姓名）：字符串，更新人姓名
     * - 其他联系人基本字段
     * 
     * @param int $id 联系人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function show($id)
    {
        try {
            // 查询联系人详情，关联相关表获取完整信息
            $contact = DB::table('customer_contacts as cc')
                ->join('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as bu', 'cc.business_staff_id', '=', 'bu.id')
                ->leftJoin('users as cu', 'cc.created_by', '=', 'cu.id')
                ->leftJoin('users as uu', 'cc.updated_by', '=', 'uu.id')
                ->select([
                    'cc.*', // 联系人所有字段
                    'c.customer_name as customerName',
                    'bu.real_name as business_staff_name',
                    'cu.real_name as create_user_name',
                    'uu.real_name as update_user_name'
                ])
                ->where('cc.id', $id)
                ->whereNull('cc.deleted_at') // 排除已删除的记录
                ->first();

            // 检查联系人是否存在
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => '联系人不存在'
                ], 404);
            }

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contact
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
     * 创建联系人
     * 
     * 请求参数：
     * - customer_id（客户ID）：必填，整数，必须存在于customers表
     * - contact_name（联系人姓名）：必填，字符串，最大50字符
     * - contact_type_text（联系人类型）：必填，字符串，最大50字符
     * - phone（手机号）：必填，字符串，最大50字符
     * - email（邮箱）：可选，邮箱格式，最大100字符
     * - gender（性别）：可选，字符串，只能是"男"或"女"
     * - is_on_job（在职状态）：可选，布尔值
     * - position（职位）：可选，字符串，最大50字符
     * - department（部门）：可选，字符串，最大100字符
     * - title（职称）：可选，字符串，最大50字符
     * - business_staff（业务员）：可选，字符串，最大100字符
     * - work_address（工作地址）：可选，字符串，最大200字符
     * - telephone（座机）：可选，字符串，最大50字符
     * - wechat（微信）：可选，字符串，最大50字符
     * - qq（QQ）：可选，字符串，最大20字符
     * - address（地址）：可选，字符串
     * - id_card（身份证）：可选，字符串，最大20字符
     * - remarks（备注）：可选，字符串
     * 
     * 返回参数：
     * - id（联系人ID）：整数，新创建的联系人ID
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function store(Request $request)
    {
        try {
            // 数据预处理 - 在验证之前处理
            $data = $request->all();
            
            // 将空字符串转换为null（解决验证问题）
            foreach (['gender', 'telephone', 'email', 'work_email', 'wechat', 'qq', 'address', 'work_address', 'id_card', 'remarks', 'position', 'department', 'title', 'business_staff_id'] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            // 字段映射处理：将前端字段名映射为数据库字段名
            if (isset($data['name'])) {
                $data['contact_name'] = $data['name'];
                unset($data['name']);
            }
            if (isset($data['contactType'])) {
                $data['contact_type_text'] = $data['contactType'];
                unset($data['contactType']);
            }
            if (isset($data['businessStaffId'])) {
                $data['business_staff_id'] = $data['businessStaffId'];
                unset($data['businessStaffId']);
            }
            if (isset($data['workAddress'])) {
                $data['work_address'] = $data['workAddress'];
                unset($data['workAddress']);
            }
            if (isset($data['workEmail'])) {
                $data['email'] = $data['workEmail'];
                $data['work_email'] = $data['workEmail'];
                unset($data['workEmail']);
            }
            if (isset($data['landline'])) {
                $data['telephone'] = $data['landline'];
                unset($data['landline']);
            }
            if (isset($data['remark'])) {
                $data['remarks'] = $data['remark'];
                unset($data['remark']);
            }
            
            // 处理布尔值字段：在职状态
            if (isset($data['isOnJob'])) {
                if ($data['isOnJob'] === '是' || $data['isOnJob'] === 'true' || $data['isOnJob'] === true || $data['isOnJob'] === 1 || $data['isOnJob'] === '1') {
                    $data['is_on_job'] = true;
                } else {
                    $data['is_on_job'] = false;
                }
                unset($data['isOnJob']);
            }
            
            // 验证请求数据
            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',           // 客户ID：必填，必须存在
                'contact_name' => 'required|string|max:50',               // 联系人姓名：必填
                'contact_type_text' => 'required|string|max:50',          // 联系人类型：必填
                'phone' => 'required|string|max:50',                      // 手机号：必填
                'email' => 'nullable|email|max:100',                      // 邮箱：可选邮箱格式
                'gender' => [                                             // 性别：可选，只能是男或女
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if ($value !== null && !in_array($value, ['男', '女'])) {
                            $fail('性别必须是男或女');
                        }
                    }
                ],
                'is_on_job' => 'nullable|boolean',                        // 在职状态：可选布尔值
                'position' => 'nullable|string|max:50',                   // 职位：可选
                'department' => 'nullable|string|max:100',                // 部门：可选
                'title' => 'nullable|string|max:50',                      // 职称：可选
                'business_staff' => 'nullable|string|max:100',            // 业务员：可选
                'work_address' => 'nullable|string|max:200',              // 工作地址：可选
                'telephone' => 'nullable|string|max:50',                  // 座机：可选
                'wechat' => 'nullable|string|max:50',                     // 微信：可选
                'qq' => 'nullable|string|max:20',                         // QQ：可选
                'address' => 'nullable|string',                           // 地址：可选
                'id_card' => 'nullable|string|max:20',                    // 身份证：可选
                'remarks' => 'nullable|string'                            // 备注：可选
            ]);

            // 验证失败处理
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 设置系统字段
            $data['created_by'] = auth()->id() ?? 1;  // 设置创建人ID
            $data['updated_by'] = auth()->id() ?? 1;  // 设置更新人ID
            $data['status'] = 1;                      // 设置状态为有效
            $data['created_at'] = now();              // 设置创建时间
            $data['updated_at'] = now();              // 设置更新时间

            // 插入数据并获取ID
            $contactId = DB::table('customer_contacts')->insertGetId($data);

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $contactId]
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
     * 更新联系人
     * 
     * 请求参数：
     * - id（联系人ID）：必填，整数，URL路径参数
     * - 其他参数同创建接口，均为可选更新字段
     * 
     * 返回参数：
     * - 无具体数据，仅返回操作结果
     * 
     * @param Request $request HTTP请求对象
     * @param int $id 联系人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function update(Request $request, $id)
    {
        try {
            // 检查联系人是否存在
            $contact = DB::table('customer_contacts')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => '联系人不存在'
                ], 404);
            }

            // 数据预处理 - 在验证之前处理
            $data = $request->all();
            
            // 将空字符串转换为null（解决验证问题）
            foreach (['gender', 'telephone', 'email', 'work_email', 'wechat', 'qq', 'address', 'work_address', 'id_card', 'remarks', 'position', 'department', 'title', 'business_staff_id'] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            // 字段映射处理：将前端字段名映射为数据库字段名
            if (isset($data['name'])) {
                $data['contact_name'] = $data['name'];
                unset($data['name']);
            }
            if (isset($data['contactType'])) {
                $data['contact_type_text'] = $data['contactType'];
                unset($data['contactType']);
            }
            if (isset($data['businessStaffId'])) {
                $data['business_staff_id'] = $data['businessStaffId'];
                unset($data['businessStaffId']);
            }
            if (isset($data['workAddress'])) {
                $data['work_address'] = $data['workAddress'];
                unset($data['workAddress']);
            }
            if (isset($data['workEmail'])) {
                $data['email'] = $data['workEmail'];
                $data['work_email'] = $data['workEmail'];
                unset($data['workEmail']);
            }
            if (isset($data['landline'])) {
                $data['telephone'] = $data['landline'];
                unset($data['landline']);
            }
            if (isset($data['remark'])) {
                $data['remarks'] = $data['remark'];
                unset($data['remark']);
            }
            
            // 处理布尔值字段：在职状态
            if (isset($data['isOnJob'])) {
                if ($data['isOnJob'] === '是' || $data['isOnJob'] === 'true' || $data['isOnJob'] === true || $data['isOnJob'] === 1 || $data['isOnJob'] === '1') {
                    $data['is_on_job'] = true;
                } else {
                    $data['is_on_job'] = false;
                }
                unset($data['isOnJob']);
            }

            // 验证请求数据（与创建时相同的规则）
            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',
                'contact_name' => 'required|string|max:50',
                'contact_type_text' => 'required|string|max:50',
                'phone' => 'required|string|max:50',
                'email' => 'nullable|email|max:100',
                'gender' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if ($value !== null && !in_array($value, ['男', '女'])) {
                            $fail('性别必须是男或女');
                        }
                    }
                ],
                'is_on_job' => 'nullable|boolean',
                'position' => 'nullable|string|max:50',
                'department' => 'nullable|string|max:100',
                'title' => 'nullable|string|max:50',
                'business_staff' => 'nullable|string|max:100',
                'work_address' => 'nullable|string|max:200',
                'telephone' => 'nullable|string|max:50',
                'wechat' => 'nullable|string|max:50',
                'qq' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'id_card' => 'nullable|string|max:20',
                'remarks' => 'nullable|string'
            ]);

            // 验证失败处理
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 移除不应该更新的字段（前端展示字段和不存在的数据库字段）
            unset($data['customerName'], $data['businessStaff'], $data['createUser'], $data['createTime'], $data['updateUser'], $data['updateTime']);
            
            $data['updated_by'] = auth()->id() ?? 1;  // 设置更新人ID
            $data['updated_at'] = now();              // 设置更新时间

            // 执行更新操作
            DB::table('customer_contacts')->where('id', $id)->update($data);

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
     * 删除联系人
     * 
     * 请求参数：
     * - id（联系人ID）：必填，整数，URL路径参数
     * 
     * 返回参数：
     * - 无具体数据，仅返回操作结果
     * 
     * @param int $id 联系人ID
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function destroy($id)
    {
        try {
            // 检查联系人是否存在
            $contact = DB::table('customer_contacts')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => '联系人不存在'
                ], 404);
            }

            // 软删除：设置deleted_at字段
            DB::table('customer_contacts')->where('id', $id)->update([
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
     * 获取客户选项列表
     * 
     * 请求参数：
     * - 无
     * 
     * 返回参数：
     * - value（客户ID）：整数，客户主键ID
     * - label（客户名称）：字符串，客户名称
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function getCustomerOptions()
    {
        try {
            // 查询所有有效客户，用于下拉选择
            $customers = DB::table('customers')
                ->select(['id as value', 'customer_name as label'])
                ->whereNull('deleted_at') // 排除已删除的客户
                ->orderBy('customer_name') // 按客户名称排序
                ->get();

            // 返回成功响应
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $customers
            ]);

        } catch (\Exception $e) {
            // 异常处理，返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }
}