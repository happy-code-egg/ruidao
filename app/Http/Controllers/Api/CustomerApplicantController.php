<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerApplicantController extends Controller
{
    /**
     * 获取申请人列表
     */
    public function index(Request $request)
    {
        try {
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
                    DB::raw("CONCAT_WS('/', ca.province, ca.city, ca.district) as administrativeArea"),
                    'bs.real_name as businessStaff',
                    'ca.business_staff_id as businessStaffId',
                    'creator.real_name as createUser',
                    'ca.created_by as createUserId',
                    'ca.created_at as createTime',
                    'updater.real_name as updateUser',
                    'ca.updated_by as updateUserId',
                    'ca.updated_at as updateTime',
                    DB::raw("DATE(ca.created_at) as createDate")
                ])
                ->whereNull('ca.deleted_at');

            // 搜索条件
            // 重要：只显示指定客户的申请人
            if ($request->filled('customer_id')) {
                $query->where('ca.customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->where('c.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            if ($request->filled('applicant_name_cn')) {
                $query->where('ca.applicant_name_cn', 'like', '%' . $request->applicant_name_cn . '%');
            }

            if ($request->filled('applicant_name_en')) {
                $query->where('ca.applicant_name_en', 'like', '%' . $request->applicant_name_en . '%');
            }

            if ($request->filled('country')) {
                $query->where('ca.country', $request->country);
            }

            if ($request->filled('is_valid')) {
                $isValid = $request->is_valid == '1';
                if ($isValid) {
                    $query->where('ca.fee_reduction', true)
                          ->where('ca.fee_reduction_start_date', '<=', now())
                          ->where('ca.fee_reduction_end_date', '>=', now());
                } else {
                    $query->where(function($q) {
                        $q->where('ca.fee_reduction', false)
                          ->orWhere('ca.fee_reduction_start_date', '>', now())
                          ->orWhere('ca.fee_reduction_end_date', '<', now())
                          ->orWhereNull('ca.fee_reduction_start_date')
                          ->orWhereNull('ca.fee_reduction_end_date');
                    });
                }
            }

            if ($request->filled('valid_start_date')) {
                $query->where('ca.fee_reduction_start_date', '>=', $request->valid_start_date);
            }

            if ($request->filled('valid_end_date')) {
                $query->where('ca.fee_reduction_end_date', '<=', $request->valid_end_date);
            }

            if ($request->filled('sync_start_date')) {
                $query->where('ca.sync_date', '>=', $request->sync_start_date);
            }

            if ($request->filled('sync_end_date')) {
                $query->where('ca.sync_date', '<=', $request->sync_end_date);
            }

            if ($request->filled('province')) {
                $query->where('ca.province', $request->province);
            }

            if ($request->filled('city')) {
                $query->where('ca.city', $request->city);
            }

            if ($request->filled('district')) {
                $query->where('ca.district', $request->district);
            }

            if ($request->filled('applicant_type')) {
                $query->where('ca.applicant_type', $request->applicant_type);
            }

            if ($request->filled('entity_type')) {
                $query->where('ca.entity_type', $request->entity_type);
            }

            if ($request->filled('business_staff')) {
                $query->where('bs.real_name', 'like', '%' . $request->business_staff . '%');
            }

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $applicants = $query->orderBy('ca.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

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
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取申请人详情
     */
    public function show($id)
    {
        try {
            $applicant = DB::table('customer_applicants as ca')
                ->join('customers as c', 'ca.customer_id', '=', 'c.id')
                ->leftJoin('users as bs', 'ca.business_staff_id', '=', 'bs.id')
                ->leftJoin('users as creator', 'ca.created_by', '=', 'creator.id')
                ->leftJoin('users as updater', 'ca.updated_by', '=', 'updater.id')
                ->select([
                    'ca.*',
                    'c.customer_name as customerName',
                    'c.customer_code as customerCode',
                    'c.customer_no as customerNumber',
                    'bs.real_name as businessStaff',
                    'creator.real_name as createUser',
                    'updater.real_name as updateUser'
                ])
                ->where('ca.id', $id)
                ->whereNull('ca.deleted_at')
                ->first();

            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $applicant
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建申请人
     */
    public function store(Request $request)
    {
        try {
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

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 生成申请人编号
            $data['applicant_code'] = $this->generateApplicantCode();
            $data['created_by'] = auth()->id() ?? 1;
            $data['updated_by'] = auth()->id() ?? 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $applicantId = DB::table('customer_applicants')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $applicantId]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新申请人
     */
    public function update(Request $request, $id)
    {
        try {
            $applicant = DB::table('customer_applicants')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

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

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            // 移除不应该更新的字段
            unset($data['customerName'], $data['applicantName'], $data['applicantNameEn'],
                  $data['administrativeArea'], $data['createUser'], $data['createTime'],
                  $data['updateUser'], $data['updateTime'], $data['feeReductionStartDate'],
                  $data['feeReductionEndDate'], $data['id'], $data['applicant_code']);

            $data['updated_by'] = auth()->id() ?? 1;
            $data['updated_at'] = now();

            DB::table('customer_applicants')->where('id', $id)->update($data);

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
     * 删除申请人
     */
    public function destroy($id)
    {
        try {
            $applicant = DB::table('customer_applicants')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => '申请人不存在'
                ], 404);
            }

            DB::table('customer_applicants')->where('id', $id)->update([
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
     * 生成申请人编号
     */
    private function generateApplicantCode()
    {
        $prefix = 'APP';
        $date = date('Ymd');
        
        // 获取今日最大编号
        $maxCode = DB::table('customer_applicants')
            ->where('applicant_code', 'like', $prefix . $date . '%')
            ->orderBy('applicant_code', 'desc')
            ->value('applicant_code');
        
        if ($maxCode) {
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}