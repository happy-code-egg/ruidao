<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerInventorController extends Controller
{
    /**
     * 获取发明人列表
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
     * 获取发明人详情
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
     * 创建发明人
     */
    public function store(Request $request)
    {
        try {
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

            // 生成发明人编号
            $data['inventor_code'] = $this->generateInventorCode();
            $data['created_by'] = auth()->id() ?? 1;
            $data['updated_by'] = auth()->id() ?? 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

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
     * 更新发明人
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
            unset($data['customerName'], $data['administrativeArea'], $data['addressEn'],
                  $data['createDate'], $data['createUser'], $data['createTime'],
                  $data['updateUser'], $data['updateTime']);
            
            $data['updated_by'] = auth()->id() ?? 1;
            $data['updated_at'] = now();

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
     * 删除发明人
     */
    public function destroy($id)
    {
        try {
            $inventor = DB::table('customer_inventors')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$inventor) {
                return response()->json([
                    'success' => false,
                    'message' => '发明人不存在'
                ], 404);
            }

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
     */
    private function generateInventorCode()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        
        // 获取今日最大编号
        $maxCode = DB::table('customer_inventors')
            ->where('inventor_code', 'like', $prefix . $date . '%')
            ->orderBy('inventor_code', 'desc')
            ->value('inventor_code');
        
        if ($maxCode) {
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}