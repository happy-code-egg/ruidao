<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
    获取新申请待分配列表
    查询未分配的专利、商标、版权类新申请相关处理事项，支持分页与冻结状态筛选控制
    请求参数：
    page（页码）：可选，整数，默认 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 20，分页查询的每页记录数
    show_frozen（是否显示冻结事项）：可选，布尔值，默认 false，暂未实际生效（表中无 is_frozen 字段）
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，失败时返回错误描述，成功时无该字段
    data：对象，成功时返回列表数据及分页信息
    list：数组，待分配新申请列表，每个元素包含：
    id：整数，处理事项 ID
    caseNumber：字符串，案件编号（case_code）
    caseTitle：字符串，案件名称（case_name）
    caseType：字符串，案件类型文本（type_text）
    customerName：字符串，客户名称（关联客户表 customer_name）
    applyType：字符串，申请类型（application_type）
    processItem：字符串，处理事项名称（process_name）
    processor：字符串，处理人姓名（关联分配用户表 name）
    reviewer：字符串，审核人姓名（关联审核用户表 name）
    contractNo：字符串，合同编号（contract_number）
    createDate：字符串，创建时间，格式为 "Y-m-d"
    support：字符串，售前支持（presale_support）
    frozen：字符串，冻结状态，固定为 "否"（暂未关联实际字段）
    assignmentStatus：字符串，分配状态（通过 getAssignmentStatus 方法获取）
    officialDeadline：字符串，官方截止日期，格式为 "Y-m-d"
    internalDeadline：字符串，内部截止日期，格式为 "Y-m-d"
    total：整数，符合条件的总记录数
    current_page：整数，当前页码
    per_page：整数，每页条数
    last_page：整数，最后一页页码
    说明：
    仅查询案件类型为专利（TYPE_PATENT）、商标（TYPE_TRADEMARK）、版权（TYPE_COPYRIGHT）的记录
    筛选处理事项名称含 "新申请" 且未分配（assigned_to 为 null）的记录
    关联查询案件、客户、业务人员、分配用户、审核用户等关联数据
    按创建时间倒序排序，返回数据已做格式化处理
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function newApplications(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);
            $showFrozen = $request->input('show_frozen', false);

            // 查询新申请类型的处理事项（未分配的）
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) {
                // 只查询专利、商标、版权类型的案例
                $caseQuery->whereIn('case_type', [
                    Cases::TYPE_PATENT,
                    Cases::TYPE_TRADEMARK,
                    Cases::TYPE_COPYRIGHT
                ]);
            })
            ->where('process_name', 'like', '%新申请%') // 新申请类型的处理事项
            ->whereNull('assigned_to'); // 未分配的

            // 是否显示冻结事项 (暂时跳过，因为表中没有is_frozen字段)
            // if (!$showFrozen) {
            //     $query->where('is_frozen', '!=', 1);
            // }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'caseNumber' => $case->case_code ?? '',
                    'caseTitle' => $case->case_name ?? '',
                    'caseType' => $case->type_text,
                    'customerName' => $customer ? $customer->customer_name : '',
                    'applyType' => $case->application_type ?? '',
                    'processItem' => $process->process_name ?? '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'contractNo' => $case->contract_number ?? '',
                    'createDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'support' => $case->presale_support ?? '',
                    'frozen' => '否', // 暂时硬编码，因为表中没有is_frozen字段
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取中间案待分配列表
    查询未分配的专利、商标、版权类中间案（排除新申请）处理事项，支持多条件组合筛选与分页
    请求参数：
    基础筛选参数：
    client_name（客户名称）：可选，字符串，模糊匹配客户名称
    case_name（案件名称）：可选，字符串，模糊匹配案件名称
    project_name（项目名称）：可选，字符串，暂未用于筛选逻辑
    our_ref_number（我方参考号）：可选，字符串，模糊匹配案件编号（case_code）
    application_no（申请号）：可选，字符串，模糊匹配申请号
    application_type（申请类型）：可选，字符串，模糊匹配申请类型
    processing_item（处理事项）：可选，字符串，模糊匹配处理事项名称
    case_type（案件类型）：可选，字符串，支持 "patent"（专利）、"trademark"（商标）、"copyright"（版权）
    business_type（业务类型）：可选，字符串，模糊匹配处理类型（process_type）
    registration_no（注册号）：可选，字符串，模糊匹配注册号
    applicant（申请人）：可选，字符串，匹配申请人信息中的名称
    日期范围筛选参数（数组格式，需传递 2 个日期值）：
    official_deadline_range（官方截止日期范围）：可选，数组，格式为 [开始日期，结束日期]
    processing_create_date_range（处理事项创建日期范围）：可选，数组，格式为 [开始日期，结束日期]
    internal_deadline_range（内部截止日期范围）：可选，数组，格式为 [开始日期，结束日期]
    分页参数：
    page（页码）：可选，整数，默认 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 20，分页查询的每页记录数
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，失败时返回错误描述，成功时无该字段
    data：对象，成功时返回列表数据及分页信息
    list：数组，中间案待分配列表，每个元素包含：
    id：整数，处理事项 ID
    caseNumber：字符串，案件编号（case_code）
    caseName：字符串，案件名称（case_name）
    customerName：字符串，客户名称（关联客户表 customer_name）
    applicationType：字符串，申请类型（application_type）
    processingItem：字符串，处理事项名称（process_name）
    caseType：字符串，案件类型文本（type_text）
    businessType：字符串，业务类型（process_type）
    applicationNo：字符串，申请号（application_no）
    registrationNo：字符串，注册号（registration_no）
    applicant：字符串，申请人名称（通过 getApplicantName 方法解析 applicant_info 获取）
    officialDeadline：字符串，官方截止日期，格式为 "Y-m-d"
    internalDeadline：字符串，内部截止日期，格式为 "Y-m-d"
    processingCreateDate：字符串，处理事项创建日期，格式为 "Y-m-d"
    assignmentStatus：字符串，分配状态（通过 getAssignmentStatus 方法获取）
    processor：字符串，处理人姓名（关联分配用户表 name）
    reviewer：字符串，审核人姓名（关联审核用户表 name）
    total：整数，符合条件的总记录数
    current_page：整数，当前页码
    per_page：整数，每页条数
    last_page：整数，最后一页页码
    说明：
    仅查询案件类型为专利、商标、版权的记录，排除处理事项名称含 "新申请" 的记录
    筛选未分配（assigned_to 为 null）的处理事项，支持客户、案件、日期等多维度组合查询
    关联查询案件、客户、业务人员、技术负责人、分配用户、审核用户等关联数据
    按处理事项创建时间倒序排序，返回数据已做格式化处理
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function middleCases(Request $request)
    {
        try {
            // 获取查询参数
            $clientName = $request->input('client_name');
            $caseName = $request->input('case_name');
            $projectName = $request->input('project_name');
            $ourRefNumber = $request->input('our_ref_number');
            $applicationNo = $request->input('application_no');
            $officialDeadlineRange = $request->input('official_deadline_range', []);
            $processingCreateDateRange = $request->input('processing_create_date_range', []);
            $internalDeadlineRange = $request->input('internal_deadline_range', []);
            $applicationType = $request->input('application_type');
            $processingItem = $request->input('processing_item');
            $caseType = $request->input('case_type');
            $businessType = $request->input('business_type');
            $registrationNo = $request->input('registration_no');
            $applicant = $request->input('applicant');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询中间案（非新申请）的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson', 'techLeader']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) use (
                $clientName, $caseName, $ourRefNumber, $applicationNo,
                $applicationType, $caseType, $registrationNo, $applicant
            ) {
                // 只查询专利、商标、版权类型的案例
                $caseQuery->whereIn('case_type', [
                    Cases::TYPE_PATENT,
                    Cases::TYPE_TRADEMARK,
                    Cases::TYPE_COPYRIGHT
                ]);

                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($applicationNo) {
                    $caseQuery->where('application_no', 'like', "%{$applicationNo}%");
                }
                if ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                }
                if ($caseType) {
                    $typeMap = [
                        'patent' => Cases::TYPE_PATENT,
                        'trademark' => Cases::TYPE_TRADEMARK,
                        'copyright' => Cases::TYPE_COPYRIGHT
                    ];
                    if (isset($typeMap[$caseType])) {
                        $caseQuery->where('case_type', $typeMap[$caseType]);
                    }
                }
                if ($registrationNo) {
                    $caseQuery->where('registration_no', 'like', "%{$registrationNo}%");
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
            })
            ->where('process_name', 'not like', '%新申请%') // 排除新申请类型
            ->whereNull('assigned_to'); // 未分配的

            // 处理事项筛选
            if ($processingItem) {
                $query->where('process_name', 'like', "%{$processingItem}%");
            }

            // 业务类型筛选
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }

            // 日期范围筛选
            if (!empty($officialDeadlineRange) && count($officialDeadlineRange) == 2) {
                $query->whereBetween('official_deadline', $officialDeadlineRange);
            }
            if (!empty($processingCreateDateRange) && count($processingCreateDateRange) == 2) {
                $query->whereBetween('created_at', $processingCreateDateRange);
            }
            if (!empty($internalDeadlineRange) && count($internalDeadlineRange) == 2) {
                $query->whereBetween('internal_deadline', $internalDeadlineRange);
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'caseNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'applicationType' => $case->application_type ?? '',
                    'processingItem' => $process->process_name ?? '',
                    'caseType' => $case->type_text,
                    'businessType' => $process->process_type ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'registrationNo' => $case->registration_no ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'processingCreateDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取科服待分配列表
    查询未分配的科技服务类型案件处理事项，支持多条件组合筛选与分页查询
    请求参数：
    基础筛选参数：
    our_ref_number（我方参考号）：可选，字符串，模糊匹配案件编号（case_code）
    case_name（案件名称）：可选，字符串，模糊匹配案件名称
    client_name（客户名称）：可选，字符串，模糊匹配客户名称（关联客户表）
    business_type（业务类型）：可选，字符串，模糊匹配处理类型（process_type）
    application_type（申请类型）：可选，字符串，模糊匹配申请类型（关联案件表）
    applicant（申请人）：可选，字符串，匹配申请人信息中的名称
    project_processor（项目处理人）：可选，字符串，筛选指定分配人（assigned_to）的记录
    case_business_staff（案件业务人员）：可选，字符串，模糊匹配业务人员姓名（关联业务人员表）
    日期范围筛选参数：
    receiving_date_range（收单日期范围）：可选，数组，格式为 [开始日期，结束日期]，匹配处理事项创建时间
    分页参数：
    page（页码）：可选，整数，默认 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 20，分页查询的每页记录数
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，失败时返回错误描述，成功时无该字段
    data：对象，成功时返回列表数据及分页信息
    list：数组，科服待分配列表，每个元素包含：
    id：整数，处理事项 ID
    ourRefNumber：字符串，我方参考号（案件编号 case_code）
    caseName：字符串，案件名称（case_name）
    clientName：字符串，客户名称（关联客户表 customer_name）
    businessType：字符串，业务类型（process_type）
    applicationType：字符串，申请类型（案件表 application_type）
    applicant：字符串，申请人名称（通过 getApplicantName 方法解析 applicant_info 获取）
    projectProcessor：字符串，项目处理人姓名（关联分配用户表 name）
    receivingDate：字符串，收单日期（处理事项创建时间），格式为 "Y-m-d"
    caseBusinessStaff：字符串，案件业务人员姓名（关联业务人员表 name）
    assignmentStatus：字符串，分配状态（通过 getAssignmentStatus 方法获取）
    total：整数，符合条件的总记录数
    current_page：整数，当前页码
    per_page：整数，每页条数
    last_page：整数，最后一页页码
    说明：
    仅查询案件类型为科技服务（TYPE_TECH_SERVICE）的记录，且筛选未分配（assigned_to 为 null）的处理事项
    支持客户、业务类型、申请类型、日期范围等多维度组合查询，适配科服业务筛选需求
    关联查询案件、客户、业务人员、分配用户、审核用户等关联数据
    按处理事项创建时间倒序排序，返回数据已做格式化处理
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function techServiceCases(Request $request)
    {
        try {
            // 获取查询参数
            $ourRefNumber = $request->input('our_ref_number');
            $caseName = $request->input('case_name');
            $clientName = $request->input('client_name');
            $businessType = $request->input('business_type');
            $applicationType = $request->input('application_type');
            $applicant = $request->input('applicant');
            $projectProcessor = $request->input('project_processor');
            $receivingDateRange = $request->input('receiving_date_range', []);
            $caseBusinessStaff = $request->input('case_business_staff');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询科技服务类型的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) use (
                $ourRefNumber, $caseName, $clientName, $applicant, $caseBusinessStaff
            ) {
                // 只查询科技服务类型的案例
                $caseQuery->where('case_type', Cases::TYPE_TECH_SERVICE);

                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
                if ($caseBusinessStaff) {
                    $caseQuery->whereHas('businessPerson', function($businessQuery) use ($caseBusinessStaff) {
                        $businessQuery->where('name', 'like', "%{$caseBusinessStaff}%");
                    });
                }
            })
            ->whereNull('assigned_to'); // 未分配的

            // 业务类型筛选
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }

            // 申请类型筛选
            if ($applicationType) {
                $query->whereHas('case', function($caseQuery) use ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                });
            }

            // 项目处理人筛选
            if ($projectProcessor) {
                $query->where('assigned_to', $projectProcessor);
            }

            // 收单日期范围筛选
            if (!empty($receivingDateRange) && count($receivingDateRange) == 2) {
                $query->whereBetween('created_at', $receivingDateRange);
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'ourRefNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'clientName' => $customer ? $customer->customer_name : '',
                    'businessType' => $process->process_type ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'projectProcessor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'receivingDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'caseBusinessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    批量分配处理事项
    支持一次性将多个案件处理事项分配给指定处理人，并可指定审核人，同步更新事项状态
    请求参数：
    process_ids（处理事项 ID 列表）：必填，数组，需包含至少一个待分配的处理事项 ID
    assigned_to（处理人 ID）：必填，字符串 / 整数，指定接收分配的处理人标识
    reviewer（审核人 ID）：可选，字符串 / 整数，指定处理事项的审核人标识
    返回参数：
    success：布尔值，true 表示分配成功，false 表示分配失败
    message：字符串，操作结果描述（如 "分配成功"、"请选择要分配的事项" 等）
    说明：
    必传参数校验：process_ids 为空或 assigned_to 未传递时，返回 400 错误及对应提示
    事务保障：分配操作通过数据库事务执行，失败时自动回滚，避免数据不一致
    状态更新：分配成功后，处理事项状态会设为 "待处理"（CaseProcess::STATUS_PENDING）
    记录跟踪：自动填充更新时间（updated_at）和更新人 ID（updated_by，优先取当前登录用户 ID，默认 1）
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function batchAssign(Request $request)
    {
        try {
            $processIds = $request->input('process_ids', []);
            $assignedTo = $request->input('assigned_to');
            $reviewer = $request->input('reviewer');

            if (empty($processIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要分配的事项'
                ], 400);
            }

            if (!$assignedTo) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择处理人'
                ], 400);
            }

            DB::beginTransaction();

            // 更新处理事项的分配信息
            CaseProcess::whereIn('id', $processIds)->update([
                'assigned_to' => $assignedTo,
                'reviewer' => $reviewer,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_at' => now(),
                'updated_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '分配成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '分配失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    直接分配（单个处理事项分配）
    针对单个案件处理事项进行分配，指定处理人及可选审核人，同步更新事项状态
    请求参数：
    process_id（处理事项 ID）：必填，整数 / 字符串，指定待分配的单个处理事项唯一标识
    assigned_to（处理人 ID）：必填，整数 / 字符串，接收分配的处理人标识
    reviewer（审核人 ID）：可选，整数 / 字符串，处理事项的审核人标识
    返回参数：
    success：布尔值，true 表示分配成功，false 表示分配失败
    message：字符串，操作结果描述（如 "分配成功"、"参数不完整"、"处理事项不存在" 等）
    说明：
    参数校验：process_id 或 assigned_to 未传递时，返回 400 参数不完整错误
    记录校验：查询不到对应 process_id 的处理事项时，返回 404 不存在错误
    状态更新：分配成功后，处理事项状态设为 "待处理"（CaseProcess::STATUS_PENDING）
    记录跟踪：自动填充更新人 ID（updated_by，优先取当前登录用户 ID，默认 1）
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function directAssign(Request $request)
    {
        try {
            $processId = $request->input('process_id');
            $assignedTo = $request->input('assigned_to');
            $reviewer = $request->input('reviewer');

            if (!$processId || !$assignedTo) {
                return response()->json([
                    'success' => false,
                    'message' => '参数不完整'
                ], 400);
            }

            $process = CaseProcess::find($processId);
            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            // 更新分配信息
            $process->update([
                'assigned_to' => $assignedTo,
                'reviewer' => $reviewer,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_by' => auth()->id() ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => '分配成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '分配失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    撤回分配
    批量撤销已分配的案件处理事项，清除处理人、审核人信息并重置状态
    请求参数：
    process_ids（处理事项 ID 列表）：必填，数组，需包含至少一个待撤回分配的处理事项 ID
    返回参数：
    success：布尔值，true 表示撤回成功，false 表示撤回失败
    message：字符串，操作结果描述（如 "撤回成功"、"请选择要撤回的事项" 等）
    说明：
    参数校验：process_ids 为空时，返回 400 错误及对应提示
    事务保障：撤回操作通过数据库事务执行，失败时自动回滚，保障数据一致性
    信息清除：撤回后将 assigned_to（处理人）、reviewer（审核人）设为 null
    状态重置：处理事项状态保持为 "待处理"（CaseProcess::STATUS_PENDING）
    记录跟踪：自动填充更新时间（updated_at）和更新人 ID（updated_by，优先取当前登录用户 ID，默认 1）
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function withdrawAssignment(Request $request)
    {
        try {
            $processIds = $request->input('process_ids', []);

            if (empty($processIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要撤回的事项'
                ], 400);
            }

            DB::beginTransaction();

            // 清除分配信息
            CaseProcess::whereIn('id', $processIds)->update([
                'assigned_to' => null,
                'reviewer' => null,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_at' => now(),
                'updated_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '撤回成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '撤回失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取已分配列表
    查询已分配的案件处理事项，支持多条件组合筛选与分页，涵盖各类案件类型
    请求参数：
    基础筛选参数：
    our_ref_number（我方参考号）：可选，字符串，模糊匹配案件编号（case_code）
    case_name（案件名称）：可选，字符串，模糊匹配案件名称
    client_name（客户名称）：可选，字符串，模糊匹配客户名称（关联客户表）
    business_type（业务类型）：可选，字符串，模糊匹配处理类型（process_type）
    application_type（申请类型）：可选，字符串，模糊匹配申请类型（关联案件表）
    applicant（申请人）：可选，字符串，匹配申请人信息中的名称
    project_processor（项目处理人）：可选，字符串，筛选指定处理人（assigned_to）的记录
    case_business_staff（案件业务人员）：可选，字符串，模糊匹配业务人员姓名（关联业务人员表）
    日期范围筛选参数：
    receiving_date_range（收单日期范围）：可选，数组，格式为 [开始日期，结束日期]，匹配处理事项创建时间
    分页参数：
    page（页码）：可选，整数，默认 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 20，分页查询的每页记录数
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，失败时返回错误描述，成功时无该字段
    data：对象，成功时返回列表数据及分页信息
    list：数组，已分配列表，每个元素包含：
    id：整数，处理事项 ID
    ourRefNumber：字符串，我方参考号（案件编号 case_code）
    caseName：字符串，案件名称（case_name）
    clientName：字符串，客户名称（关联客户表 customer_name）
    businessType：字符串，业务类型（process_type）
    applicationType：字符串，申请类型（案件表 application_type）
    applicant：字符串，申请人名称（通过 getApplicantName 方法解析 applicant_info 获取）
    projectProcessor：字符串，项目处理人姓名（关联分配用户表 name）
    reviewer：字符串，审核人姓名（关联审核用户表 name）
    receivingDate：字符串，收单日期（处理事项创建时间），格式为 "Y-m-d"
    assignmentDate：字符串，分配日期（处理事项更新时间），格式为 "Y-m-d"
    caseBusinessStaff：字符串，案件业务人员姓名（关联业务人员表 name）
    assignmentStatus：字符串，分配状态（通过 getAssignmentStatus 方法获取）
    processStatus：字符串，处理状态文本（status_text）
    total：整数，符合条件的总记录数
    current_page：整数，当前页码
    per_page：整数，每页条数
    last_page：整数，最后一页页码
    说明：
    仅查询已分配（assigned_to 不为 null）的处理事项，支持全类型案件查询
    筛选条件与科服待分配列表一致，适配已分配事项的多维度检索需求
    关联查询案件、客户、业务人员、分配用户、审核用户等关联数据
    按处理事项更新时间（分配时间）倒序排序，返回数据已做格式化处理
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function assignedCases(Request $request)
    {
        try {
            // 获取查询参数
            $ourRefNumber = $request->input('our_ref_number');
            $caseName = $request->input('case_name');
            $clientName = $request->input('client_name');
            $businessType = $request->input('business_type');
            $applicationType = $request->input('application_type');
            $applicant = $request->input('applicant');
            $projectProcessor = $request->input('project_processor');
            $receivingDateRange = $request->input('receiving_date_range', []);
            $caseBusinessStaff = $request->input('case_business_staff');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询已分配的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereNotNull('assigned_to'); // 已分配的

            // 应用筛选条件
            $query->whereHas('case', function($caseQuery) use (
                $ourRefNumber, $caseName, $clientName, $applicant, $caseBusinessStaff
            ) {
                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
                if ($caseBusinessStaff) {
                    $caseQuery->whereHas('businessPerson', function($businessQuery) use ($caseBusinessStaff) {
                        $businessQuery->where('name', 'like', "%{$caseBusinessStaff}%");
                    });
                }
            });

            // 其他筛选条件
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }
            if ($applicationType) {
                $query->whereHas('case', function($caseQuery) use ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                });
            }
            if ($projectProcessor) {
                $query->where('assigned_to', $projectProcessor);
            }
            if (!empty($receivingDateRange) && count($receivingDateRange) == 2) {
                $query->whereBetween('created_at', $receivingDateRange);
            }

            // 排序
            $query->orderBy('updated_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'ourRefNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'clientName' => $customer ? $customer->customer_name : '',
                    'businessType' => $process->process_type ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'projectProcessor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'receivingDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'assignmentDate' => $process->updated_at ? $process->updated_at->format('Y-m-d') : '',
                    'caseBusinessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'processStatus' => $process->status_text,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取处理事项详情
    根据处理事项 ID 查询完整详情，包含处理事项本身、关联案件及客户的全量信息
    请求参数：
    id（处理事项 ID）：必填，整数 / 字符串，通过 URL 路径传递，指定待查询的处理事项唯一标识
    返回参数：
    success：布尔值，true 表示查询成功，false 表示查询失败
    message：字符串，失败时返回错误描述（如 "处理事项不存在" 等），成功时无该字段
    data：对象，查询成功时返回完整详情，包含三大模块：
    处理事项基础信息：
    id：整数，处理事项 ID
    process_code：字符串，处理事项编码
    process_name：字符串，处理事项名称
    process_type：字符串，处理事项类型
    process_status：整数，处理事项状态码
    priority_level：字符串 / 整数，优先级等级
    case_stage：字符串，案件阶段
    due_date：字符串，到期日期（格式 "Y-m-d"）
    internal_deadline：字符串，内部截止日期（格式 "Y-m-d"）
    official_deadline：字符串，官方截止日期（格式 "Y-m-d"）
    completion_date：字符串，完成日期（格式 "Y-m-d"）
    issue_date：字符串，签发日期（格式 "Y-m-d"）
    assigned_to：整数 / 字符串，处理人 ID
    assignee：字符串，处理人相关标识（冗余字段）
    reviewer：整数 / 字符串，审核人 ID
    processor：字符串，处理人姓名（关联用户表 name）
    reviewer_name：字符串，审核人姓名（关联用户表 name）
    process_coefficient：字符串 / 数值，处理系数
    process_description：字符串，处理事项描述
    created_at：字符串，创建时间（格式 "Y-m-d H:i:s"）
    updated_at：字符串，更新时间（格式 "Y-m-d H:i:s"）
    assignment_status：字符串，分配状态（通过 getAssignmentStatus 方法获取）
    关联案件信息（case 对象）：
    id：整数，案件 ID
    case_code：字符串，案件编号
    case_name：字符串，案件名称
    case_type：整数，案件类型码
    type_text：字符串，案件类型文本
    case_status：整数，案件状态码
    application_type：字符串，申请类型
    application_no：字符串，申请号
    application_date：字符串，申请日期（格式 "Y-m-d"）
    country_code：字符串，国家 / 地区编码
    case_phase：字符串，案件阶段
    applicant_info：数组，申请人信息（JSON 解析后）
    contract_number：字符串，合同编号
    presale_support：字符串，售前支持
    business_person_id：整数，业务人员 ID
    tech_leader：字符串 / 整数，技术负责人标识
    关联客户信息（customer 对象，可能为 null）：
    id：整数，客户 ID
    customer_name：字符串，客户名称
    customer_code：字符串，客户编码
    contact_person：字符串，联系人
    contact_phone：字符串，联系电话
    contact_email：字符串，联系邮箱
    address：字符串，客户地址
    说明：
    先校验处理事项是否存在，不存在返回 404 错误
    关联查询案件（含客户）、处理人、审核人数据，一次性返回全量详情
    日期字段统一格式化为 "Y-m-d" 或 "Y-m-d H:i:s"，申请人信息解析为数组格式
    支持处理人、审核人姓名的直接返回，无需前端二次关联查询
    @param int/string $id 处理事项 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function getProcessDetail($id)
    {
        try {
            $process = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->find($id);

            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            $case = $process->case;
            $customer = $case->customer;

            $data = [
                'id' => $process->id,
                'process_code' => $process->process_code,
                'process_name' => $process->process_name,
                'process_type' => $process->process_type,
                'process_status' => $process->process_status,
                'priority_level' => $process->priority_level,
                'case_stage' => $process->case_stage,
                'due_date' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                'internal_deadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                'official_deadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                'completion_date' => $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
                'issue_date' => $process->issue_date ? $process->issue_date->format('Y-m-d') : '',
                'assigned_to' => $process->assigned_to,
                'assignee' => $process->assignee,
                'reviewer' => $process->reviewer,
                'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                'reviewer_name' => $process->reviewerUser ? $process->reviewerUser->name : '',
                'process_coefficient' => $process->process_coefficient,
                'process_description' => $process->process_description,
                'created_at' => $process->created_at ? $process->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i:s') : '',
                // 案件信息
                'case' => [
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_name' => $case->case_name,
                    'case_type' => $case->case_type,
                    'type_text' => $case->type_text,
                    'case_status' => $case->case_status,
                    'application_type' => $case->application_type,
                    'application_no' => $case->application_no,
                    'application_date' => $case->application_date ? $case->application_date->format('Y-m-d') : '',
                    'country_code' => $case->country_code,
                    'case_phase' => $case->case_phase,
                    'applicant_info' => $case->applicant_info ? json_decode($case->applicant_info, true) : null,
                    'contract_number' => $case->contract_number,
                    'presale_support' => $case->presale_support,
                    'business_person_id' => $case->business_person_id,
                    'tech_leader' => $case->tech_leader
                ],
                // 客户信息
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'customer_name' => $customer->customer_name,
                    'customer_code' => $customer->customer_code,
                    'contact_person' => $customer->contact_person,
                    'contact_phone' => $customer->contact_phone,
                    'contact_email' => $customer->contact_email,
                    'address' => $customer->address
                ] : null,
                'assignment_status' => $this->getAssignmentStatus($process)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取可分配的用户列表
    查询状态为启用的用户，格式化为前端下拉选择所需结构，用于处理事项分配时选择处理人 / 审核人
    请求参数：无
    返回参数：
    success：布尔值，true 表示查询成功，false 表示查询失败
    message：字符串，失败时返回错误描述，成功时无该字段
    data：数组，可分配用户列表，每个元素包含：
    id：整数，用户 ID（用于分配时的标识关联）
    name：字符串，用户真实姓名（real_name，用于前端显示）
    email：字符串，用户邮箱
    department：整数，部门 ID（关联部门表标识）
    说明：
    仅返回状态为启用（status=1）的用户，确保分配对象为有效用户
    按用户真实姓名（real_name）升序排序，方便前端选择时检索
    字段格式已适配前端下拉组件需求，无需二次转换
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function getAssignableUsers(Request $request)
    {
        try {
            $users = User::where('status', 1)
                        ->select('id', 'real_name', 'email', 'department_id')
                        ->orderBy('real_name')
                        ->get();

        // 转换为前端需要的格式
        $users = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->real_name,
                'email' => $user->email,
                'department' => $user->department_id
            ];
        });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取用户列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    获取处理事项的分配状态文本
    根据处理事项的分配人（assigned_to）和处理状态（process_status），返回对应的中文状态描述，用于前端展示
    状态判断逻辑：
    分配人（assigned_to）为 null → 返回 "未分配"
    处理状态为 "待处理"（CaseProcess::STATUS_PENDING） → 返回 "已分配"
    处理状态为 "处理中"（CaseProcess::STATUS_PROCESSING） → 返回 "处理中"
    处理状态为 "已完成"（CaseProcess::STATUS_COMPLETED） → 返回 "已完成"
    其他情况 → 返回 "未知状态"
    @param \App\Models\CaseProcess $process 处理事项模型实例
    @return string 分配状态中文文本描述
     */
    private function getAssignmentStatus($process)
    {
        if (is_null($process->assigned_to)) {
            return '未分配';
        } elseif ($process->process_status == CaseProcess::STATUS_PENDING) {
            return '已分配';
        } elseif ($process->process_status == CaseProcess::STATUS_PROCESSING) {
            return '处理中';
        } elseif ($process->process_status == CaseProcess::STATUS_COMPLETED) {
            return '已完成';
        } else {
            return '未知状态';
        }
    }

    /**
    解析申请人名称
    处理申请人信息的多种格式（字符串 / 数组），提取并返回申请人名称，适配不同数据存储场景
    解析逻辑：
    若申请人信息（$applicantInfo）为字符串 → 直接返回该字符串
    若为数组且包含 "name" 键 → 返回该 "name" 键对应的 value
    若为非空数组（无直接 "name" 键） → 取数组第一个元素，若该元素为数组且含 "name" 键，返回其 value
    其他情况（空数组、不满足上述格式） → 返回空字符串
    @param string|array $applicantInfo 申请人信息（可能为字符串或数组格式）
    @return string 提取到的申请人名称，提取失败则返回空字符串*/
    private function getApplicantName($applicantInfo)
    {
        if (is_string($applicantInfo)) {
            return $applicantInfo;
        }

        if (is_array($applicantInfo) && isset($applicantInfo['name'])) {
            return $applicantInfo['name'];
        }

        if (is_array($applicantInfo) && !empty($applicantInfo)) {
            $first = reset($applicantInfo);
            if (is_array($first) && isset($first['name'])) {
                return $first['name'];
            }
        }

        return '';
    }
}
