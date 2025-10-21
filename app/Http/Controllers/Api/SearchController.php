<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PatentSearchExport;
use App\Exports\TrademarkSearchExport;
use App\Exports\CopyrightSearchExport;
use App\Exports\ProjectSearchExport;

class SearchController extends Controller
{
    /**
     * 专利查询
     */
    public function searchPatents(Request $request)
    {
        try {
            $query = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as agent_user', 'cc.agent_id', '=', 'agent_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 1)
                ->select([
                    'cc.id',
                    'cc.case_code as our_ref_number',
                    'cc.case_name',
                    'cc.application_no as app_number',
                    'cc.application_date as app_date',
                    'cc.registration_no as reg_number',
                    'cc.registration_date as reg_date',
                    'cc.case_status',
                    'cc.case_phase',
                    'cc.country_code as app_country',
                    'cc.priority_level',
                    'cc.entity_type',
                    'cc.estimated_cost',
                    'cc.actual_cost',
                    'cc.service_fee',
                    'cc.official_fee',
                    'cc.deadline_date',
                    'cc.annual_fee_due_date',
                    'cc.case_description',
                    'cc.technical_field',
                    'cc.innovation_points',
                    'cc.remarks',
                    'cc.created_at as receive_date',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'agent_user.real_name as tech_leader',
                    'assistant.real_name as assistant_name'
                ]);

            // 应用查询条件
            $this->applyPatentFilters($query, $request);

            // 分页
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 10);
            $offset = ($page - 1) * $pageSize;

            $total = $query->count();
            $data = $query->offset($offset)->limit($pageSize)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize,
                    'last_page' => ceil($total / $pageSize)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 商标查询
     */
    public function searchTrademarks(Request $request)
    {
        try {
            $query = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 2)
                ->select([
                    'cc.id',
                    'cc.case_code as our_ref_number',
                    'cc.case_name as trademark_name',
                    'cc.application_no as app_number',
                    'cc.registration_no as reg_number',
                    'cc.application_date as app_date',
                    'cc.registration_date as reg_date',
                    'cc.case_status',
                    'cc.case_phase',
                    'cc.country_code as app_country',
                    'cc.case_subtype as trademark_class',
                    'cc.estimated_cost',
                    'cc.actual_cost',
                    'cc.service_fee',
                    'cc.official_fee',
                    'cc.deadline_date',
                    'cc.case_description',
                    'cc.remarks',
                    'cc.created_at as receive_date',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'assistant.real_name as assistant_name'
                ]);

            // 应用查询条件
            $this->applyTrademarkFilters($query, $request);

            // 分页
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 10);
            $offset = ($page - 1) * $pageSize;

            $total = $query->count();
            $data = $query->offset($offset)->limit($pageSize)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize,
                    'last_page' => ceil($total / $pageSize)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 版权查询
     */
    public function searchCopyrights(Request $request)
    {
        try {
            $query = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 3)
                ->select([
                    'cc.id',
                    'cc.case_code as our_ref_number',
                    'cc.case_name as work_name',
                    'cc.registration_no as reg_number',
                    'cc.application_date as app_date',
                    'cc.registration_date as reg_date',
                    'cc.case_status',
                    'cc.case_phase',
                    'cc.case_subtype as work_type',
                    'cc.estimated_cost',
                    'cc.actual_cost',
                    'cc.service_fee',
                    'cc.official_fee',
                    'cc.deadline_date',
                    'cc.case_description',
                    'cc.remarks',
                    'cc.created_at as receive_date',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'assistant.real_name as assistant_name'
                ]);

            // 应用查询条件
            $this->applyCopyrightFilters($query, $request);

            // 分页
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 10);
            $offset = ($page - 1) * $pageSize;

            $total = $query->count();
            $data = $query->offset($offset)->limit($pageSize)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize,
                    'last_page' => ceil($total / $pageSize)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 科服查询
     */
    public function searchProjects(Request $request)
    {
        try {
            $query = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as agent_user', 'cc.agent_id', '=', 'agent_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 4)
                ->select([
                    'cc.id',
                    'cc.case_code as project_number',
                    'cc.case_phase as apply_stage',
                    'cc.case_name as tech_service_name',
                    'cc.case_status as apply_result',
                    'cc.application_type',
                    'cc.case_subtype as business_type',
                    'cc.estimated_cost as gov_estimated_reward',
                    'cc.actual_cost as gov_actual_reward',
                    'cc.service_fee',
                    'cc.official_fee',
                    'cc.priority_level as is_urgent',
                    'cc.deadline_date as apply_deadline',
                    'cc.application_date as actual_submit_date',
                    'cc.created_at as receive_date',
                    'cc.case_description',
                    'cc.technical_field',
                    'cc.innovation_points',
                    'cc.remarks',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'agent_user.real_name as tech_lead',
                    'assistant.real_name as assistant_name'
                ]);

            // 应用查询条件
            $this->applyProjectFilters($query, $request);

            // 分页
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 10);
            $offset = ($page - 1) * $pageSize;

            $total = $query->count();
            $data = $query->offset($offset)->limit($pageSize)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $data,
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $pageSize,
                    'last_page' => ceil($total / $pageSize)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 专利查询导出
     */
    public function exportPatents(Request $request)
    {
        try {
            return Excel::download(new PatentSearchExport($request->all()), '专利查询_' . date('YmdHis') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 商标查询导出
     */
    public function exportTrademarks(Request $request)
    {
        try {
            return Excel::download(new TrademarkSearchExport($request->all()), '商标查询_' . date('YmdHis') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 版权查询导出
     */
    public function exportCopyrights(Request $request)
    {
        try {
            return Excel::download(new CopyrightSearchExport($request->all()), '版权查询_' . date('YmdHis') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 科服查询导出
     */
    public function exportProjects(Request $request)
    {
        try {
            return Excel::download(new ProjectSearchExport($request->all()), '科服查询_' . date('YmdHis') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取专利详情
     */
    public function getPatentDetail($id)
    {
        try {
            $patent = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as agent_user', 'cc.agent_id', '=', 'agent_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 1)
                ->where('cc.id', $id)
                ->select([
                    'cc.*',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'agent_user.real_name as tech_leader',
                    'assistant.real_name as assistant_name'
                ])
                ->first();

            if (!$patent) {
                return response()->json([
                    'success' => false,
                    'message' => '专利信息未找到'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $patent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取商标详情
     */
    public function getTrademarkDetail($id)
    {
        try {
            $trademark = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 2)
                ->where('cc.id', $id)
                ->select([
                    'cc.*',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'assistant.real_name as assistant_name'
                ])
                ->first();

            if (!$trademark) {
                return response()->json([
                    'success' => false,
                    'message' => '商标信息未找到'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $trademark
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版权详情
     */
    public function getCopyrightDetail($id)
    {
        try {
            $copyright = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 3)
                ->where('cc.id', $id)
                ->select([
                    'cc.*',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'assistant.real_name as assistant_name'
                ])
                ->first();

            if (!$copyright) {
                return response()->json([
                    'success' => false,
                    'message' => '版权信息未找到'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $copyright
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取科服详情
     */
    public function getProjectDetail($id)
    {
        try {
            $project = DB::table('cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
                ->leftJoin('users as agent_user', 'cc.agent_id', '=', 'agent_user.id')
                ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
                ->where('cc.case_type', 4)
                ->where('cc.id', $id)
                ->select([
                    'cc.*',
                    'c.customer_name',
                    'business_user.real_name as business_person',
                    'agent_user.real_name as tech_lead',
                    'assistant.real_name as assistant_name'
                ])
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => '科服项目信息未找到'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $project
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 应用专利查询过滤条件
     */
    private function applyPatentFilters($query, $request)
    {
        if ($request->filled('ourRefNumber')) {
            $query->where('cc.case_code', 'like', '%' . $request->ourRefNumber . '%');
        }

        if ($request->filled('appNumber')) {
            $query->where('cc.application_no', 'like', '%' . $request->appNumber . '%');
        }

        if ($request->filled('customerName')) {
            $query->where('c.customer_name', 'like', '%' . $request->customerName . '%');
        }

        if ($request->filled('caseName')) {
            $query->where('cc.case_name', 'like', '%' . $request->caseName . '%');
        }

        if ($request->filled('applicationType')) {
            $query->where('cc.application_type', $request->applicationType);
        }

        if ($request->filled('caseStatus')) {
            $query->where('cc.case_status', $request->caseStatus);
        }

        if ($request->filled('appDateRange') && is_array($request->appDateRange) && count($request->appDateRange) == 2) {
            $query->whereBetween('cc.application_date', $request->appDateRange);
        }

        if ($request->filled('businessPerson')) {
            $query->where('business_user.real_name', 'like', '%' . $request->businessPerson . '%');
        }

        if ($request->filled('appCountry')) {
            $query->where('cc.country_code', $request->appCountry);
        }
    }

    /**
     * 应用商标查询过滤条件
     */
    private function applyTrademarkFilters($query, $request)
    {
        if ($request->filled('ourRefNumber')) {
            $query->where('cc.case_code', 'like', '%' . $request->ourRefNumber . '%');
        }

        if ($request->filled('regNumber')) {
            $query->where('cc.registration_no', 'like', '%' . $request->regNumber . '%');
        }

        if ($request->filled('appNumber')) {
            $query->where('cc.application_no', 'like', '%' . $request->appNumber . '%');
        }

        if ($request->filled('customerName')) {
            $query->where('c.customer_name', 'like', '%' . $request->customerName . '%');
        }

        if ($request->filled('trademarkName')) {
            $query->where('cc.case_name', 'like', '%' . $request->trademarkName . '%');
        }

        if ($request->filled('trademarkClass')) {
            $query->where('cc.case_subtype', $request->trademarkClass);
        }

        if ($request->filled('caseStatus')) {
            $query->where('cc.case_status', $request->caseStatus);
        }

        if ($request->filled('appDateRange') && is_array($request->appDateRange) && count($request->appDateRange) == 2) {
            $query->whereBetween('cc.application_date', $request->appDateRange);
        }

        if ($request->filled('businessPerson')) {
            $query->where('business_user.real_name', 'like', '%' . $request->businessPerson . '%');
        }
    }

    /**
     * 应用版权查询过滤条件
     */
    private function applyCopyrightFilters($query, $request)
    {
        if ($request->filled('ourRefNumber')) {
            $query->where('cc.case_code', 'like', '%' . $request->ourRefNumber . '%');
        }

        if ($request->filled('regNumber')) {
            $query->where('cc.registration_no', 'like', '%' . $request->regNumber . '%');
        }

        if ($request->filled('workName')) {
            $query->where('cc.case_name', 'like', '%' . $request->workName . '%');
        }

        if ($request->filled('customerName')) {
            $query->where('c.customer_name', 'like', '%' . $request->customerName . '%');
        }

        if ($request->filled('workType')) {
            $query->where('cc.case_subtype', $request->workType);
        }

        if ($request->filled('caseStatus')) {
            $query->where('cc.case_status', $request->caseStatus);
        }

        if ($request->filled('appDateRange') && is_array($request->appDateRange) && count($request->appDateRange) == 2) {
            $query->whereBetween('cc.application_date', $request->appDateRange);
        }

        if ($request->filled('businessPerson')) {
            $query->where('business_user.real_name', 'like', '%' . $request->businessPerson . '%');
        }
    }

    /**
     * 应用科服查询过滤条件
     */
    private function applyProjectFilters($query, $request)
    {
        if ($request->filled('projectNumber')) {
            $query->where('cc.case_code', 'like', '%' . $request->projectNumber . '%');
        }

        if ($request->filled('applyStage')) {
            if ($request->applyStage === '__empty__') {
                $query->whereNull('cc.case_phase');
            } else {
                $query->where('cc.case_phase', $request->applyStage);
            }
        }

        if ($request->filled('applyResult')) {
            if ($request->applyResult === '__empty__') {
                $query->whereNull('cc.case_status');
            } else {
                $query->where('cc.case_status', $request->applyResult);
            }
        }

        if ($request->filled('customerName')) {
            $query->where('c.customer_name', 'like', '%' . $request->customerName . '%');
        }

        if ($request->filled('businessType')) {
            $query->where('cc.case_subtype', $request->businessType);
        }

        if ($request->filled('applicationType')) {
            $query->where('cc.application_type', $request->applicationType);
        }

        if ($request->filled('techServiceName')) {
            $query->where('cc.case_name', 'like', '%' . $request->techServiceName . '%');
        }

        if ($request->filled('businessPerson')) {
            if ($request->businessPerson === '__empty__') {
                $query->whereNull('cc.business_person_id');
            } else {
                $query->where('business_user.real_name', 'like', '%' . $request->businessPerson . '%');
            }
        }
    }

    /**
     * 获取业务人员列表
     */
    public function getBusinessPersons()
    {
        $persons = DB::table('users')
            ->whereNull('deleted_at')
            ->whereNotNull('real_name')
            ->select('id', 'real_name as name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $persons
        ]);
    }

    /**
     * 获取其他辅助数据的方法...
     */
    public function getCaseHandlers()
    {
        return $this->getBusinessPersons(); // 复用业务人员数据
    }

    public function getTechLeaders()
    {
        return $this->getBusinessPersons(); // 复用业务人员数据
    }

    public function getRegions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['name' => '北京', 'children' => [['name' => '北京市', 'children' => [['name' => '海淀区'], ['name' => '朝阳区']]]]],
                ['name' => '上海', 'children' => [['name' => '上海市', 'children' => [['name' => '浦东新区'], ['name' => '黄浦区']]]]],
                ['name' => '广东', 'children' => [['name' => '广州市', 'children' => [['name' => '天河区'], ['name' => '越秀区']]], ['name' => '深圳市', 'children' => [['name' => '南山区'], ['name' => '福田区']]]]]
            ]
        ]);
    }

    public function getAgencies()
    {
        $agencies = DB::table('agencies')
            ->whereNull('deleted_at')
            ->select('id', 'agency_name_cn as name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $agencies
        ]);
    }

    public function getDepartments()
    {
        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->select('id', 'department_name as name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    public function getCountries()
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['name' => '中国', 'code' => 'CN'],
                ['name' => '美国', 'code' => 'US'],
                ['name' => '日本', 'code' => 'JP'],
                ['name' => '欧盟', 'code' => 'EU'],
                ['name' => '英国', 'code' => 'GB'],
                ['name' => '德国', 'code' => 'DE'],
                ['name' => '法国', 'code' => 'FR']
            ]
        ]);
    }
}
