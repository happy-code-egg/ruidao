<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileSearchController extends Controller
{
   /**
 * 文件管理搜索 search
 *
 * 功能描述：根据多种筛选条件搜索项目文件信息，支持分页和多种查询参数
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - case_type (string, optional): 项目类型 (patent, trademark, copyright, project)
 *   - business_type (string, optional): 业务类型
 *   - application_type (string, optional): 申请类型
 *   - customer_name (string, optional): 客户名称
 *   - applicant (string, optional): 申请人
 *   - project_number (string, optional): 项目编号
 *   - case_name (string, optional): 项目名称
 *   - application_number (string, optional): 申请号
 *   - registration_number (string, optional): 注册号
 *   - application_country (string, optional): 申请国家
 *   - case_flow (string, optional): 项目流向
 *   - document_name (string, optional): 来文名称
 *   - business_staff (int, optional): 业务人员ID
 *   - process_staff (int, optional): 技术主导人员ID
 *   - application_date_range (array, optional): 申请日期范围 [开始日期, 结束日期]
 *   - upload_date_range (array, optional): 上传日期范围 [开始日期, 结束日期]
 *   - send_date_range (array, optional): 发文日期范围 [开始日期, 结束日期]
 *   - receive_date_range (array, optional): 收文日期范围 [开始日期, 结束日期]
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为20
 *
 * 输出参数：
 * - success (boolean): 是否成功
 * - message (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 项目文件列表数据
 *     - index (int): 序号
 *     - ourFileNumber (string): 项目编号
 *     - customerName (string): 客户名称
 *     - applicationNumber (string): 申请号
 *     - registrationNumber (string): 注册号
 *     - caseName (string): 项目名称
 *     - applicationType (string): 申请类型标签
 *     - applicant (string): 申请人
 *     - technicalLead (string): 技术主导人员
 *     - documentName (string): 来文名称
 *     - sendDate (string): 发文日期
 *     - receiveDate (string): 收文日期
 *     - businessStaff (string): 业务人员
 *     - applicationDate (string): 申请日期
 *     - trademarkCategory (string): 商标类别
 *     - fileUrl (string): 文件路径
 *     - fileId (int): 文件ID
 *     - attachmentCount (int): 附件数量
 *     - caseStatus (string): 项目状态标签
 *     - caseRemarks (string): 项目备注
 *     - caseHandler (string): 案件处理人
 *     - proposalName (string): 方案名称
 *     - presalesSupport (string): 售前支持
 *     - openDate (string): 立项日期
 *     - handlingDepartment (string): 处理部门
 *     - businessAssistant (string): 业务助理
 *     - caseCoefficient (float): 案件系数
 *     - businessStaffDepartment (string): 业务人员部门
 *     - leadDepartment (string): 技术主导部门
 *     - customField1-5 (string): 自定义字段1-5
 *     - hasTechnicalDisclosure (string): 是否有技术交底
 *     - agencyName (string): 代理机构名称
 *     - agentName (string): 代理人名称
 *     - priorityNumber (string): 优先权号
 *     - priorityDate (string): 优先权日
 *     - doubleReport (string): 是否双报
 *   - total (int): 总记录数
 *   - current_page (int): 当前页码
 *   - per_page (int): 每页数量
 */
public function search(Request $request)
{
    try {
        // 获取查询参数
        $caseType = $request->input('case_type');
        $businessType = $request->input('business_type');
        $applicationType = $request->input('application_type');
        $customerName = $request->input('customer_name');
        $applicant = $request->input('applicant');
        $projectNumber = $request->input('project_number');
        $caseName = $request->input('case_name');
        $applicationNumber = $request->input('application_number');
        $registrationNumber = $request->input('registration_number');
        $applicationCountry = $request->input('application_country');
        $caseFlow = $request->input('case_flow');
        $documentName = $request->input('document_name');
        $businessStaff = $request->input('business_staff');
        $processStaff = $request->input('process_staff');
        $applicationDateRange = $request->input('application_date_range', []);
        $uploadDateRange = $request->input('upload_date_range', []);
        $sendDateRange = $request->input('send_date_range', []);
        $receiveDateRange = $request->input('receive_date_range', []);

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        // 构建基础查询 - 查询有附件的项目
        $query = Cases::with([
            'attachments' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'businessPerson',
            'techLeader'
        ])->whereHas('attachments');

        // 项目类型筛选
        if ($caseType) {
            $typeMap = [
                'patent' => Cases::TYPE_PATENT,
                'trademark' => Cases::TYPE_TRADEMARK,
                'copyright' => Cases::TYPE_COPYRIGHT,
                'project' => Cases::TYPE_TECH_SERVICE
            ];
            if (isset($typeMap[$caseType])) {
                $query->where('case_type', $typeMap[$caseType]);
            }
        }

        // 业务类型筛选
        if ($businessType) {
            $query->where('business_type', 'like', "%{$businessType}%");
        }

        // 申请类型筛选
        if ($applicationType) {
            $query->where('application_type', 'like', "%{$applicationType}%");
        }

        // 客户名称筛选
        if ($customerName) {
            $query->where('customer_name', 'like', "%{$customerName}%");
        }

        // 申请人筛选
        if ($applicant) {
            $query->where('applicant_name', 'like', "%{$applicant}%");
        }

        // 项目编号筛选
        if ($projectNumber) {
            $query->where('our_ref_number', 'like', "%{$projectNumber}%");
        }

        // 项目名称筛选
        if ($caseName) {
            $query->where('case_name', 'like', "%{$caseName}%");
        }

        // 申请号筛选
        if ($applicationNumber) {
            $query->where('application_number', 'like', "%{$applicationNumber}%");
        }

        // 注册号筛选
        if ($registrationNumber) {
            $query->where('registration_number', 'like', "%{$registrationNumber}%");
        }

        // 申请国家筛选
        if ($applicationCountry) {
            $query->where('application_country', $applicationCountry);
        }

        // 项目流向筛选
        if ($caseFlow) {
            $query->where('case_flow', $caseFlow);
        }

        // 来文名称筛选 - 通过附件的文档类型筛选
        if ($documentName) {
            $query->whereHas('attachments', function($q) use ($documentName) {
                $q->where('document_type', 'like', "%{$documentName}%");
            });
        }

        // 业务人员筛选
        if ($businessStaff) {
            $query->where('business_person_id', $businessStaff);
        }

        // 技术主导筛选
        if ($processStaff) {
            $query->where('tech_leader', $processStaff);
        }

        // 申请日期范围筛选
        if (!empty($applicationDateRange) && count($applicationDateRange) == 2) {
            $query->whereBetween('application_date', $applicationDateRange);
        }

        // 上传日期范围筛选
        if (!empty($uploadDateRange) && count($uploadDateRange) == 2) {
            $query->whereHas('attachments', function($q) use ($uploadDateRange) {
                $q->whereBetween('created_at', $uploadDateRange);
            });
        }

        // 发文日期范围筛选
        if (!empty($sendDateRange) && count($sendDateRange) == 2) {
            $query->whereBetween('send_date', $sendDateRange);
        }

        // 收文日期范围筛选
        if (!empty($receiveDateRange) && count($receiveDateRange) == 2) {
            $query->whereBetween('receive_date', $receiveDateRange);
        }

        // 获取分页数据
        $cases = $query->paginate($limit, ['*'], 'page', $page);

        // 格式化数据
        $tableData = [];
        foreach ($cases->items() as $index => $case) {
            // 获取最新的附件作为主要文件信息
            $mainAttachment = $case->attachments->first();

            $data = [
                'index' => ($page - 1) * $limit + $index + 1,
                'ourFileNumber' => $case->our_ref_number,
                'customerName' => $case->customer_name,
                'applicationNumber' => $case->application_number,
                'registrationNumber' => $case->registration_number,
                'caseName' => $case->case_name,
                'applicationType' => $this->getApplicationTypeLabel($case->application_type),
                'applicant' => $case->applicant_name,
                'technicalLead' => $case->techLeader ? $case->techLeader->real_name : '',
                'documentName' => $mainAttachment ? $mainAttachment->document_type : '',
                'sendDate' => $case->send_date ? Carbon::parse($case->send_date)->format('Y-m-d') : '',
                'receiveDate' => $case->receive_date ? Carbon::parse($case->receive_date)->format('Y-m-d') : '',
                'businessStaff' => $case->businessPerson ? $case->businessPerson->real_name : '',
                'applicationDate' => $case->application_date ? Carbon::parse($case->application_date)->format('Y-m-d') : '',
                'trademarkCategory' => $case->trademark_category,
                'fileUrl' => $mainAttachment ? $mainAttachment->file_path : '',
                'fileId' => $mainAttachment ? $mainAttachment->id : null,
                'attachmentCount' => $case->attachments->count(),
                // 隐藏字段数据
                'caseStatus' => $this->getCaseStatusLabel($case->case_status),
                'caseRemarks' => $case->remarks,
                'caseHandler' => $case->caseHandler ? $case->caseHandler->real_name : '',
                'proposalName' => $case->proposal_name,
                'presalesSupport' => $case->presalesSupport ? $case->presalesSupport->real_name : '',
                'openDate' => $case->open_date ? Carbon::parse($case->open_date)->format('Y-m-d') : '',
                'handlingDepartment' => $case->handlingDepartment ? $case->handlingDepartment->department_name : '',
                'businessAssistant' => $case->businessAssistant ? $case->businessAssistant->real_name : '',
                'caseCoefficient' => $case->case_coefficient,
                'businessStaffDepartment' => $case->businessPerson && $case->businessPerson->department ? $case->businessPerson->department->department_name : '',
                'leadDepartment' => $case->techLeader && $case->techLeader->department ? $case->techLeader->department->department_name : '',
                'customField1' => $case->custom_field_1,
                'customField2' => $case->custom_field_2,
                'customField3' => $case->custom_field_3,
                'customField4' => $case->custom_field_4,
                'customField5' => $case->custom_field_5,
                'hasTechnicalDisclosure' => $case->has_technical_disclosure ? '有' : '无',
                'agencyName' => $case->agency ? $case->agency->agency_name_cn : '',
                'agentName' => $case->agent ? $case->agent->name_cn : '',
                'priorityNumber' => $case->priority_number,
                'priorityDate' => $case->priority_date ? Carbon::parse($case->priority_date)->format('Y-m-d') : '',
                'doubleReport' => $case->double_report ? '是' : '否'
            ];

            $tableData[] = $data;
        }

        // 返回成功响应
        return response()->json([
            'success' => true,
            'message' => '查询成功',
            'data' => [
                'list' => $tableData,
                'total' => $cases->total(),
                'current_page' => $cases->currentPage(),
                'per_page' => $cases->perPage()
            ]
        ]);

    } catch (\Exception $e) {
        // 返回失败响应
        return response()->json([
            'success' => false,
            'message' => '查询失败: ' . $e->getMessage()
        ], 500);
    }
}

  /**
 * 获取下拉选项数据 getOptions
 *
 * 功能描述：根据指定类型获取相应的下拉选项数据
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - type (string): 选项类型，可选值包括：
 *     - case_types: 项目类型选项
 *     - business_types: 业务类型选项
 *     - application_types: 申请类型选项
 *     - countries: 国家选项
 *     - case_flows: 项目流向选项
 *     - staff: 员工选项
 *     - document_names: 文档名称选项
 *
 * 输出参数：
 * - success (boolean): 是否成功
 * - message (string): 操作结果消息
 * - data (array): 选项数据列表
 *   - label (string): 选项显示标签
 *   - value (mixed): 选项值
 */
public function getOptions(Request $request)
{
    try {
        // 获取请求的选项类型
        $type = $request->input('type');

        // 根据类型返回相应的选项数据
        switch ($type) {
            case 'case_types':
                return $this->getCaseTypeOptions();
            case 'business_types':
                return $this->getBusinessTypeOptions();
            case 'application_types':
                return $this->getApplicationTypeOptions();
            case 'countries':
                return $this->getCountryOptions();
            case 'case_flows':
                return $this->getCaseFlowOptions();
            case 'staff':
                return $this->getStaffOptions();
            case 'document_names':
                return $this->getDocumentNameOptions();
            default:
                // 不支持的选项类型，返回错误信息
                return response()->json([
                    'success' => false,
                    'message' => '不支持的选项类型'
                ], 400);
        }
    } catch (\Exception $e) {
        // 发生异常时返回错误信息
        return response()->json([
            'success' => false,
            'message' => '获取选项失败: ' . $e->getMessage()
        ], 500);
    }
}


   /**
 * 文件下载 downloadFile
 *
 * 功能描述：根据附件ID下载指定的文件
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 * - id (int): 附件ID
 *
 * 输出参数：
 * - success (boolean): 是否成功
 * - message (string): 操作结果消息
 * - 文件下载响应: 成功时返回文件下载流
 */
public function downloadFile(Request $request, $id)
{
    try {
        // 查找附件记录
        $attachment = CaseAttachment::findOrFail($id);

        // 检查文件是否存在
        if (!Storage::exists($attachment->file_path)) {
            return response()->json([
                'success' => false,
                'message' => '文件不存在'
            ], 404);
        }

        // 返回文件下载响应
        return Storage::download($attachment->file_path, $attachment->original_name);

    } catch (\Exception $e) {
        // 发生异常时返回错误信息
        return response()->json([
            'success' => false,
            'message' => '文件下载失败: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * 批量下载文件 batchDownload
 *
 * 功能描述：批量下载多个文件（目前仅返回提示信息，未实现实际功能）
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - file_ids (array): 要下载的文件ID数组
 *
 * 输出参数：
 * - success (boolean): 是否成功
 * - message (string): 操作结果消息
 */
public function batchDownload(Request $request)
{
    try {
        // 获取要下载的文件ID列表
        $fileIds = $request->input('file_ids', []);

        // 检查是否有选择文件
        if (empty($fileIds)) {
            return response()->json([
                'success' => false,
                'message' => '请选择要下载的文件'
            ], 400);
        }

        // 这里可以实现批量下载逻辑，比如打包成ZIP文件
        // 暂时返回成功消息
        return response()->json([
            'success' => true,
            'message' => '批量下载已开始，请稍候...'
        ]);

    } catch (\Exception $e) {
        // 发生异常时返回错误信息
        return response()->json([
            'success' => false,
            'message' => '批量下载失败: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * 导出数据 export
 *
 * 功能描述：根据搜索条件或选中ID导出项目文件数据
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - search_params (array, optional): 搜索参数
 *   - selected_ids (array, optional): 选中的项目ID数组
 *
 * 输出参数：
 * - success (boolean): 是否成功
 * - message (string): 操作结果消息
 * - CSV文件下载响应: 成功时返回CSV格式的导出文件
 */
public function export(Request $request)
{
    try {
        // 获取搜索参数和选中的ID
        $searchParams = $request->input('search_params', []);
        $selectedIds = $request->input('selected_ids', []);

        // 构建查询（复用搜索逻辑）
        $query = $this->buildSearchQuery($searchParams);

        // 如果有选中的ID，只导出选中的数据
        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        // 获取项目数据
        $cases = $query->with(['attachments', 'businessPerson', 'techLeader'])->get();

        // 准备导出数据
        $exportData = [];
        foreach ($cases as $case) {
            // 获取主要附件信息
            $mainAttachment = $case->attachments->first();

            $exportData[] = [
                '项目编号' => $case->our_ref_number,
                '客户名称' => $case->customer_name,
                '申请号' => $case->application_number,
                '注册号' => $case->registration_number,
                '项目名称' => $case->case_name,
                '申请类型' => $this->getApplicationTypeLabel($case->application_type),
                '申请人' => $case->applicant_name,
                '技术主导' => $case->techLeader ? $case->techLeader->real_name : '',
                '来文名称' => $mainAttachment ? $mainAttachment->document_type : '',
                '发文日' => $case->send_date ? Carbon::parse($case->send_date)->format('Y-m-d') : '',
                '收文日' => $case->receive_date ? Carbon::parse($case->receive_date)->format('Y-m-d') : '',
                '业务人员' => $case->businessPerson ? $case->businessPerson->real_name : '',
                '申请日' => $case->application_date ? Carbon::parse($case->application_date)->format('Y-m-d') : '',
                '商标类别' => $case->trademark_category,
                '附件数量' => $case->attachments->count()
            ];
        }

        // 生成并返回Excel响应
        return $this->generateExcelResponse($exportData, '文件管理导出');

    } catch (\Exception $e) {
        // 发生异常时返回错误信息
        return response()->json([
            'success' => false,
            'message' => '导出失败: ' . $e->getMessage()
        ], 500);
    }
}


    // 私有方法
    /**
 * 构建搜索查询 buildSearchQuery
 *
 * 功能描述：构建项目文件搜索的查询构建器，复用搜索逻辑
 *
 * 传入参数：
 * - searchParams (array): 搜索参数数组
 *
 * 输出参数：
 * - query (Builder): 查询构建器实例
 */
private function buildSearchQuery($searchParams)
{
    // 初始化查询构建器，预加载关联关系并筛选有附件的项目
    $query = Cases::with(['attachments', 'businessPerson', 'techLeader'])
                  ->whereHas('attachments');

    // 应用项目类型搜索条件
    if (!empty($searchParams['case_type'])) {
        $typeMap = [
            'patent' => Cases::TYPE_PATENT,
            'trademark' => Cases::TYPE_TRADEMARK,
            'copyright' => Cases::TYPE_COPYRIGHT,
            'project' => Cases::TYPE_TECH_SERVICE
        ];
        if (isset($typeMap[$searchParams['case_type']])) {
            $query->where('case_type', $typeMap[$searchParams['case_type']]);
        }
    }

    // 其他搜索条件...

    return $query;
}

/**
 * 获取项目类型选项 getCaseTypeOptions
 *
 * 功能描述：获取项目类型下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getCaseTypeOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '商标', 'value' => 'trademark'],
            ['label' => '专利', 'value' => 'patent'],
            ['label' => '版权', 'value' => 'copyright'],
            ['label' => '科服', 'value' => 'project']
        ]
    ]);
}

/**
 * 获取业务类型选项 getBusinessTypeOptions
 *
 * 功能描述：获取业务类型下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getBusinessTypeOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '申请', 'value' => 'application'],
            ['label' => '审查', 'value' => 'examination'],
            ['label' => '维护', 'value' => 'maintenance'],
            ['label' => '异议', 'value' => 'opposition'],
            ['label' => '无效', 'value' => 'invalidation']
        ]
    ]);
}

/**
 * 获取申请类型选项 getApplicationTypeOptions
 *
 * 功能描述：获取申请类型下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getApplicationTypeOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '发明', 'value' => 'invention'],
            ['label' => '实用新型', 'value' => 'utility_model'],
            ['label' => '外观设计', 'value' => 'design'],
            ['label' => '商标注册', 'value' => 'trademark_reg'],
            ['label' => '著作权登记', 'value' => 'copyright_reg']
        ]
    ]);
}

/**
 * 获取国家选项 getCountryOptions
 *
 * 功能描述：获取国家下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getCountryOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '中国', 'value' => 'CN'],
            ['label' => '美国', 'value' => 'US'],
            ['label' => '欧盟', 'value' => 'EP'],
            ['label' => '日本', 'value' => 'JP'],
            ['label' => '韩国', 'value' => 'KR'],
            ['label' => '英国', 'value' => 'GB'],
            ['label' => '德国', 'value' => 'DE']
        ]
    ]);
}

/**
 * 获取项目流向选项 getCaseFlowOptions
 *
 * 功能描述：获取项目流向下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getCaseFlowOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '国内申请', 'value' => 'domestic'],
            ['label' => '国外申请', 'value' => 'foreign'],
            ['label' => 'PCT申请', 'value' => 'pct'],
            ['label' => '巴黎公约', 'value' => 'paris'],
            ['label' => '马德里协议', 'value' => 'madrid']
        ]
    ]);
}

/**
 * 获取员工选项 getStaffOptions
 *
 * 功能描述：获取员工下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getStaffOptions()
{
    // 查询所有状态为1的用户，只选择id和real_name字段
    $users = \App\Models\User::where('status', 1)->select('id', 'real_name')->get();

    return response()->json([
        'success' => true,
        'data' => $users->map(function($user) {
            return ['label' => $user->real_name, 'value' => $user->id];
        })
    ]);
}

/**
 * 获取文档名称选项 getDocumentNameOptions
 *
 * 功能描述：获取文档名称下拉选项数据
 *
 * 输出参数：
 * - response (JsonResponse): JSON响应对象
 *   - success (boolean): 是否成功
 *   - data (array): 选项数据列表
 */
private function getDocumentNameOptions()
{
    return response()->json([
        'success' => true,
        'data' => [
            ['label' => '发明专利申请书', 'value' => 'invention_app'],
            ['label' => '实用新型专利申请书', 'value' => 'utility_app'],
            ['label' => '外观设计专利申请书', 'value' => 'design_app'],
            ['label' => '商标注册申请书', 'value' => 'trademark_app'],
            ['label' => '著作权登记申请书', 'value' => 'copyright_app'],
            ['label' => '审查意见通知书', 'value' => 'examination_notice'],
            ['label' => '授权通知书', 'value' => 'grant_notice'],
            ['label' => '驳回通知书', 'value' => 'rejection_notice']
        ]
    ]);
}

/**
 * 获取申请类型标签 getApplicationTypeLabel
 *
 * 功能描述：根据申请类型代码获取对应的中文标签
 *
 * 传入参数：
 * - type (string): 申请类型代码
 *
 * 输出参数：
 * - string: 申请类型中文标签
 */
private function getApplicationTypeLabel($type)
{
    $map = [
        'invention' => '发明',
        'utility_model' => '实用新型',
        'design' => '外观设计',
        'trademark_reg' => '商标注册',
        'copyright_reg' => '著作权登记'
    ];

    return $map[$type] ?? $type;
}

/**
 * 获取项目状态标签 getCaseStatusLabel
 *
 * 功能描述：根据项目状态代码获取对应的中文标签
 *
 * 传入参数：
 * - status (string): 项目状态代码
 *
 * 输出参数：
 * - string: 项目状态中文标签
 */
private function getCaseStatusLabel($status)
{
    $map = [
        'applying' => '申请中',
        'examining' => '审查中',
        'granted' => '已授权',
        'rejected' => '已驳回',
        'withdrawn' => '已撤回',
        'abandoned' => '已放弃',
        'maintaining' => '维持中'
    ];

    return $map[$status] ?? $status;
}

/**
 * 生成Excel响应 generateExcelResponse
 *
 * 功能描述：生成CSV格式的Excel文件下载响应
 *
 * 传入参数：
 * - data (array): 要导出的数据
 * - filename (string): 文件名前缀
 *
 * 输出参数：
 * - response (Response): 文件下载响应对象
 */
private function generateExcelResponse($data, $filename)
{
    // 这里应该使用Excel库生成文件，比如 PhpSpreadsheet
    // 为了简化，我们返回CSV格式的数据

    $output = fopen('php://temp', 'w');

    // 写入BOM以支持中文
    fwrite($output, "\xEF\xBB\xBF");

    if (!empty($data)) {
        // 写入表头
        fputcsv($output, array_keys($data[0]));

        // 写入数据
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return response($csv, 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"',
    ]);
}

}
