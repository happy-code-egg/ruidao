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

/**
 * 功能: 查询模块控制器，集中提供专利、商标、版权、科服的列表查询、导出与详情接口，
 *       以及前端筛选所需的辅助数据（人员、地区、机构、部门、国家）。
 * 路由前缀: /api/search
 * 说明: 仅补充注释，不改动任何业务逻辑或日志。
 */
class SearchController extends Controller
{
    /**
     * 功能: 专利综合查询，支持多条件筛选与分页。
     * 接口: GET /api/search/patents (route: api.search.patents)
     * 请求参数:
     * - page int 当前页，默认 1
     * - page_size int 每页条数，默认 10
     * - ourRefNumber string 我方案号，模糊匹配
     * - appNumber string 申请号，模糊匹配
     * - customerName string 客户名称，模糊匹配
     * - caseName string 项目名称，模糊匹配
     * - applicationType string|int 申请类型，精确匹配
     * - caseStatus string|int 项目状态，精确匹配
     * - appDateRange array [开始日期, 结束日期]，格式 YYYY-MM-DD
     * - businessPerson string 业务人员姓名，模糊匹配
     * - appCountry string 国家/地区代码，精确匹配
     * 返回参数:
     * - success bool
     * - data.object 列表和分页信息：data, total, current_page, per_page, last_page
     * 内部说明:
     * - 关联 cases/customers/users 表字段，选择常用显示列
     * - 调用 applyPatentFilters 应用筛选条件
     * - 使用 count/offset/limit 分页
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
     * 功能: 商标综合查询，支持多条件筛选与分页。
     * 接口: GET /api/search/trademarks (route: api.search.trademarks)
     * 请求参数:
     * - page int 当前页，默认 1
     * - page_size int 每页条数，默认 10
     * - ourRefNumber string 我方案号，模糊匹配
     * - regNumber string 注册号，模糊匹配
     * - appNumber string 申请号，模糊匹配
     * - customerName string 客户名称，模糊匹配
     * - trademarkName string 商标名称，模糊匹配
     * - trademarkClass string|int 商标类别，精确匹配
     * - caseStatus string|int 项目状态，精确匹配
     * - appDateRange array [开始日期, 结束日期]，格式 YYYY-MM-DD
     * - businessPerson string 业务人员姓名，模糊匹配
     * 返回参数:
     * - success bool
     * - data.object 列表和分页信息：data, total, current_page, per_page, last_page
     * 内部说明:
     * - 关联 cases/customers/users 表字段
     * - 调用 applyTrademarkFilters 应用筛选条件
     * - 使用 count/offset/limit 分页
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
     * 功能: 版权综合查询，支持多条件筛选与分页。
     * 接口: GET /api/search/copyrights (route: api.search.copyrights)
     * 请求参数:
     * - page int 当前页，默认 1
     * - page_size int 每页条数，默认 10
     * - ourRefNumber string 我方案号，模糊匹配
     * - regNumber string 登记号，模糊匹配
     * - workName string 作品名称，模糊匹配
     * - customerName string 客户名称，模糊匹配
     * - workType string|int 作品类型，精确匹配
     * - caseStatus string|int 项目状态，精确匹配
     * - appDateRange array [开始日期, 结束日期]，格式 YYYY-MM-DD
     * - businessPerson string 业务人员姓名，模糊匹配
     * 返回参数:
     * - success bool
     * - data.object 列表和分页信息：data, total, current_page, per_page, last_page
     * 内部说明:
     * - 关联 cases/customers/users 表字段
     * - 调用 applyCopyrightFilters 应用筛选
     * - 使用 count/offset/limit 分页
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
     * 功能: 科技服务项目综合查询，支持多条件筛选与分页。
     * 接口: GET /api/search/projects (route: api.search.projects)
     * 请求参数:
     * - page int 当前页，默认 1
     * - page_size int 每页条数，默认 10
     * - projectNumber string 项目编号，模糊匹配
     * - applyStage string|int 申报阶段，精确匹配；传 '__empty__' 代表过滤空值
     * - applyResult string|int 申报结果，精确匹配；传 '__empty__' 代表过滤空值
     * - customerName string 客户名称，模糊匹配
     * - businessType string|int 业务类型，精确匹配
     * - applicationType string|int 申请类型，精确匹配
     * - techServiceName string 科服项目名称，模糊匹配
     * - businessPerson string 业务人员姓名；传 '__empty__' 时匹配业务人员为空
     * 返回参数:
     * - success bool
     * - data.object 列表和分页信息：data, total, current_page, per_page, last_page
     * 内部说明:
     * - 关联 cases/customers/users 表字段
     * - 调用 applyProjectFilters 应用筛选，包含空值特殊处理
     * - 使用 count/offset/limit 分页
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
     * 功能: 导出专利查询结果为 Excel 文件。
     * 接口: POST /api/search/patents/export (route: api.search.patents.export)
     * 请求参数: 与 GET /api/search/patents 相同，作为导出筛选条件。
     * 返回参数: 直接触发文件下载，文件名形如 "专利查询_YYYYMMDDHHMMSS.xlsx"。
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
     * 功能: 导出商标查询结果为 Excel 文件。
     * 接口: POST /api/search/trademarks/export (route: api.search.trademarks.export)
     * 请求参数: 与 GET /api/search/trademarks 相同，作为导出筛选条件。
     * 返回参数: 直接触发文件下载，文件名形如 "商标查询_YYYYMMDDHHMMSS.xlsx"。
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
     * 功能: 导出版权查询结果为 Excel 文件。
     * 接口: POST /api/search/copyrights/export (route: api.search.copyrights.export)
     * 请求参数: 与 GET /api/search/copyrights 相同，作为导出筛选条件。
     * 返回参数: 直接触发文件下载，文件名形如 "版权查询_YYYYMMDDHHMMSS.xlsx"。
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
     * 功能: 导出科服查询结果为 Excel 文件。
     * 接口: POST /api/search/projects/export (route: api.search.projects.export)
     * 请求参数: 与 GET /api/search/projects 相同，作为导出筛选条件。
     * 返回参数: 直接触发文件下载，文件名形如 "科服查询_YYYYMMDDHHMMSS.xlsx"。
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
     * 功能: 获取单个专利项目详情。
     * 接口: GET /api/search/patents/{id}/detail (route: api.search.patents.detail)
     * 请求参数:
     * - id int 路径参数，项目ID
     * 返回参数:
     * - success bool
     * - data.object 项目完整详情；未找到返回 404。
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
     * 功能: 获取单个商标项目详情。
     * 接口: GET /api/search/trademarks/{id}/detail (route: api.search.trademarks.detail)
     * 请求参数:
     * - id int 路径参数，项目ID
     * 返回参数:
     * - success bool
     * - data.object 项目完整详情；未找到返回 404。
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
     * 功能: 获取单个版权项目详情。
     * 接口: GET /api/search/copyrights/{id}/detail (route: api.search.copyrights.detail)
     * 请求参数:
     * - id int 路径参数，项目ID
     * 返回参数:
     * - success bool
     * - data.object 项目完整详情；未找到返回 404。
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
     * 功能: 获取单个科技服务项目详情。
     * 接口: GET /api/search/projects/{id}/detail (route: api.search.projects.detail)
     * 请求参数:
     * - id int 路径参数，项目ID
     * 返回参数:
     * - success bool
     * - data.object 项目完整详情；未找到返回 404。
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
     * 功能: 将专利查询的请求参数转换为 SQL 过滤条件。
     * 调用方: searchPatents
     * 请求参数支持:
     * - ourRefNumber, appNumber, customerName, caseName, applicationType,
     *   caseStatus, appDateRange([开始,结束]), businessPerson, appCountry。
     * 内部说明: 模糊匹配使用 like；日期范围使用 whereBetween。
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
     * 功能: 将商标查询的请求参数转换为 SQL 过滤条件。
     * 调用方: searchTrademarks
     * 请求参数支持:
     * - ourRefNumber, regNumber, appNumber, customerName, trademarkName,
     *   trademarkClass, caseStatus, appDateRange([开始,结束]), businessPerson。
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
     * 功能: 将版权查询的请求参数转换为 SQL 过滤条件。
     * 调用方: searchCopyrights
     * 请求参数支持:
     * - ourRefNumber, regNumber, workName, customerName, workType,
     *   caseStatus, appDateRange([开始,结束]), businessPerson。
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
      * 功能: 将科服查询的请求参数转换为 SQL 过滤条件，处理空值特殊标识。
      * 调用方: searchProjects
      * 请求参数支持:
      * - projectNumber, applyStage('__empty__' 表示匹配空值), applyResult('__empty__'),
      *   customerName, businessType, applicationType, techServiceName,
      *   businessPerson('__empty__' 表示业务人员为空)。
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
     * 功能: 获取业务人员列表（供前端筛选使用）。
     * 接口: GET /api/search/business-persons (route: api.search.business.persons)
     * 返回参数: success, data[ {id, name} ]。
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
     * 功能: 获取案件处理人列表。
     * 接口: GET /api/search/case-handlers (route: api.search.case.handlers)
     * 返回参数: success, data[ {id, name} ]；复用业务人员数据。
     */
    public function getCaseHandlers()
    {
        return $this->getBusinessPersons(); // 复用业务人员数据
    }

    /**
     * 功能: 获取技术主导列表。
     * 接口: GET /api/search/tech-leaders (route: api.search.tech.leaders)
     * 返回参数: success, data[ {id, name} ]；复用业务人员数据。
     */
    public function getTechLeaders()
    {
        return $this->getBusinessPersons(); // 复用业务人员数据
    }

    /**
     * 功能: 获取地区树结构（示例数据）。
     * 接口: GET /api/search/regions (route: api.search.regions)
     * 返回参数: success, data 为省-市-区层级结构。
     */
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

    /**
     * 功能: 获取代理机构列表。
     * 接口: GET /api/search/agencies (route: api.search.agencies)
     * 返回参数: success, data[ {id, name} ]。
     */
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

    /**
     * 功能: 获取部门列表。
     * 接口: GET /api/search/departments (route: api.search.departments)
     * 返回参数: success, data[ {id, name} ]。
     */
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

    /**
     * 功能: 获取国家/地区选项（示例数据）。
     * 接口: GET /api/search/countries (route: api.search.countries)
     * 返回参数: success, data[ {name, code} ]。
     */
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
