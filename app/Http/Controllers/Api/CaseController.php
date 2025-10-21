<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CaseController extends Controller
{
    /**
     * 获取案例列表
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('cases as c')
                ->join('customers as cu', 'c.customer_id', '=', 'cu.id')
                ->leftJoin('products as p', 'c.product_id', '=', 'p.id')
                ->select([
                    'c.id',
                    'c.case_code',
                    'c.case_name',
                    'c.customer_id',
                    'c.contract_id',
                    'cu.customer_name as customerName',
                    'c.case_type',
                    'c.case_subtype',
                    'c.application_type',
                    'c.case_status',
                    'c.case_phase',
                    'c.priority_level',
                    'c.application_no',
                    'c.application_date',
                    'c.registration_no',
                    'c.registration_date',
                    'c.country_code',
                    'c.deadline_date',
                    'c.estimated_cost',
                    'c.actual_cost',
                    'c.service_fee',
                    'c.official_fee',
                    'c.case_description',
                    'c.technical_field',
                    'c.innovation_points',
                    'c.remarks',
                    'c.product_id',
                    'p.product_name',
                    'p.specification as product_specification',
                    'p.product_code',
                    'c.created_by as createUser',
                    'c.created_at as createTime',
                    'c.updated_by as updateUser',
                    'c.updated_at as updateTime'
                ])
                ->whereNull('c.deleted_at');

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('c.customer_id', $request->customer_id);
            }

            if ($request->filled('contract_id')) {
                $query->where('c.contract_id', $request->contract_id);
            }

            if ($request->filled('customer_name')) {
                $query->where('cu.customer_name', 'like', '%' . $request->customer_name . '%');
            }

            if ($request->filled('case_name')) {
                $query->where('c.case_name', 'like', '%' . $request->case_name . '%');
            }

            if ($request->filled('case_code')) {
                $query->where('c.case_code', 'like', '%' . $request->case_code . '%');
            }

            if ($request->filled('case_type')) {
                $query->where('c.case_type', $request->case_type);
            }

            if ($request->filled('case_status')) {
                $query->where('c.case_status', $request->case_status);
            }

            if ($request->filled('application_no')) {
                $query->where('c.application_no', 'like', '%' . $request->application_no . '%');
            }

            if ($request->filled('registration_no')) {
                $query->where('c.registration_no', 'like', '%' . $request->registration_no . '%');
            }

            if ($request->filled('country_code')) {
                $query->where('c.country_code', $request->country_code);
            }

            if ($request->filled('application_date_start')) {
                $query->where('c.application_date', '>=', $request->application_date_start);
            }

            if ($request->filled('application_date_end')) {
                $query->where('c.application_date', '<=', $request->application_date_end);
            }

            $perPage = $request->input('page_size', 10);
            $page = $request->input('page', 1);
            
            $total = $query->count();
            $cases = $query->orderBy('c.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $cases,
                    'total' => $total,
                    'page_size' => $perPage,
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
     * 获取案例详情
     */
    public function show($id)
    {
        try {
            $case = DB::table('cases as c')
                ->join('customers as cu', 'c.customer_id', '=', 'cu.id')
                ->leftJoin('products as p', 'c.product_id', '=', 'p.id')
                ->select([
                    'c.*',
                    'cu.customer_name as customerName',
                    'p.product_name',
                    'p.specification as product_specification',
                    'p.product_code'
                ])
                ->where('c.id', $id)
                ->whereNull('c.deleted_at')
                ->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $case
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建案例
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'case_name' => 'required|string|max:200',
                'case_type' => 'required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'case_status' => 'nullable|integer',
                'case_phase' => 'nullable|string|max:50',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'application_no' => 'nullable|string|max:100',
                'application_date' => 'nullable|date',
                'registration_no' => 'nullable|string|max:100',
                'registration_date' => 'nullable|date',
                'acceptance_no' => 'nullable|string|max:100',
                'country_code' => 'nullable|string|max:10',
                'entity_type' => 'nullable|integer|in:1,2,3',
                'presale_support' => 'nullable|integer',
                'tech_leader' => 'nullable|integer',
                'tech_contact' => 'nullable|integer',
                'is_authorized' => 'nullable|integer|in:0,1',
                'tech_service_name' => 'nullable|string|max:200',
                'trademark_category' => 'nullable|string|max:50',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'agent_id' => 'nullable|integer',
                'assistant_id' => 'nullable|integer',
                'agency_id' => 'nullable|integer',
                'deadline_date' => 'nullable|date',
                'annual_fee_due_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric',
                'actual_cost' => 'nullable|numeric',
                'service_fee' => 'nullable|numeric',
                'official_fee' => 'nullable|numeric',
                'case_description' => 'nullable|string',
                'technical_field' => 'nullable|string',
                'innovation_points' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 转换状态字符串为数值
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            // 生成案例编号
            $data['case_code'] = $this->generateCaseCode($data['case_type']);
            $data['created_by'] = auth()->id() ?? 1;
            $data['updated_by'] = auth()->id() ?? 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $caseId = DB::table('cases')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => ['id' => $caseId]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新案例
     */
    public function update(Request $request, $id)
    {
        try {
            $case = DB::table('cases')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'case_name' => 'required|string|max:200',
                'case_type' => 'required|integer|in:1,2,3,4',
                'case_subtype' => 'nullable|string|max:50',
                'application_type' => 'nullable|string|max:50',
                'case_status' => 'nullable|integer',
                'case_phase' => 'nullable|string|max:50',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'application_no' => 'nullable|string|max:100',
                'application_date' => 'nullable|date',
                'registration_no' => 'nullable|string|max:100',
                'registration_date' => 'nullable|date',
                'acceptance_no' => 'nullable|string|max:100',
                'country_code' => 'nullable|string|max:10',
                'entity_type' => 'nullable|integer|in:1,2,3',
                'presale_support' => 'nullable|integer',
                'tech_leader' => 'nullable|integer',
                'tech_contact' => 'nullable|integer',
                'is_authorized' => 'nullable|integer|in:0,1',
                'tech_service_name' => 'nullable|string|max:200',
                'trademark_category' => 'nullable|string|max:50',
                'business_person_id' => 'nullable|integer|exists:users,id',
                'agent_id' => 'nullable|integer',
                'assistant_id' => 'nullable|integer',
                'agency_id' => 'nullable|integer',
                'deadline_date' => 'nullable|date',
                'annual_fee_due_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric',
                'actual_cost' => 'nullable|numeric',
                'service_fee' => 'nullable|numeric',
                'official_fee' => 'nullable|numeric',
                'case_description' => 'nullable|string',
                'technical_field' => 'nullable|string',
                'innovation_points' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // 转换状态字符串为数值
            if (isset($data['case_status']) && is_string($data['case_status'])) {
                $data['case_status'] = $this->convertStatusToInt($data['case_status']);
            }

            $data['updated_by'] = auth()->id() ?? 1;
            $data['updated_at'] = now();

            DB::table('cases')->where('id', $id)->update($data);

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
     * 删除案例
     */
    public function destroy($id)
    {
        try {
            $case = DB::table('cases')->where('id', $id)->whereNull('deleted_at')->first();
            
            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '案例不存在'
                ], 404);
            }

            DB::table('cases')->where('id', $id)->update([
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
     * 生成案例编号
     */
    private function generateCaseCode($caseType)
    {
        $prefixMap = [
            1 => 'PAT', // 专利
            2 => 'TRA', // 商标
            3 => 'COP', // 版权
            4 => 'SER'  // 科服
        ];
        
        $prefix = $prefixMap[$caseType] ?? 'CAS';
        $date = date('Ymd');
        
        // 获取今日最大编号
        $maxCode = DB::table('cases')
            ->where('case_code', 'like', $prefix . $date . '%')
            ->orderBy('case_code', 'desc')
            ->value('case_code');
        
        if ($maxCode) {
            $number = (int)substr($maxCode, -4) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 转换状态字符串为数值
     */
    private function convertStatusToInt($status)
    {
        $statusMap = [
            'draft' => \App\Models\Cases::STATUS_DRAFT,                    // 1 - 草稿
            'to-be-filed' => \App\Models\Cases::STATUS_TO_BE_FILED,        // 2 - 立项中（提交后）
            'to-be-pending' => \App\Models\Cases::STATUS_TO_BE_FILED,      // 2 - 待处理（同立项中，给审核人员看）
            'filed' => \App\Models\Cases::STATUS_COMPLETED,                // 7 - 已立项
            'completed' => \App\Models\Cases::STATUS_COMPLETED,            // 7 - 已完成
            // 保留其他状态以防万一
            'submitted' => \App\Models\Cases::STATUS_TO_BE_FILED,          // 2 - 已提交（等同立项中）
            'processing' => \App\Models\Cases::STATUS_PROCESSING,          // 4 - 处理中
            'authorized' => \App\Models\Cases::STATUS_AUTHORIZED,          // 5 - 已授权
            'rejected' => \App\Models\Cases::STATUS_REJECTED,              // 6 - 已驳回
        ];

        return $statusMap[$status] ?? (int)$status;
    }
}
