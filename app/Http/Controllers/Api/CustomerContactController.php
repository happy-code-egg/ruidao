<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerContactController extends Controller
{
    /**
     * 获取联系人列表
     */
    public function index(Request $request)
    {
        try {
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
                ->whereNull('cc.deleted_at');

            // 搜索条件
            // 重要：只显示指定客户的联系人
            if ($request->filled('customer_id')) {
                $query->where('cc.customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->where('c.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            if ($request->filled('name')) {
                $query->where('cc.contact_name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('contact_type')) {
                $query->where('cc.contact_type_text', $request->contact_type);
            }

            if ($request->filled('phone')) {
                $query->where('cc.phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->filled('is_on_job')) {
                $query->where('cc.is_on_job', $request->is_on_job == 'true' || $request->is_on_job == 1);
            }

            if ($request->filled('department')) {
                $query->where('cc.department', 'like', '%' . $request->department . '%');
            }

            if ($request->filled('business_staff')) {
                // 通过业务人员姓名搜索
                $query->where('bu.real_name', 'like', '%' . $request->business_staff . '%');
            }

            if ($request->filled('business_staff_id')) {
                // 通过业务人员ID搜索
                $query->where('cc.business_staff_id', $request->business_staff_id);
            }

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $contacts = $query->orderBy('cc.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

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
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取联系人详情
     */
    public function show($id)
    {
        try {
            $contact = DB::table('customer_contacts as cc')
                ->join('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as bu', 'cc.business_staff_id', '=', 'bu.id')
                ->leftJoin('users as cu', 'cc.created_by', '=', 'cu.id')
                ->leftJoin('users as uu', 'cc.updated_by', '=', 'uu.id')
                ->select([
                    'cc.*',
                    'c.customer_name as customerName',
                    'bu.real_name as business_staff_name',
                    'cu.real_name as create_user_name',
                    'uu.real_name as update_user_name'
                ])
                ->where('cc.id', $id)
                ->whereNull('cc.deleted_at')
                ->first();

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => '联系人不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contact
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建联系人
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
            
            // 字段映射处理
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
            
            // 处理布尔值字段
            if (isset($data['isOnJob'])) {
                if ($data['isOnJob'] === '是' || $data['isOnJob'] === 'true' || $data['isOnJob'] === true || $data['isOnJob'] === 1 || $data['isOnJob'] === '1') {
                    $data['is_on_job'] = true;
                } else {
                    $data['is_on_job'] = false;
                }
                unset($data['isOnJob']);
            }
            
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

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data['created_by'] = auth()->id() ?? 1;
            $data['updated_by'] = auth()->id() ?? 1;
            $data['status'] = 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $contactId = DB::table('customer_contacts')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $contactId]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新联系人
     */
    public function update(Request $request, $id)
    {
        try {
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
            
            // 字段映射处理
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
            
            // 处理布尔值字段
            if (isset($data['isOnJob'])) {
                if ($data['isOnJob'] === '是' || $data['isOnJob'] === 'true' || $data['isOnJob'] === true || $data['isOnJob'] === 1 || $data['isOnJob'] === '1') {
                    $data['is_on_job'] = true;
                } else {
                    $data['is_on_job'] = false;
                }
                unset($data['isOnJob']);
            }

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

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 移除不应该更新的字段（前端展示字段和不存在的数据库字段）
            unset($data['customerName'], $data['businessStaff'], $data['createUser'], $data['createTime'], $data['updateUser'], $data['updateTime']);
            
            $data['updated_by'] = auth()->id() ?? 1;
            $data['updated_at'] = now();

            DB::table('customer_contacts')->where('id', $id)->update($data);

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
     * 删除联系人
     */
    public function destroy($id)
    {
        try {
            $contact = DB::table('customer_contacts')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => '联系人不存在'
                ], 404);
            }

            DB::table('customer_contacts')->where('id', $id)->update([
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
     * 获取客户选项列表
     */
    public function getCustomerOptions()
    {
        try {
            $customers = DB::table('customers')
                ->select(['id as value', 'customer_name as label'])
                ->whereNull('deleted_at')
                ->orderBy('customer_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $customers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }
}