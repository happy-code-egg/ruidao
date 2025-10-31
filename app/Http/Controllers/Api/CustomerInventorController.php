<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 客户发明人管理控制器
 * 
 * 负责客户发明人信息的增删改查操作，包括：
 * - 发明人列表查询（支持多条件搜索和分页）
 * - 发明人详情查看
 * - 发明人信息创建
 * - 发明人信息更新
 * - 发明人信息删除（软删除）
 * 
 * 支持的发明人信息包括：
 * - 基本信息：中英文姓名、发明人类型、性别、联系方式
 * - 身份信息：证件类型、证件号码
 * - 地址信息：国家、省市区、详细地址
 * - 工作信息：工作单位、部门、职位
 * - 关联信息：所属客户、业务员
 * - 系统信息：创建人、更新人、时间戳
 * 
 * @package App\Http\Controllers\Api
 * @author EMA System
 * @version 1.0
 */
class CustomerInventorController extends Controller
{
    /**
     * 获取客户发明人列表
     * 
     * 获取客户发明人列表，支持多种搜索条件和分页功能。
     * 返回发明人的基本信息、联系方式、地址信息、工作信息等。
     * 
     * @param Request $request HTTP请求对象，包含以下可选参数：
     *   - customer_id: int 客户ID（重要：只显示指定客户的发明人）
     *   - customer_name: string 客户名称（模糊搜索）
     *   - inventor_name: string 发明人中文姓名（模糊搜索）
     *   - inventor_name_en: string 发明人英文姓名（模糊搜索）
     *   - phone: string 手机号码（模糊搜索）
     *   - inventor_type: string 发明人类型（精确匹配）
     *   - position: string 职位（模糊搜索）
     *   - country: string 国家（精确匹配）
     *   - province: string 省份（精确匹配）
     *   - city: string 城市（精确匹配）
     *   - district: string 区县（精确匹配）
     *   - business_staff: string 业务员姓名（模糊搜索）
     *   - per_page: int 每页数量，默认10
     *   - page: int 页码，默认1
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "list": [
     *       {
     *         "id": 1,
     *         "customer_id": 1,
     *         "customerName": "客户名称",
     *         "customerCode": "客户编码",
     *         "customerNumber": "客户编号",
     *         "inventorName": "发明人中文姓名",
     *         "inventorNameEn": "发明人英文姓名",
     *         "inventor_type": "发明人类型",
     *         "gender": "性别",
     *         "phone": "手机号码",
     *         "landline": "座机号码",
     *         "wechat": "微信号",
     *         "country": "国家",
     *         "administrativeArea": "省/市/区",
     *         "idNumber": "证件号码",
     *         "id_type": "证件类型",
     *         "position": "职位",
     *         "businessStaff": "业务员姓名",
     *         "businessStaffId": 1,
     *         "province": "省份",
     *         "city": "城市",
     *         "district": "区县",
     *         "street": "街道",
     *         "address": "详细地址",
     *         "addressEn": "英文地址",
     *         "email": "邮箱",
     *         "workUnit": "工作单位",
     *         "department": "部门",
     *         "remark": "备注",
     *         "createDate": "创建日期",
     *         "createUser": "创建人",
     *         "createTime": "创建时间",
     *         "updateUser": "更新人",
     *         "updateTime": "更新时间"
     *       }
     *     ],
     *     "total": 100,
     *     "per_page": 10,
     *     "current_page": 1,
     *     "last_page": 10
     *   }
     * }
     * 
     * 失败响应格式：
     * {
     *   "success": false,
     *   "message": "获取失败：错误信息"
     * }
     * 
     * @throws \Exception 当数据库查询失败时抛出异常
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('customer_inventors as ci')
                ->join('customers as c', 'ci.customer_id', '=', 'c.id')
                ->leftJoin('users as bs', 'ci.business_staff_id', '=', 'bs.id')
                ->leftJoin('users as creator', 'ci.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'ci.updated_by', '=', 'updater.id')
                ->select([
                    'ci.id',
                    'ci.customer_id',
                    'c.customer_name as customerName',
                    'c.customer_code as customerCode',
                    'c.customer_no as customerNumber',
                    'ci.inventor_name_cn as inventorName',
                    'ci.inventor_name_en as inventorNameEn',
                    'ci.inventor_type',
                    'ci.gender',
                    'ci.phone',
                    'ci.landline',
                    'ci.wechat',
                    'ci.country',
                    DB::raw("CONCAT_WS('/', ci.province, ci.city, ci.district) as administrativeArea"),
                    'ci.id_number as idNumber',
                    'ci.id_type',
                    'ci.position',
                    'bs.real_name as businessStaff',
                    'ci.business_staff_id as businessStaffId',
                    'ci.province',
                    'ci.city',
                    'ci.district',
                    'ci.street',
                    'ci.address',
                    'ci.address_en as addressEn',
                    'ci.email',
                    'ci.work_unit as workUnit',
                    'ci.department',
                    'ci.remark',
                    DB::raw("DATE(ci.created_at) as createDate"),
                    'creator.real_name as createUser',
                    'ci.created_at as createTime',
                    'updater.real_name as updateUser',
                    'ci.updated_at as updateTime'
                ])
                ->whereNull('ci.deleted_at');

            // 搜索条件
            // 重要：只显示指定客户的发明人
            if ($request->filled('customer_id')) {
                $query->where('ci.customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->where('c.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            if ($request->filled('inventor_name')) {
                $query->where('ci.inventor_name_cn', 'like', '%' . $request->inventor_name . '%');
            }

            if ($request->filled('inventor_name_en')) {
                $query->where('ci.inventor_name_en', 'like', '%' . $request->inventor_name_en . '%');
            }

            if ($request->filled('phone')) {
                $query->where('ci.phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->filled('inventor_type')) {
                $query->where('ci.inventor_type', $request->inventor_type);
            }

            if ($request->filled('position')) {
                $query->where('ci.position', 'like', '%' . $request->position . '%');
            }

            if ($request->filled('country')) {
                $query->where('ci.country', $request->country);
            }

            if ($request->filled('province')) {
                $query->where('ci.province', $request->province);
            }

            if ($request->filled('city')) {
                $query->where('ci.city', $request->city);
            }

            if ($request->filled('district')) {
                $query->where('ci.district', $request->district);
            }

            if ($request->filled('business_staff')) {
                $query->where('bs.real_name', 'like', '%' . $request->business_staff . '%');
            }

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $inventors = $query->orderBy('ci.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $inventors,
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
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
     * 获取客户发明人详情
     * 
     * 根据发明人ID获取发明人的详细信息，包括关联的客户信息、业务员信息等。
     * 
     * @param int $id 发明人ID
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "id": 1,
     *     "customer_id": 1,
     *     "inventor_code": "INV20240101001",
     *     "inventor_name_cn": "发明人中文姓名",
     *     "inventor_name_en": "发明人英文姓名",
     *     "inventor_type": "发明人类型",
     *     "gender": "性别",
     *     "id_type": "证件类型",
     *     "id_number": "证件号码",
     *     "country": "国家",
     *     "province": "省份",
     *     "city": "城市",
     *     "district": "区县",
     *     "street": "街道",
     *     "postal_code": "邮政编码",
     *     "address": "详细地址",
     *     "address_en": "英文地址",
     *     "phone": "手机号码",
     *     "landline": "座机号码",
     *     "wechat": "微信号",
     *     "email": "邮箱",
     *     "work_unit": "工作单位",
     *     "department": "部门",
     *     "position": "职位",
     *     "business_staff_id": 1,
     *     "remark": "备注",
     *     "created_by": 1,
     *     "updated_by": 1,
     *     "created_at": "2024-01-01 00:00:00",
     *     "updated_at": "2024-01-01 00:00:00",
     *     "deleted_at": null,
     *     "customerName": "客户名称",
     *     "customerCode": "客户编码",
     *     "customerNumber": "客户编号",
     *     "businessStaff": "业务员姓名",
     *     "createUser": "创建人姓名",
     *     "updateUser": "更新人姓名"
     *   }
     * }
     * 
     * 失败响应格式（发明人不存在）：
     * {
     *   "success": false,
     *   "message": "发明人不存在"
     * }
     * 
     * 失败响应格式（其他错误）：
     * {
     *   "success": false,
     *   "message": "获取失败：错误信息"
     * }
     * 
     * @throws \Exception 当数据库查询失败时抛出异常
     */
    public function show($id)
    {
        try {
            $inventor = DB::table('customer_inventors as ci')
                ->join('customers as c', 'ci.customer_id', '=', 'c.id')
                ->leftJoin('users as bs', 'ci.business_staff_id', '=', 'bs.id')
                ->leftJoin('users as creator', 'ci.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'ci.updated_by', '=', 'updater.id')
                ->select([
                    'ci.*',
                    'c.customer_name as customerName',
                    'c.customer_code as customerCode',
                    'c.customer_no as customerNumber',
                    'bs.real_name as businessStaff',
                    'creator.real_name as createUser',
                    'updater.real_name as updateUser'
                ])
                ->where('ci.id', $id)
                ->whereNull('ci.deleted_at')
                ->first();

            if (!$inventor) {
                return response()->json([
                    'success' => false,
                    'message' => '发明人不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $inventor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建客户发明人
     * 
     * 创建新的客户发明人记录，支持前端驼峰命名和数据库下划线命名的字段映射。
     * 自动生成发明人编号，设置创建人和更新人信息。
     * 
     * @param Request $request HTTP请求对象，包含以下参数：
     *   必填项：
     *   - customer_id: int 客户ID（必须存在于customers表）
     *   - inventor_name_cn|inventorName: string 发明人中文姓名（最大100字符）
     *   
     *   可选项：
     *   - inventor_name_en|inventorNameEn: string 发明人英文姓名（最大100字符）
     *   - inventor_type|inventorType: string 发明人类型（最大50字符）
     *   - gender: string 性别（男/女）
     *   - id_type|idType: string 证件类型（最大50字符）
     *   - id_number|idNumber: string 证件号码（最大100字符）
     *   - country: string 国家（最大50字符）
     *   - province: string 省份（最大50字符）
     *   - city: string 城市（最大50字符）
     *   - district: string 区县（最大50字符）
     *   - street: string 街道（最大200字符）
     *   - postal_code: string 邮政编码（最大20字符）
     *   - address: string 详细地址
     *   - address_en: string 英文地址（最大500字符）
     *   - phone: string 手机号码（最大50字符）
     *   - landline: string 座机号码（最大50字符）
     *   - wechat: string 微信号（最大50字符）
     *   - email: string 邮箱（必须是有效邮箱格式，最大100字符）
     *   - work_unit|workUnit: string 工作单位（最大200字符）
     *   - department: string 部门（最大100字符）
     *   - position: string 职位（最大100字符）
     *   - business_staff_id|businessStaffId: int 业务员ID（必须存在于users表）
     *   - remark: string 备注
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "创建成功",
     *   "data": {
     *     "id": 1
     *   }
     * }
     * 
     * 失败响应格式（验证失败）：
     * {
     *   "success": false,
     *   "message": "验证失败",
     *   "errors": {
     *     "customer_id": ["客户ID字段是必需的"],
     *     "inventor_name_cn": ["发明人中文姓名字段是必需的"]
     *   }
     * }
     * 
     * 失败响应格式（其他错误）：
     * {
     *   "success": false,
     *   "message": "创建失败：错误信息"
     * }
     * 
     * @throws \Exception 当数据库操作失败时抛出异常
     */
    public function store(Request $request)
    {
        try {
            // 数据预处理 - 在验证之前处理
            $data = $request->all();
            
            // 将空字符串转换为null（解决gender验证问题）
            // 前端可能发送空字符串，但验证规则要求null或有效值
            if (isset($data['gender']) && $data['gender'] === '') {
                $data['gender'] = null;
            }
            
            // 字段映射（前端驼峰转数据库下划线）
            // 支持前端使用驼峰命名，后端转换为数据库下划线命名
            if (isset($data['inventorName'])) {
                $data['inventor_name_cn'] = $data['inventorName']; // 发明人中文姓名
                unset($data['inventorName']);
            }
            if (isset($data['inventorNameEn'])) {
                $data['inventor_name_en'] = $data['inventorNameEn']; // 发明人英文姓名
                unset($data['inventorNameEn']);
            }
            if (isset($data['inventorType'])) {
                $data['inventor_type'] = $data['inventorType']; // 发明人类型
                unset($data['inventorType']);
            }
            if (isset($data['idType'])) {
                $data['id_type'] = $data['idType']; // 证件类型
                unset($data['idType']);
            }
            if (isset($data['idNumber'])) {
                $data['id_number'] = $data['idNumber']; // 证件号码
                unset($data['idNumber']);
            }
            if (isset($data['businessStaffId'])) {
                $data['business_staff_id'] = $data['businessStaffId']; // 业务员ID
                unset($data['businessStaffId']);
            }
            if (isset($data['workUnit'])) {
                $data['work_unit'] = $data['workUnit']; // 工作单位
                unset($data['workUnit']);
            }
            
            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',
                'inventor_name_cn' => 'required|string|max:100',
                'inventor_name_en' => 'nullable|string|max:100',
                'inventor_type' => 'nullable|string|max:50',
                'gender' => 'nullable|in:男,女',
                'id_type' => 'nullable|string|max:50',
                'id_number' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:50',
                'province' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'district' => 'nullable|string|max:50',
                'street' => 'nullable|string|max:200',
                'postal_code' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'address_en' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:50',
                'landline' => 'nullable|string|max:50',
                'wechat' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'work_unit' => 'nullable|string|max:200',
                'department' => 'nullable|string|max:100',
                'position' => 'nullable|string|max:100',
                'business_staff_id' => 'nullable|exists:users,id',
                'remark' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 生成发明人编号和设置系统字段
            $data['inventor_code'] = $this->generateInventorCode(); // 自动生成唯一编号
            $data['created_by'] = auth()->id() ?? 1; // 创建人ID，默认为1
            $data['updated_by'] = auth()->id() ?? 1; // 更新人ID，默认为1
            $data['created_at'] = now(); // 创建时间
            $data['updated_at'] = now(); // 更新时间

            // 插入数据并获取新记录的ID
            $inventorId = DB::table('customer_inventors')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $inventorId]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新客户发明人信息
     * 
     * 根据发明人ID更新发明人信息，支持前端驼峰命名和数据库下划线命名的字段映射。
     * 自动设置更新人信息，过滤掉前端展示字段。
     * 
     * @param Request $request HTTP请求对象，包含以下参数：
     *   必填项：
     *   - customer_id: int 客户ID（必须存在于customers表）
     *   - inventor_name_cn|inventorName: string 发明人中文姓名（最大100字符）
     *   
     *   可选项：
     *   - inventor_name_en|inventorNameEn: string 发明人英文姓名（最大100字符）
     *   - inventor_type|inventorType: string 发明人类型（最大50字符）
     *   - gender: string 性别（男/女）
     *   - id_type|idType: string 证件类型（最大50字符）
     *   - id_number|idNumber: string 证件号码（最大100字符）
     *   - country: string 国家（最大50字符）
     *   - province: string 省份（最大50字符）
     *   - city: string 城市（最大50字符）
     *   - district: string 区县（最大50字符）
     *   - street: string 街道（最大200字符）
     *   - postal_code: string 邮政编码（最大20字符）
     *   - address: string 详细地址
     *   - address_en: string 英文地址（最大500字符）
     *   - phone: string 手机号码（最大50字符）
     *   - landline: string 座机号码（最大50字符）
     *   - wechat: string 微信号（最大50字符）
     *   - email: string 邮箱（必须是有效邮箱格式，最大100字符）
     *   - work_unit|workUnit: string 工作单位（最大200字符）
     *   - department: string 部门（最大100字符）
     *   - position: string 职位（最大100字符）
     *   - business_staff_id|businessStaffId: int 业务员ID（必须存在于users表）
     *   - remark: string 备注
     * 
     * @param int $id 发明人ID
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "更新成功"
     * }
     * 
     * 失败响应格式（发明人不存在）：
     * {
     *   "success": false,
     *   "message": "发明人不存在"
     * }
     * 
     * 失败响应格式（验证失败）：
     * {
     *   "success": false,
     *   "message": "验证失败",
     *   "errors": {
     *     "customer_id": ["客户ID字段是必需的"],
     *     "inventor_name_cn": ["发明人中文姓名字段是必需的"]
     *   }
     * }
     * 
     * 失败响应格式（其他错误）：
     * {
     *   "success": false,
     *   "message": "更新失败：错误信息"
     * }
     * 
     * @throws \Exception 当数据库操作失败时抛出异常
     */
    public function update(Request $request, $id)
    {
        try {
            $inventor = DB::table('customer_inventors')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$inventor) {
                return response()->json([
                    'success' => false,
                    'message' => '发明人不存在'
                ], 404);
            }

            // 数据预处理 - 在验证之前处理
            $data = $request->all();
            
            // 将空字符串转换为null（解决gender验证问题）
            if (isset($data['gender']) && $data['gender'] === '') {
                $data['gender'] = null;
            }
            
            // 字段映射（前端驼峰转数据库下划线）
            if (isset($data['inventorName'])) {
                $data['inventor_name_cn'] = $data['inventorName'];
                unset($data['inventorName']);
            }
            if (isset($data['inventorNameEn'])) {
                $data['inventor_name_en'] = $data['inventorNameEn'];
                unset($data['inventorNameEn']);
            }
            if (isset($data['inventorType'])) {
                $data['inventor_type'] = $data['inventorType'];
                unset($data['inventorType']);
            }
            if (isset($data['idType'])) {
                $data['id_type'] = $data['idType'];
                unset($data['idType']);
            }
            if (isset($data['idNumber'])) {
                $data['id_number'] = $data['idNumber'];
                unset($data['idNumber']);
            }
            if (isset($data['businessStaffId'])) {
                $data['business_staff_id'] = $data['businessStaffId'];
                unset($data['businessStaffId']);
            }
            if (isset($data['workUnit'])) {
                $data['work_unit'] = $data['workUnit'];
                unset($data['workUnit']);
            }

            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',
                'inventor_name_cn' => 'required|string|max:100',
                'inventor_name_en' => 'nullable|string|max:100',
                'inventor_type' => 'nullable|string|max:50',
                'gender' => 'nullable|in:男,女',
                'id_type' => 'nullable|string|max:50',
                'id_number' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:50',
                'province' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'district' => 'nullable|string|max:50',
                'street' => 'nullable|string|max:200',
                'postal_code' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'address_en' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:50',
                'landline' => 'nullable|string|max:50',
                'wechat' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'work_unit' => 'nullable|string|max:200',
                'department' => 'nullable|string|max:100',
                'position' => 'nullable|string|max:100',
                'business_staff_id' => 'nullable|exists:users,id',
                'remark' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 移除不应该更新的字段（前端展示字段和已处理的字段）
            // 这些字段是查询时生成的展示字段，不应该写入数据库
            unset($data['customerName'], $data['administrativeArea'], $data['addressEn'],
                  $data['createDate'], $data['createUser'], $data['createTime'],
                  $data['updateUser'], $data['updateTime']);
            
            // 设置更新相关字段
            $data['updated_by'] = auth()->id() ?? 1; // 更新人ID，默认为1
            $data['updated_at'] = now(); // 更新时间

            // 执行数据库更新操作
            DB::table('customer_inventors')->where('id', $id)->update($data);

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
     * 删除客户发明人
     * 
     * 根据发明人ID执行软删除操作，将deleted_at字段设置为当前时间。
     * 
     * @param int $id 发明人ID
     * 
     * @return \Illuminate\Http\JsonResponse JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "删除成功"
     * }
     * 
     * 失败响应格式（发明人不存在）：
     * {
     *   "success": false,
     *   "message": "发明人不存在"
     * }
     * 
     * 失败响应格式（其他错误）：
     * {
     *   "success": false,
     *   "message": "删除失败：错误信息"
     * }
     * 
     * @throws \Exception 当数据库操作失败时抛出异常
     */
    public function destroy($id)
    {
        try {
            // 查找指定ID的发明人记录（排除已删除的记录）
            $inventor = DB::table('customer_inventors')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$inventor) {
                return response()->json([
                    'success' => false,
                    'message' => '发明人不存在'
                ], 404);
            }

            // 执行软删除操作，设置deleted_at字段为当前时间
            DB::table('customer_inventors')->where('id', $id)->update([
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
     * 生成发明人编号
     * 
     * 自动生成唯一的发明人编号，格式为：INV + 日期(YYYYMMDD) + 4位序号
     * 例如：INV202401010001, INV202401010002
     * 
     * 编号生成规则：
     * 1. 前缀固定为 "INV"
     * 2. 日期部分为当前日期，格式为 YYYYMMDD
     * 3. 序号部分为4位数字，从0001开始递增
     * 4. 每天的序号从1开始重新计算
     * 
     * @return string 生成的发明人编号
     * 
     * @example
     * // 2024年1月1日创建的第一个发明人
     * $code = $this->generateInventorCode(); // 返回: "INV202401010001"
     * 
     * // 2024年1月1日创建的第二个发明人
     * $code = $this->generateInventorCode(); // 返回: "INV202401010002"
     */
    private function generateInventorCode()
    {
        // 定义编号前缀
        $prefix = 'INV';
        // 获取当前日期，格式：YYYYMMDD
        $date = date('Ymd');
        
        // 获取今日最大编号，查找以"INV+今日日期"开头的编号
        $maxCode = DB::table('customer_inventors')
            ->where('inventor_code', 'like', $prefix . $date . '%')
            ->orderBy('inventor_code', 'desc')
            ->value('inventor_code');
        
        if ($maxCode) {
            // 如果存在记录，取最后4位数字并加1
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            // 如果不存在记录，从1开始
            $number = 1;
        }
        
        // 返回完整编号：前缀 + 日期 + 4位序号（左侧补0）
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}