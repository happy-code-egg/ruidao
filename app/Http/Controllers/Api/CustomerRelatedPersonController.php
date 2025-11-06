<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerRelatedPerson;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CustomerRelatedPersonController extends Controller
{
   /**
 * 获取相关人员列表
 *
 * 功能描述：根据筛选条件获取客户相关人员列表，支持分页和多种搜索条件
 *
 * 传入参数：
 * - customer_id (int, 可选): 客户ID，用于精确筛选
 * - customer_name (string, 可选): 客户名称，用于模糊搜索
 * - person_name (string, 可选): 相关人员姓名，用于模糊搜索
 * - name (string, 可选): 相关人员姓名别名，用于模糊搜索
 * - person_type (string, 可选): 人员类型
 * - related_business_person_id (int, 可选): 关联业务人员ID
 * - phone (string, 可选): 联系电话，用于模糊搜索
 * - email (string, 可选): 邮箱，用于模糊搜索
 * - position (string, 可选): 职位，用于模糊搜索
 * - department (string, 可选): 部门，用于模糊搜索
 * - relationship (string, 可选): 关系类型
 * - is_active (int, 可选): 是否激活（0:否, 1:是）
 * - page_size (int, 可选, 默认10): 每页显示数量
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (object): 分页数据对象
 *   - list (array): 相关人员列表数据
 *     - id (int): 人员ID
 *     - customer_id (int): 客户ID
 *     - customer_name (string): 客户名称
 *     - customerName (string): 客户名称（别名）
 *     - person_name (string): 人员姓名
 *     - personName (string): 人员姓名（别名）
 *     - person_type (string): 人员类型
 *     - personType (string): 人员类型（别名）
 *     - phone (string): 联系电话
 *     - email (string): 邮箱
 *     - position (string): 职位
 *     - department (string): 部门
 *     - related_business_person (int): 关联业务人员ID
 *     - relatedBusinessPerson (int): 关联业务人员ID（别名）
 *     - related_business_person_name (string): 关联业务人员姓名
 *     - relatedBusinessPersonName (string): 关联业务人员姓名（别名）
 *     - relationship (string): 关系类型
 *     - responsibility (string): 职责
 *     - is_active (int): 是否激活
 *     - isActive (int): 是否激活（别名）
 *     - business_staff (string): 业务人员
 *     - businessStaff (string): 业务人员（别名）
 *     - create_date (string): 创建日期
 *     - createDate (string): 创建日期（别名）
 *     - remark (string): 备注
 *     - remarks (string): 备注（别名）
 *     - create_user (string): 创建人
 *     - createUser (string): 创建人（别名）
 *     - creator (string): 创建人（别名）
 *     - created_at (string): 创建时间
 *     - create_time (string): 创建时间（别名）
 *     - createTime (string): 创建时间（别名）
 *     - update_user (string): 更新人
 *     - updateUser (string): 更新人（别名）
 *     - updater (string): 更新人（别名）
 *     - updated_at (string): 更新时间
 *     - update_time (string): 更新时间（别名）
 *     - updateTime (string): 更新时间（别名）
 *   - total (int): 总记录数
 *   - per_page (int): 每页显示数量
 *   - current_page (int): 当前页码
 *   - last_page (int): 最后一页页码
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 */
public function index(Request $request)
{
    try {
        // 初始化查询构造器，预加载关联关系
        $query = CustomerRelatedPerson::with(['customer', 'creator', 'updater', 'relatedBusinessPerson']);

        // 搜索条件：根据客户ID精确查询
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 搜索条件：根据客户名称模糊查询
        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // 搜索条件：根据人员姓名模糊查询（支持person_name和name两个字段）
        if ($request->filled('person_name') || $request->filled('name')) {
            $name = $request->person_name ?: $request->name;
            $query->where('person_name', 'like', '%' . $name . '%');
        }

        // 搜索条件：根据人员类型筛选
        if ($request->filled('person_type')) {
            $query->where('person_type', $request->person_type);
        }

        // 搜索条件：根据关联业务人员ID筛选
        if ($request->filled('related_business_person_id')) {
            $query->where('related_business_person_id', $request->related_business_person_id);
        }

        // 搜索条件：根据联系电话模糊查询
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        // 搜索条件：根据邮箱模糊查询
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // 搜索条件：根据职位模糊查询
        if ($request->filled('position')) {
            $query->where('position', 'like', '%' . $request->position . '%');
        }

        // 搜索条件：根据部门模糊查询
        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }

        // 搜索条件：根据关系类型筛选
        if ($request->filled('relationship')) {
            $query->where('relationship', $request->relationship);
        }

        // 搜索条件：根据是否激活状态筛选
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // 分页处理：获取每页数量，默认10条
        $pageSize = $request->get('page_size', 10);
        // 执行查询并分页，按ID倒序排列
        $persons = $query->orderBy('id', 'desc')->paginate($pageSize);

        // 格式化数据：转换返回数据结构以适配前端需求
        $persons->getCollection()->transform(function ($person) {
            return [
                'id' => $person->id,
                'customer_id' => $person->customer_id,
                'customer_name' => $person->customer->customer_name ?? '',
                'customerName' => $person->customer->customer_name ?? '',
                'person_name' => $person->person_name,
                'personName' => $person->person_name,
                'person_type' => $person->person_type,
                'personType' => $person->person_type,
                'phone' => $person->phone,
                'email' => $person->email,
                'position' => $person->position,
                'department' => $person->department,
                'related_business_person' => $person->related_business_person_id,
                'relatedBusinessPerson' => $person->related_business_person_id,
                'related_business_person_name' => $person->relatedBusinessPerson->real_name ?? '',
                'relatedBusinessPersonName' => $person->relatedBusinessPerson->real_name ?? '',
                'relationship' => $person->relationship,
                'responsibility' => $person->responsibility,
                'is_active' => $person->is_active,
                'isActive' => $person->is_active,
                'business_staff' => $person->customer->businessPerson->name ?? '',
                'businessStaff' => $person->customer->businessPerson->name ?? '',
                'create_date' => $person->created_at ? $person->created_at->format('Y-m-d') : '',
                'createDate' => $person->created_at ? $person->created_at->format('Y-m-d') : '',
                'remark' => $person->remark,
                'remarks' => $person->remark,
                'create_user' => $person->creator->real_name ?? '',
                'createUser' => $person->creator->real_name ?? '',
                'creator' => $person->creator->real_name ?? '',
                'created_at' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                'create_time' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                'createTime' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                'update_user' => $person->updater->real_name ?? '',
                'updateUser' => $person->updater->real_name ?? '',
                'updater' => $person->updater->real_name ?? '',
                'updated_at' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
                'update_time' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
                'updateTime' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
            ];
        });

        // 返回成功响应，包含列表数据和分页信息
        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'list' => $persons->items(),
                'total' => $persons->total(),
                'per_page' => $persons->perPage(),
                'current_page' => $persons->currentPage(),
                'last_page' => $persons->lastPage(),
            ]
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '获取失败：' . $e->getMessage()
        ], 500);
    }
}
/**
 * 创建相关人员
 *
 * 功能描述：创建一个新的客户相关人员记录
 *
 * 传入参数：
 * - customer_id (int, 必填): 客户ID，必须存在于 `customers` 表中
 * - person_name (string, 必填): 相关人员姓名，最大100个字符
 * - person_type (string, 必填): 人员类型，最大50个字符
 * - related_business_person_id (int, 可选): 关联业务人员ID，必须存在于 `users` 表中
 * - name (string, 可选): 人员姓名别名（会映射到 person_name）
 * - personType (string, 可选): 人员类型别名（会映射到 person_type）
 * - remarks (string, 可选): 备注别名（会映射到 remark）
 * - related_business_person (int, 可选): 关联业务人员别名（会映射到 related_business_person_id）
 * - phone (string, 可选): 联系电话，最大50个字符
 * - email (string, 可选): 邮箱，必须是有效的邮箱格式，最大100个字符
 * - position (string, 可选): 职位，最大100个字符
 * - department (string, 可选): 部门，最大100个字符
 * - relationship (string, 可选): 关系类型，最大100个字符
 * - responsibility (string, 可选): 职责
 * - remark (string, 可选): 备注
 * - is_active (boolean, 可选): 是否激活
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (object): 创建成功的相关人员对象
 *   - id (int): 人员ID
 *   - customer_id (int): 客户ID
 *   - person_name (string): 人员姓名
 *   - person_type (string): 人员类型
 *   - related_business_person_id (int): 关联业务人员ID
 *   - phone (string): 联系电话
 *   - email (string): 邮箱
 *   - position (string): 职位
 *   - department (string): 部门
 *   - relationship (string): 关系类型
 *   - responsibility (string): 职责
 *   - remark (string): 备注
 *   - is_active (boolean): 是否激活
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 * - errors (object, 可选): 验证错误详情（HTTP状态码422）
 */
public function store(Request $request)
{
    try {
        // 数据预处理 - 前端字段映射：处理前端可能发送的不同字段名
        $data = $request->all();

        // 字段映射：前端可能发送name而不是person_name
        if (isset($data['name']) && !isset($data['person_name'])) {
            $data['person_name'] = $data['name'];
            unset($data['name']);
        }

        // 字段映射：前端可能发送personType而不是person_type
        if (isset($data['personType']) && !isset($data['person_type'])) {
            $data['person_type'] = $data['personType'];
            unset($data['personType']);
        }

        // 字段映射：前端可能发送remarks而不是remark
        if (isset($data['remarks']) && !isset($data['remark'])) {
            $data['remark'] = $data['remarks'];
            unset($data['remarks']);
        }

        // 字段映射：前端可能发送related_business_person而不是related_business_person_id
        if (isset($data['related_business_person']) && !isset($data['related_business_person_id'])) {
            $data['related_business_person_id'] = $data['related_business_person'];
            unset($data['related_business_person']);
        }

        // 验证请求数据的合法性
        $validator = Validator::make($data, [
            'customer_id' => 'required|integer|exists:customers,id',        // 客户ID必填且必须存在
            'person_name' => 'required|string|max:100',                     // 人员姓名必填且不超过100字符
            'person_type' => 'required|string|max:50',                      // 人员类型必填且不超过50字符
            'related_business_person_id' => 'nullable|integer|exists:users,id', // 关联业务人员ID可为空但必须存在
            'phone' => 'nullable|string|max:50',                           // 电话可为空且不超过50字符
            'email' => 'nullable|email|max:100',                           // 邮箱可为空且必须是有效邮箱格式
            'position' => 'nullable|string|max:100',                       // 职位可为空且不超过100字符
            'department' => 'nullable|string|max:100',                     // 部门可为空且不超过100字符
            'relationship' => 'nullable|string|max:100',                   // 关系类型可为空且不超过100字符
            'responsibility' => 'nullable|string',                         // 职责可为空
            'remark' => 'nullable|string',                                 // 备注可为空
            'is_active' => 'nullable|boolean',                             // 是否激活可为空且必须是布尔值
        ]);

        // 如果验证失败，返回验证错误信息
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        // 设置创建人和更新人信息
        $data['created_by'] = auth()->id();  // 设置创建人为当前认证用户
        $data['updated_by'] = auth()->id();  // 设置更新人为当前认证用户

        // 创建新的相关人员记录
        $person = CustomerRelatedPerson::create($data);

        // 返回成功响应及创建的数据
        return response()->json([
            'success' => true,
            'message' => '创建成功',
            'data' => $person
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '创建失败：' . $e->getMessage()
        ], 500);
    }
}


    /**
 * 获取相关人员详情
 *
 * 功能描述：根据ID获取指定客户相关人员的详细信息
 *
 * 传入参数：
 * - id (int, 路径参数): 客户相关人员ID
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (object): 客户相关人员详情对象
 *   - id (int): 人员ID
 *   - customer_id (int): 客户ID
 *   - person_name (string): 人员姓名
 *   - person_type (string): 人员类型
 *   - related_business_person_id (int): 关联业务人员ID
 *   - phone (string): 联系电话
 *   - email (string): 邮箱
 *   - position (string): 职位
 *   - department (string): 部门
 *   - relationship (string): 关系类型
 *   - responsibility (string): 职责
 *   - remark (string): 备注
 *   - is_active (boolean): 是否激活
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *   - customer (object): 关联的客户信息
 *   - creator (object): 创建人信息
 *   - updater (object): 更新人信息
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息（HTTP状态码404）
 */
public function show($id)
{
    try {
        // 根据ID查询客户相关人员，并关联加载客户、创建人和更新人信息
        $person = CustomerRelatedPerson::with(['customer', 'creator', 'updater'])->findOrFail($id);

        // 返回成功响应及详情数据
        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $person
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '获取失败：' . $e->getMessage()
        ], 404);
    }
}


   /**
 * 更新相关人员
 *
 * 功能描述：根据ID更新指定客户相关人员的信息
 *
 * 传入参数：
 * - id (int, 路径参数): 客户相关人员ID
 * - customer_id (int, 必填): 客户ID，必须存在于 `customers` 表中
 * - person_name (string, 必填): 相关人员姓名，最大100个字符
 * - person_type (string, 必填): 人员类型，最大50个字符
 * - related_business_person_id (int, 可选): 关联业务人员ID，必须存在于 `users` 表中
 * - name (string, 可选): 人员姓名别名（会映射到 person_name）
 * - personType (string, 可选): 人员类型别名（会映射到 person_type）
 * - remarks (string, 可选): 备注别名（会映射到 remark）
 * - related_business_person (int, 可选): 关联业务人员别名（会映射到 related_business_person_id）
 * - phone (string, 可选): 联系电话，最大50个字符
 * - email (string, 可选): 邮箱，必须是有效的邮箱格式，最大100个字符
 * - position (string, 可选): 职位，最大100个字符
 * - department (string, 可选): 部门，最大100个字符
 * - relationship (string, 可选): 关系类型，最大100个字符
 * - responsibility (string, 可选): 职责
 * - remark (string, 可选): 备注
 * - is_active (boolean, 可选): 是否激活
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (object): 更新后的相关人员对象
 *   - id (int): 人员ID
 *   - customer_id (int): 客户ID
 *   - person_name (string): 人员姓名
 *   - person_type (string): 人员类型
 *   - related_business_person_id (int): 关联业务人员ID
 *   - phone (string): 联系电话
 *   - email (string): 邮箱
 *   - position (string): 职位
 *   - department (string): 部门
 *   - relationship (string): 关系类型
 *   - responsibility (string): 职责
 *   - remark (string): 备注
 *   - is_active (boolean): 是否激活
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - created_at (datetime): 创建时间
 *   - updated_at (datetime): 更新时间
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 * - errors (object, 可选): 验证错误详情（HTTP状态码422）
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找客户相关人员记录
        $person = CustomerRelatedPerson::findOrFail($id);

        // 数据预处理 - 前端字段映射：处理前端可能发送的不同字段名
        $data = $request->all();

        // 字段映射：前端可能发送name而不是person_name
        if (isset($data['name']) && !isset($data['person_name'])) {
            $data['person_name'] = $data['name'];
            unset($data['name']);
        }

        // 字段映射：前端可能发送personType而不是person_type
        if (isset($data['personType']) && !isset($data['person_type'])) {
            $data['person_type'] = $data['personType'];
            unset($data['personType']);
        }

        // 字段映射：前端可能发送remarks而不是remark
        if (isset($data['remarks']) && !isset($data['remark'])) {
            $data['remark'] = $data['remarks'];
            unset($data['remarks']);
        }

        // 字段映射：前端可能发送related_business_person而不是related_business_person_id
        if (isset($data['related_business_person']) && !isset($data['related_business_person_id'])) {
            $data['related_business_person_id'] = $data['related_business_person'];
            unset($data['related_business_person']);
        }

        // 验证请求数据的合法性
        $validator = Validator::make($data, [
            'customer_id' => 'required|integer|exists:customers,id',        // 客户ID必填且必须存在
            'person_name' => 'required|string|max:100',                     // 人员姓名必填且不超过100字符
            'person_type' => 'required|string|max:50',                      // 人员类型必填且不超过50字符
            'related_business_person_id' => 'nullable|integer|exists:users,id', // 关联业务人员ID可为空但必须存在
            'phone' => 'nullable|string|max:50',                           // 电话可为空且不超过50字符
            'email' => 'nullable|email|max:100',                           // 邮箱可为空且必须是有效邮箱格式
            'position' => 'nullable|string|max:100',                       // 职位可为空且不超过100字符
            'department' => 'nullable|string|max:100',                     // 部门可为空且不超过100字符
            'relationship' => 'nullable|string|max:100',                   // 关系类型可为空且不超过100字符
            'responsibility' => 'nullable|string',                         // 职责可为空
            'remark' => 'nullable|string',                                 // 备注可为空
            'is_active' => 'nullable|boolean',                             // 是否激活可为空且必须是布尔值
        ]);

        // 如果验证失败，返回验证错误信息
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        // 设置更新人信息
        $data['updated_by'] = Auth::id();  // 设置更新人为当前认证用户

        // 更新客户相关人员记录
        $person->update($data);

        // 返回成功响应及更新后的数据
        return response()->json([
            'success' => true,
            'message' => '更新成功',
            'data' => $person
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '更新失败：' . $e->getMessage()
        ], 500);
    }
}

   /**
 * 删除相关人员
 *
 * 功能描述：根据ID删除指定的客户相关人员记录
 *
 * 传入参数：
 * - id (int, 路径参数): 客户相关人员ID
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息（HTTP状态码500）
 */
public function destroy($id)
{
    try {
        // 根据ID查找客户相关人员记录
        $person = CustomerRelatedPerson::findOrFail($id);
        // 执行删除操作
        $person->delete();

        // 返回成功响应
        return response()->json([
            'success' => true,
            'message' => '删除成功'
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '删除失败：' . $e->getMessage()
        ], 500);
    }
}


   /**
 * 获取人员类型列表
 *
 * 功能描述：获取客户相关人员的人员类型选项列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (array): 人员类型选项列表
 *   - value (string): 类型值
 *   - label (string): 类型标签
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息（HTTP状态码500）
 */
public function getPersonTypes()
{
    // 定义人员类型选项数组
    $types = [
        ['value' => CustomerRelatedPerson::TYPE_TECH_LEADER, 'label' => '技术负责人'],
        ['value' => CustomerRelatedPerson::TYPE_BUSINESS_LEADER, 'label' => '商务负责人'],
        ['value' => CustomerRelatedPerson::TYPE_FINANCE_LEADER, 'label' => '财务负责人'],
        ['value' => CustomerRelatedPerson::TYPE_PROJECT_MANAGER, 'label' => '项目负责人'],
        ['value' => CustomerRelatedPerson::TYPE_BUSINESS_ASSISTANT, 'label' => '业务助理'],
        ['value' => CustomerRelatedPerson::TYPE_BUSINESS_COLLABORATOR, 'label' => '业务协作人'],
        ['value' => CustomerRelatedPerson::TYPE_OTHER, 'label' => '其他'],
    ];

    // 返回成功响应及人员类型数据
    return response()->json([
        'success' => true,
        'message' => '获取成功',
        'data' => $types
    ]);
}


    /**
 * 获取客户的业务人员列表
 *
 * 功能描述：根据客户ID获取该客户关联的业务人员、业务助理和业务协作人信息
 *
 * 传入参数：
 * - customer_id (int, 必填): 客户ID
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (array): 业务人员列表
 *   - id (int): 用户ID
 *   - name (string): 用户姓名
 *   - department (string): 所属部门
 *   - phone (string): 联系电话
 *   - email (string): 邮箱
 *   - type (string): 人员类型（业务人员/业务助理/业务协作人）
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 * - errors (object, 可选): 验证错误详情（HTTP状态码400/404/500）
 */
public function getCustomerBusinessPersons(Request $request)
{
    try {
        // 获取并验证客户ID参数
        $customerId = $request->get('customer_id');
        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => '客户ID不能为空'
            ], 400);
        }

        // 获取客户信息，预加载业务人员、业务助理和业务协作人关联关系
        $customer = \App\Models\Customer::with([
            'businessPerson',
            'businessAssistant',
            'businessPartner'
        ])->find($customerId);

        // 如果客户不存在，返回404错误
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => '客户不存在'
            ], 404);
        }

        // 初始化业务人员数组
        $businessPersons = [];

        // 添加业务人员信息
        if ($customer->businessPerson) {
            $businessPersons[] = [
                'id' => $customer->businessPerson->id,
                'name' => $customer->businessPerson->real_name,
                'department' => $customer->businessPerson->department->name ?? '',
                'phone' => $customer->businessPerson->phone,
                'email' => $customer->businessPerson->email,
                'type' => '业务人员'
            ];
        }

        // 添加业务助理信息
        if ($customer->businessAssistant) {
            $businessPersons[] = [
                'id' => $customer->businessAssistant->id,
                'name' => $customer->businessAssistant->real_name,
                'department' => $customer->businessAssistant->department->name ?? '',
                'phone' => $customer->businessAssistant->phone,
                'email' => $customer->businessAssistant->email,
                'type' => '业务助理'
            ];
        }

        // 添加业务协作人信息
        if ($customer->businessPartner) {
            $businessPersons[] = [
                'id' => $customer->businessPartner->id,
                'name' => $customer->businessPartner->real_name,
                'department' => $customer->businessPartner->department->name ?? '',
                'phone' => $customer->businessPartner->phone,
                'email' => $customer->businessPartner->email,
                'type' => '业务协作人'
            ];
        }

        // 返回成功响应及业务人员数据
        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $businessPersons
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '获取失败：' . $e->getMessage()
        ], 500);
    }
}


   /**
 * 搜索用户（用于添加助理/协作人）
 *
 * 功能描述：根据关键词搜索系统中的启用用户，用于添加业务助理或业务协作人
 *
 * 传入参数：
 * - keyword (string, 可选): 搜索关键词，默认为空
 * - limit (int, 可选): 返回结果数量限制，默认20条
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 * - data (array): 搜索到的用户列表
 *   - id (int): 用户ID
 *   - name (string): 用户姓名或用户名
 *   - username (string): 用户名
 *   - real_name (string): 真实姓名
 *   - department (string): 所属部门
 *   - phone (string): 联系电话
 *   - email (string): 邮箱
 *   - position (string): 职位
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息（HTTP状态码500）
 */
public function searchUsers(Request $request)
{
    try {
        // 获取搜索参数：关键词和结果数量限制
        $keyword = $request->get('keyword', '');
        $limit = $request->get('limit', 20);

        // 初始化查询构造器，只查询启用状态的用户
        $query = \App\Models\User::where('status', 1);

        // 如果提供了关键词，则按姓名、用户名、电话或邮箱进行模糊搜索
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('real_name', 'like', '%' . $keyword . '%')
                  ->orWhere('username', 'like', '%' . $keyword . '%')
                  ->orWhere('phone', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        // 执行查询，预加载部门信息，限制结果数量
        $users = $query->with('department')
                      ->limit($limit)
                      ->get()
                      // 格式化返回数据结构
                      ->map(function ($user) {
                          return [
                              'id' => $user->id,
                              'name' => $user->real_name ?: $user->username,
                              'username' => $user->username,
                              'real_name' => $user->real_name,
                              'department' => $user->department->department_name ?? '',
                              'phone' => $user->phone,
                              'email' => $user->email,
                              'position' => $user->position
                          ];
                      });

        // 返回成功响应及搜索结果
        return response()->json([
            'success' => true,
            'message' => '搜索成功',
            'data' => $users
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '搜索失败：' . $e->getMessage()
        ], 500);
    }
}


  /**
 * 添加客户业务员
 *
 * 功能描述：为客户添加一个或多个业务员，如果业务员已存在则跳过
 *
 * 传入参数：
 * - customer_id (int, 必填): 客户ID，必须存在于 `customers` 表中
 * - business_person_ids (array, 必填): 业务员用户ID数组，每个ID必须存在于 `users` 表中
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 * - errors (object, 可选): 验证错误详情（HTTP状态码422）
 */
public function addCustomerBusinessPerson(Request $request)
{
    try {
        // 验证请求数据的合法性
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',     // 客户ID必填且必须存在
            'business_person_ids' => 'required|array',                  // 业务员ID数组必填
            'business_person_ids.*' => 'integer|exists:users,id',       // 数组中每个ID必须是整数且存在于用户表
        ]);

        // 如果验证失败，返回验证错误信息
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        // 获取请求参数
        $customerId = $request->customer_id;
        $businessPersonIds = $request->business_person_ids;

        // 遍历业务员ID数组，逐个添加业务员
        foreach ($businessPersonIds as $personId) {
            // 检查该业务员是否已关联到该客户
            $exists = CustomerRelatedPerson::where('customer_id', $customerId)
                ->where('related_business_person_id', $personId)
                ->where('person_type', '业务员')
                ->exists();

            // 如果不存在，则创建新的关联记录
            if (!$exists) {
                // 获取用户信息，包含部门
                $user = \App\Models\User::with('department')->find($personId);
                if ($user) {
                    // 创建客户相关人员记录
                    CustomerRelatedPerson::create([
                        'customer_id' => $customerId,
                        'related_business_person_id' => $personId,
                        'person_name' => $user->real_name,
                        'person_type' => '业务员',
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'department' => $user->department->department_name ?? '',
                        'is_active' => true,
                        'created_by' => Auth::id(),  // 设置创建人为当前认证用户
                        'updated_by' => Auth::id(),  // 设置更新人为当前认证用户
                    ]);
                }
            }
        }

        // 返回成功响应
        return response()->json([
            'success' => true,
            'message' => '添加业务员成功'
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '添加业务员失败：' . $e->getMessage()
        ], 500);
    }
}


   /**
 * 移除客户业务员
 *
 * 功能描述：从客户关联人员中移除指定的业务员，同时删除该业务员关联的助理和协作人
 *
 * 传入参数：
 * - customer_id (int, 必填): 客户ID，必须存在于 `customers` 表中
 * - business_person_id (int, 必填): 业务员用户ID，必须存在于 `users` 表中
 *
 * 输出参数：
 * - success (boolean): 操作是否成功
 * - message (string): 操作结果消息
 *
 * 错误响应：
 * - success (boolean): false
 * - message (string): 错误信息
 * - errors (object, 可选): 验证错误详情（HTTP状态码422）
 */
public function removeCustomerBusinessPerson(Request $request)
{
    try {
        // 验证请求数据的合法性
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',    // 客户ID必填且必须存在
            'business_person_id' => 'required|integer|exists:users,id', // 业务员ID必填且必须存在
        ]);

        // 如果验证失败，返回验证错误信息
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        // 获取请求参数
        $customerId = $request->customer_id;
        $businessPersonId = $request->business_person_id;

        // 删除业务员记录：删除该客户下指定业务员的业务员类型记录
        CustomerRelatedPerson::where('customer_id', $customerId)
            ->where('related_business_person_id', $businessPersonId)
            ->where('person_type', '业务员')
            ->delete();

        // 同时删除该业务员的助理和协作人：删除该客户下指定业务员的助理和协作人类型记录
        CustomerRelatedPerson::where('customer_id', $customerId)
            ->where('related_business_person_id', $businessPersonId)
            ->whereIn('person_type', ['业务助理', '业务协作人'])
            ->delete();

        // 返回成功响应
        return response()->json([
            'success' => true,
            'message' => '移除业务员成功'
        ]);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应和错误信息
        return response()->json([
            'success' => false,
            'message' => '移除业务员失败：' . $e->getMessage()
        ], 500);
    }
}
}
