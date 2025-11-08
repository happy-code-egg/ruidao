<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\CaseFee;
use App\Models\User;
use App\Models\Customer;
use App\Exports\ItemMonitorExport;
use App\Exports\FeeMonitorExport;
use App\Exports\AbnormalFeeExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CaseMonitorController extends Controller
{
    /**
    事项监控（查询案例处理事项列表）
    功能说明：
    多维度筛选案例关联的处理事项，支持案例信息、处理事项信息、日期范围等组合查询
    关联案例、客户、用户等表获取完整信息，格式化时间字段为标准格式（Y-m-d）
    分页返回结构化数据，适配前端表格展示，异常时返回具体错误信息
    请求参数：
    分页参数：
    page（页码）：可选，整数，默认 1，指定分页页码
    limit（每页条数）：可选，整数，默认 20，指定单页返回记录数
    案例相关筛选（均为可选）：
    our_ref_number（我方参考号）：字符串，模糊匹配案例编码（case_code）
    application_no（申请号）：字符串，模糊匹配案例申请号
    registration_no（注册号）：字符串，模糊匹配案例注册号
    client_name（客户名称）：字符串，模糊匹配关联客户的名称
    applicant（申请人）：字符串，模糊匹配案例申请人信息（applicant_info）
    case_name（案例名称）：字符串，模糊匹配案例名称
    case_type（案例类型）：字符串，可选值 "专利"“商标”“版权”“科服”，映射为对应数值类型
    application_type（申请类型）：字符串，模糊匹配案例申请类型
    application_country（申请国家）：字符串，精准匹配案例国家代码（country_code）
    agency_type（代理类型）：字符串，可选值 "我司代理"“客户直接申请”“其他代理”，按 agency_id 筛选
    case_status（案例状态）：字符串，可选值 "待处理"“处理中”“已完成”，映射为对应数值状态
    application_date_range（申请日期范围）：数组，长度为 2，格式为 [开始日期，结束日期]，匹配案例申请日期区间
    人员相关筛选（均为可选）：
    business_staff（业务人员）：字符串，模糊匹配案例关联的业务人员姓名
    technical_lead（技术负责人）：字符串，模糊匹配案例关联的技术负责人姓名
    item_responsible（事项负责人）：字符串，模糊匹配处理事项的分配人员姓名
    处理事项相关筛选（均为可选）：
    processing_item_type（处理事项类型）：字符串，模糊匹配处理事项类型（process_type）
    processing_item（处理事项）：字符串，模糊匹配处理事项名称（process_name）
    processing_status（处理事项状态）：字符串，可选值 "待处理"“处理中”“已完成”，映射为对应数值状态
    日期范围筛选（均为可选，数组格式 [开始日期，结束日期]）：
    receive_date_range（接收日期范围）：匹配处理事项接收日期（issue_date）区间
    internal_deadline_range（内部截止日期范围）：匹配内部截止日期（internal_deadline）区间
    client_deadline_range（客户截止日期范围）：匹配客户截止日期（customer_deadline）区间
    official_deadline_range（官方截止日期范围）：匹配官方截止日期（official_deadline）区间
    opening_date_range（创建日期范围）：匹配处理事项创建日期（created_at）区间
    documentation_date_range（完成日期范围）：匹配处理事项完成日期（completion_date）区间
    processing_created_date_range（处理创建日期范围）：匹配处理事项创建日期（created_at）区间
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，失败时返回 "查询失败 + 具体错误信息"
    data（列表数据）：对象，包含以下字段：
    list（处理事项列表）：数组，每个元素为格式化后的处理事项信息，包含：
    基础标识：id（处理事项 ID）、ourRefNumber（案例编码）等
    案例信息：clientName（客户名称）、caseType（案例类型文本）等
    处理事项信息：processingItem（事项名称）、processingStatus（事项状态文本）等
    人员信息：processingResponsible（事项负责人）、businessStaff（业务人员）等
    时间信息：officialDeadline（官方截止日期）、receiveDate（接收日期）等（均为 Y-m-d 格式）
    total（总条数）：整数，符合筛选条件的处理事项总记录数
    current_page（当前页码）：整数，当前返回数据的分页页码
    per_page（每页条数）：整数，当前分页的单页数据量
    last_page（总页数）：整数，按当前分页参数计算的总页数
    错误状态码：
    500：服务器内部错误（如数据库查询异常等）
    @param Request $request 请求对象，包含筛选条件和分页参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含处理事项列表及分页信息
     **/
    public function itemMonitor(Request $request)
    {
        try {
            // 获取查询参数
            $ourRefNumber = $request->input('our_ref_number');
            $applicationNo = $request->input('application_no');
            $registrationNo = $request->input('registration_no');
            $businessStaff = $request->input('business_staff');
            $clientName = $request->input('client_name');
            $applicant = $request->input('applicant');
            $caseName = $request->input('case_name');
            $technicalLead = $request->input('technical_lead');
            $caseType = $request->input('case_type');
            $applicationType = $request->input('application_type');
            $applicationCountry = $request->input('application_country');
            $agencyType = $request->input('agency_type');
            $processingItemType = $request->input('processing_item_type');
            $processingItem = $request->input('processing_item');
            $caseStatus = $request->input('case_status');
            $processingStatus = $request->input('processing_status');
            $itemResponsible = $request->input('item_responsible');

            // 日期范围参数
            $receiveDateRange = $request->input('receive_date_range', []);
            $internalDeadlineRange = $request->input('internal_deadline_range', []);
            $clientDeadlineRange = $request->input('client_deadline_range', []);
            $officialDeadlineRange = $request->input('official_deadline_range', []);
            $openingDateRange = $request->input('opening_date_range', []);
            $documentationDateRange = $request->input('documentation_date_range', []);
            $applicationDateRange = $request->input('application_date_range', []);
            $processingCreatedDateRange = $request->input('processing_created_date_range', []);

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 构建基础查询 - 查询处理事项及关联的案例信息
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson', 'techLeader']);
                },
                'assignedUser',
                'assigneeUser'
            ]);

            // 通过案例信息筛选
            $query->whereHas('case', function($caseQuery) use (
                $ourRefNumber, $applicationNo, $registrationNo, $clientName,
                $applicant, $caseName, $technicalLead, $caseType, $applicationType,
                $applicationCountry, $agencyType, $caseStatus, $applicationDateRange
            ) {
                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($applicationNo) {
                    $caseQuery->where('application_no', 'like', "%{$applicationNo}%");
                }
                if ($registrationNo) {
                    $caseQuery->where('registration_no', 'like', "%{$registrationNo}%");
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
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($technicalLead) {
                    $caseQuery->whereHas('techLeader', function($techQuery) use ($technicalLead) {
                        $techQuery->where('name', 'like', "%{$technicalLead}%");
                    });
                }
                if ($caseType) {
                    $typeMap = [
                        '专利' => Cases::TYPE_PATENT,
                        '商标' => Cases::TYPE_TRADEMARK,
                        '版权' => Cases::TYPE_COPYRIGHT,
                        '科服' => Cases::TYPE_TECH_SERVICE
                    ];
                    if (isset($typeMap[$caseType])) {
                        $caseQuery->where('case_type', $typeMap[$caseType]);
                    }
                }
                if ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                }
                if ($applicationCountry) {
                    $caseQuery->where('country_code', $applicationCountry);
                }
                if ($agencyType) {
                    // 根据代理机构类型筛选
                    switch ($agencyType) {
                        case '我司代理':
                            $caseQuery->where('agency_id', '>', 0);
                            break;
                        case '客户直接申请':
                            $caseQuery->whereNull('agency_id');
                            break;
                        case '其他代理':
                            $caseQuery->where('agency_id', '<', 0);
                            break;
                    }
                }
                if ($caseStatus) {
                    $statusMap = [
                        '待处理' => Cases::STATUS_SUBMITTED,
                        '处理中' => Cases::STATUS_PROCESSING,
                        '已完成' => Cases::STATUS_COMPLETED
                    ];
                    if (isset($statusMap[$caseStatus])) {
                        $caseQuery->where('case_status', $statusMap[$caseStatus]);
                    }
                }
                // 申请日期范围
                if (!empty($applicationDateRange) && count($applicationDateRange) == 2) {
                    $caseQuery->whereBetween('application_date', $applicationDateRange);
                }
            });

            // 业务人员筛选
            if ($businessStaff) {
                $query->whereHas('case.businessPerson', function($businessQuery) use ($businessStaff) {
                    $businessQuery->where('name', 'like', "%{$businessStaff}%");
                });
            }

            // 处理事项类型筛选
            if ($processingItemType) {
                $query->where('process_type', 'like', "%{$processingItemType}%");
            }

            // 处理事项筛选
            if ($processingItem) {
                $query->where('process_name', 'like', "%{$processingItem}%");
            }

            // 处理事项状态筛选
            if ($processingStatus) {
                $statusMap = [
                    '待处理' => CaseProcess::STATUS_PENDING,
                    '处理中' => CaseProcess::STATUS_PROCESSING,
                    '已完成' => CaseProcess::STATUS_COMPLETED
                ];
                if (isset($statusMap[$processingStatus])) {
                    $query->where('process_status', $statusMap[$processingStatus]);
                }
            }

            // 处理事项处理人筛选
            if ($itemResponsible) {
                $query->whereHas('assignedUser', function($assignedQuery) use ($itemResponsible) {
                    $assignedQuery->where('name', 'like', "%{$itemResponsible}%");
                });
            }

            // 日期范围筛选
            if (!empty($receiveDateRange) && count($receiveDateRange) == 2) {
                $query->whereBetween('issue_date', $receiveDateRange);
            }
            if (!empty($internalDeadlineRange) && count($internalDeadlineRange) == 2) {
                $query->whereBetween('internal_deadline', $internalDeadlineRange);
            }
            if (!empty($clientDeadlineRange) && count($clientDeadlineRange) == 2) {
                $query->whereBetween('customer_deadline', $clientDeadlineRange);
            }
            if (!empty($officialDeadlineRange) && count($officialDeadlineRange) == 2) {
                $query->whereBetween('official_deadline', $officialDeadlineRange);
            }
            if (!empty($openingDateRange) && count($openingDateRange) == 2) {
                $query->whereBetween('created_at', $openingDateRange);
            }
            if (!empty($documentationDateRange) && count($documentationDateRange) == 2) {
                $query->whereBetween('completion_date', $documentationDateRange);
            }
            if (!empty($processingCreatedDateRange) && count($processingCreatedDateRange) == 2) {
                $query->whereBetween('created_at', $processingCreatedDateRange);
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
                    'clientName' => $customer ? $customer->customer_name : '',
                    'caseType' => $case->type_text,
                    'applicationType' => $case->application_type ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'registrationNo' => $case->registration_no ?? '',
                    'processingItem' => $process->process_name ?? '',
                    'casePhase' => $case->case_phase ?? '',
                    'processingStatus' => $process->status_text,
                    'processingResponsible' => $process->assignedUser ? $process->assignedUser->name : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'receiveDate' => $process->issue_date ? $process->issue_date->format('Y-m-d') : '',
                    'sendDate' => $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
                    'businessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'applicationDate' => $case->application_date ? $case->application_date->format('Y-m-d') : '',
                    'category' => $case->trademark_category ?? '',
                    'technicalLead' => $case->techLeader ? $case->techLeader->name : '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
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
    官费监控列表查询
    功能说明：
    支持项目费用管理、客户费用查询、专利官费查询等多场景费用监控
    整合多维度筛选条件，统计核心费用指标，分页返回格式化列表数据
    异常时记录错误日志，返回具体失败信息，保障查询稳定性
    请求参数（均为可选）：
    分页参数：
    page：页码，整数，默认 1
    limit/pageSize：每页条数，整数，默认 20
    筛选参数：复用 buildFeeQuery 方法支持的所有筛选条件（如项目编码、客户名称、费用类型等）
    核心逻辑：
    查询构建：调用 buildFeeQuery 方法整合多维度筛选条件，生成基础查询
    排序规则：按费用记录创建时间（created_at）降序排列，最新数据优先展示
    费用统计：基于基础查询独立计算核心指标（总金额、已付 / 未付金额等），避免分页影响统计结果
    分页查询：使用 paginate 方法实现分页，自动处理总条数、总页数等分页参数
    数据格式化：
    关联项目、客户、合同等关联数据，补充名称、编码等展示字段
    拆分官费 / 服务费金额，统一货币单位默认值（CNY）
    转换状态码为文本描述（案例状态、支付状态、收款状态）
    兼容前端多字段名需求（如 ourNumber/ourRef、salesperson/businessStaff）
    统计指标说明：
    totalAmount：符合条件的费用总金额
    paidAmount：支付状态为 “已付”（CaseFee::STATUS_PAID）的金额总和
    unpaidAmount：支付状态为 “未付”（CaseFee::STATUS_UNPAID）的金额总和
    requestedAmount：预留字段，需后续关联请款单表补充统计
    receivedAmount：已记录实际收款日期（actual_receive_date 不为空）的金额总和
    invoicedAmount：预留字段，需后续关联发票表补充统计
    返回参数：
    success：布尔值，true 表示成功，false 表示失败
    message：字符串，返回 “获取列表成功” 或 “查询失败 + 具体错误信息”
    data：对象，包含以下字段：
    list：数组，格式化后的费用监控列表，含项目、客户、费用、状态等完整信息
    total/limit/page/pages：分页核心参数（总条数、每页条数、当前页码、总页数）
    stats：对象，费用核心统计指标
    依赖说明：
    依赖 buildFeeQuery 方法构建筛选条件
    依赖 getCaseStatusText 方法转换案例状态为文本
    依赖 getPaymentStatusText 方法转换支付状态为文本
    依赖 CaseFee 模型的 STATUS_PAID/STATUS_UNPAID 常量定义支付状态
    依赖 CaseFee 与案例、客户、合同等模型的关联关系
    @param Request $request 请求对象，包含筛选参数和分页参数
    @return \Illuminate\Http\JsonResponse 费用监控列表、分页信息及统计指标响应
     */
    public function feeMonitor(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit') ?? $request->input('pageSize', 20);

            // 使用统一的查询构建方法
            $query = $this->buildFeeQuery($request);

            // 排序
            $query->orderBy('created_at', 'desc');

            // 计算统计数据 - 使用独立查询避免clone问题,不使用表前缀
            $baseQuery = $this->buildFeeQuery($request);
            $stats = [
                'totalAmount' => (clone $baseQuery)->sum('amount'),
                'paidAmount' => (clone $baseQuery)->where('payment_status', CaseFee::STATUS_PAID)->sum('amount'),
                'unpaidAmount' => (clone $baseQuery)->where('payment_status', CaseFee::STATUS_UNPAID)->sum('amount'),
                'requestedAmount' => 0, // 需要从请款单中统计
                'receivedAmount' => (clone $baseQuery)->whereNotNull('actual_receive_date')->sum('amount'),
                'invoicedAmount' => 0, // 需要从发票中统计
            ];

            // 分页
            $fees = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($fees->items() as $fee) {
                $case = $fee->case;
                if (!$case) continue;

                $customer = $case->customer;
                $contract = $case->contract;

                $tableData[] = [
                    'id' => $fee->id,
                    'ourNumber' => $case->case_code ?? '',
                    'ourRef' => $case->case_code ?? '',
                    'clientNumber' => $customer ? $customer->customer_code : '',
                    'clientName' => $customer ? ($customer->customer_name ?? $customer->company_name) : '',
                    'caseName' => $case->case_name ?? '',
                    'caseType' => $case->application_type ?? '',
                    'technologyMainNumber' => $case->tech_leader ?? '',
                    'techLead' => $case->tech_leader ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'registrationNo' => $case->registration_no ?? '',
                    'applicationDate' => $case->application_date ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'handleItem' => '', // 需要从处理事项中获取
                    'taskItem' => '', // 处理事项
                    'feeStage' => $case->case_phase ?? '',
                    'feeType' => $fee->fee_type ?? '',
                    'feeName' => $fee->fee_name ?? '',
                    'officialFee' => $fee->fee_type === 'official' ? $fee->amount : 0,
                    'serviceFee' => $fee->fee_type === 'service' ? $fee->amount : 0,
                    'totalAmount' => $fee->amount ?? 0,
                    'receivableAmount' => $fee->amount ?? 0,
                    'feeCity' => $fee->currency ?? 'CNY',
                    'currency' => $fee->currency ?? 'CNY',
                    'actualReceiveDate' => $fee->actual_receive_date ?? '',
                    'actualReceivedDate' => $fee->actual_receive_date ?? '',
                    'payableDate' => $fee->receivable_date ?? '',
                    'actualPayDate' => '', // 实付日期
                    'paymentDeadline' => $fee->payment_deadline ?? '',
                    'caseStatus' => $this->getCaseStatusText($case->case_status),
                    'caseSalesperson' => $case->businessPerson ? $case->businessPerson->name : '',
                    'salesperson' => $case->businessPerson ? $case->businessPerson->name : '',
                    'businessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'contractNumber' => $contract ? $contract->contract_no : '',
                    'contractNo' => $contract ? $contract->contract_no : '',
                    'paymentStatus' => $this->getPaymentStatusText($fee->payment_status),
                    'requestStatus' => '', // 请款状态
                    'receiptStatus' => $fee->actual_receive_date ? '已收款' : '未收款',
                ];
            }

            return json_success('获取列表成功', [
                'list' => $tableData,
                'total' => $fees->total(),
                'page' => $fees->currentPage(),
                'limit' => $fees->perPage(),
                'pages' => $fees->lastPage(),
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('费用监控查询失败: ' . $e->getMessage());
            return json_fail('查询失败：' . $e->getMessage());
        }
    }

    /**
    官费监控（费用管理查询）
    功能说明：
    支持项目费用管理、客户费用查询、专利官费查询等多场景费用数据检索
    关联案例、客户、合同等表获取完整业务信息，区分官费 / 服务费统计
    计算费用总额、已付 / 未付金额等核心统计数据，分页返回格式化表格数据
    异常时记录错误日志并返回具体提示，便于问题排查
    请求参数：
    分页参数：
    page（页码）：可选，整数，默认 1，指定分页页码
    limit/pageSize（每页条数）：可选，整数，默认 20，指定单页返回记录数（兼容两种参数名）
    筛选条件：通过buildFeeQuery方法统一构建，支持案例、客户、费用类型、支付状态等多维度筛选（具体筛选字段参考该方法实现）
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取列表成功" 或 "查询失败 + 具体错误信息"
    data（数据主体）：对象，包含以下字段：
    list（费用列表）：数组，每个元素为格式化后的费用记录，包含：
    基础标识：id（费用记录 ID）、ourNumber/ourRef（案例编码）等
    客户信息：clientNumber（客户编码）、clientName（客户名称）等
    案例信息：caseName（案例名称）、caseType（申请类型）、applicationNo（申请号）等
    人员信息：techLead（技术负责人）、businessStaff（业务人员）等
    费用信息：feeType（费用类型）、feeName（费用名称）、officialFee（官费）、serviceFee（服务费）等
    金额信息：totalAmount（总金额）、receivableAmount（应收金额）、currency（货币类型，默认 CNY）等
    时间信息：actualReceiveDate（实际收款日期）、payableDate（应付日期）、paymentDeadline（付款截止日期）等
    状态信息：caseStatus（案例状态文本）、paymentStatus（支付状态文本）、receiptStatus（收款状态）等
    合同信息：contractNumber/contractNo（合同编号）等
    total（总条数）：整数，符合筛选条件的费用记录总条数
    page（当前页码）：整数，当前返回数据的分页页码
    limit（每页条数）：整数，当前分页的单页数据量
    pages（总页数）：整数，按当前分页参数计算的总页数
    stats（统计数据）：对象，包含费用汇总信息：
    totalAmount（费用总额）：所有符合条件记录的金额总和
    paidAmount（已付金额）：支付状态为 "已付" 的金额总和
    unpaidAmount（未付金额）：支付状态为 "未付" 的金额总和
    requestedAmount（请款金额）：暂为 0，需从请款单关联统计
    receivedAmount（已收金额）：已记录实际收款日期的金额总和
    invoicedAmount（开票金额）：暂为 0，需从发票关联统计
    依赖说明：
    依赖buildFeeQuery方法构建筛选条件，具体支持的筛选字段需参考该方法实现
    依赖getCaseStatusText方法将案例数值状态转为文本描述
    依赖getPaymentStatusText方法将支付数值状态转为文本描述
    @param Request $request 请求对象，包含分页参数和筛选条件
    @return \Illuminate\Http\JsonResponse JSON 响应，包含费用列表、分页信息及统计数据
     */
    public function abnormalFee(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 构建查询 - 查找异常费用
            // 这里的逻辑需要根据具体的异常费用判断标准来实现
            // 暂时返回空数据，等待具体业务规则

            $query = DB::table('cases as c')
                ->leftJoin('case_fees as cf', 'c.id', '=', 'cf.case_id')
                ->leftJoin('customers as cust', 'c.customer_id', '=', 'cust.id')
                ->select([
                    'c.id',
                    'c.case_code as project_code',
                    'c.case_code as project_number',
                    'c.application_no',
                    'c.application_date',
                    'c.case_name',
                    'c.application_type',
                    'c.case_status',
                    'cf.amount as notice_fee',
                    'cf.amount as system_fee',
                    'cust.company_name as client_name',
                    // 这里需要添加更多字段来判断异常
                ]);

            // 添加异常条件筛选
            // 例如：通知书官费与系统官费不一致
            $query->whereRaw('cf.amount != cf.amount'); // 这里需要具体的异常判断逻辑

            $results = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($results->items() as $item) {
                $tableData[] = [
                    'id' => $item->id,
                    'projectCode' => '', // 勾选状态
                    'projectNumber' => $item->project_number ?? '',
                    'applicationNo' => $item->application_no ?? '',
                    'applicationDate' => $item->application_date ?? '',
                    'caseName' => $item->case_name ?? '',
                    'applicationType' => $item->application_type ?? '',
                    'caseStatus' => $this->getCaseStatusText($item->case_status),
                    'isReduction' => '', // 是否减缓
                    'noticeFee' => $item->notice_fee ?? 0,
                    'systemFee' => $item->system_fee ?? 0,
                    'agency' => '', // 代理机构
                    'fileName' => '', // 文件名称
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $results->total(),
                    'current_page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'last_page' => $results->lastPage(),
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
    获取申请人名称
    功能说明：
    兼容申请人信息的多格式存储（字符串、单对象数组、多对象数组）
    提取核心申请人名称并返回，无匹配格式时返回空字符串，保障数据兼容性
    解析规则：
    若传入为字符串，直接返回该字符串（适配纯文本存储场景）
    若传入为数组且含 "name" 键，直接返回该 "name" 值（适配单申请人对象存储）
    若传入为多维数组（多申请人场景），取数组首个元素，若元素含 "name" 键则返回该值
    上述场景均不匹配时，返回空字符串
    适配数据格式示例：
    字符串格式："XX 科技有限公司" → 返回 "XX 科技有限公司"
    单对象数组：["name" => "XX 工作室"] → 返回 "XX 工作室"
    多对象数组：[["name" => "张三"], ["name" => "李四"]] → 返回 "张三"
    无匹配格式：[] 或 ["title" => "申请人"] → 返回 ""
    @param string/array $applicantInfo 申请人信息（支持字符串、单维 / 多维数组格式）
    @return string 提取后的申请人名称，无有效信息时返回空字符串
     */
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

    /**
    获取代理机构类型
    功能说明：根据案例的代理机构 ID（agency_id），判断并返回对应的代理类型文本，适配业务展示场景。
    判断规则：
    若 agency_id 大于 0，返回 "我司代理"（表示案例由本公司合作的代理机构处理）
    若 agency_id 为 null，返回 "客户直接申请"（表示案例无需代理机构，客户自行提交）
    其他情况（如 agency_id 小于 0），返回 "其他代理"（适配特殊场景下的外部代理机构）
    字段说明：
    agency_id：案例表中关联代理机构的标识字段，通过该字段区分代理模式
    返回值示例：
    agency_id=5 → "我司代理"
    agency_id=null → "客户直接申请"
    agency_id=-3 → "其他代理"
    @param object $case 案例模型实例，需包含 agency_id 字段
    @return string 代理机构类型文本（"我司代理"“客户直接申请”“其他代理”）
     */
    private function getAgencyType($case)
    {
        if ($case->agency_id > 0) {
            return '我司代理';
        } elseif (is_null($case->agency_id)) {
            return '客户直接申请';
        } else {
            return '其他代理';
        }
    }

    /**
     * 获取案例状态文本
     */
    private function getCaseStatusText($status)
    {
        $statusMap = [
            Cases::STATUS_DRAFT => '草稿',
            Cases::STATUS_TO_BE_FILED => '待立项',
            Cases::STATUS_SUBMITTED => '已提交',
            Cases::STATUS_PROCESSING => '处理中',
            Cases::STATUS_AUTHORIZED => '已授权',
            Cases::STATUS_REJECTED => '已驳回',
            Cases::STATUS_COMPLETED => '已完成',
        ];

        return $statusMap[$status] ?? '未知';
    }

    /**
     * 获取支付状态文本
     */
    private function getPaymentStatusText($status)
    {
        $statusMap = [
            CaseFee::STATUS_UNPAID => '未支付',
            CaseFee::STATUS_PAID => '已支付',
            CaseFee::STATUS_OVERDUE => '已逾期',
        ];

        return $statusMap[$status] ?? '未知';
    }

    /**
    获取费用统计数据
    功能说明：
    统计案例相关费用的核心指标，包括总金额、已付 / 未付金额、已收金额等
    基于案例费用表（CaseFee）数据计算，关联案例表过滤无效数据
    异常时记录详细错误日志（含堆栈信息），便于问题定位
    统计逻辑：
    基础查询：仅统计关联有效案例的费用记录（通过 whereHas ('case') 过滤）
    核心指标计算：
    totalAmount：所有符合条件的费用记录金额总和
    paidAmount：支付状态为 "已付"（CaseFee::STATUS_PAID）的金额总和
    unpaidAmount：支付状态为 "未付"（CaseFee::STATUS_UNPAID）的金额总和
    receivedAmount：已记录实际收款日期（actual_receive_date 不为空）的金额总和
    requestedAmount：暂为 0，需后续关联请款单表补充统计
    invoicedAmount：暂为 0，需后续关联发票表补充统计
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取统计成功" 或 "统计失败 + 具体错误信息"
    data（统计数据）：对象，包含以下浮点型字段：
    totalAmount（费用总额）：所有有效费用记录的总金额
    paidAmount（已付金额）：状态为 "已付" 的费用总金额
    unpaidAmount（未付金额）：状态为 "未付" 的费用总金额
    requestedAmount（请款金额）：预留字段，暂为 0
    receivedAmount（已收金额）：已记录收款日期的费用总金额
    invoicedAmount（开票金额）：预留字段，暂为 0
    依赖说明：
    依赖 CaseFee 模型的 STATUS_PAID 和 STATUS_UNPAID 常量定义支付状态
    依赖 CaseFee 与案例表的关联关系（case 关联）
    @param Request $request 请求对象（当前无额外参数，预留用于后续筛选条件扩展）
    @return \Illuminate\Http\JsonResponse JSON 响应，包含费用统计核心指标
     */
    public function feeStats(Request $request)
    {
        try {
            // 简化版本 - 不使用表前缀,让Eloquent自动处理
            $baseQuery = CaseFee::whereHas('case');

            // 计算统计数据 - 不使用表前缀
            $totalAmount = (clone $baseQuery)->sum('amount');
            $paidAmount = (clone $baseQuery)->where('payment_status', CaseFee::STATUS_PAID)->sum('amount');
            $unpaidAmount = (clone $baseQuery)->where('payment_status', CaseFee::STATUS_UNPAID)->sum('amount');
            $receivedAmount = (clone $baseQuery)->whereNotNull('actual_receive_date')->sum('amount');

            return json_success('获取统计成功', [
                'totalAmount' => (float)$totalAmount,
                'paidAmount' => (float)$paidAmount,
                'unpaidAmount' => (float)$unpaidAmount,
                'requestedAmount' => 0, // 需要从请款单中统计
                'receivedAmount' => (float)$receivedAmount,
                'invoicedAmount' => 0, // 需要从发票中统计
            ]);
        } catch (\Exception $e) {
            \Log::error('费用统计失败: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return json_fail('统计失败：' . $e->getMessage());
        }
    }

    /**
    构建费用查询（CaseFee）
    功能说明：
    整合多源筛选参数（兼容不同前端参数名），构建案例费用的复杂查询条件
    关联案例、客户、业务人员等关联表，支持案例维度和费用维度的组合筛选
    适配日期范围、模糊匹配、精准匹配等多种查询场景，返回结构化查询构建器
    核心特性：
    参数兼容：同一筛选维度支持多个前端参数名（如案例编码支持 our_ref_number/ourNumber/ourRef）
    关联查询：通过 whereHas 关联案例及下属关联表，实现多维度关联筛选
    灵活筛选：支持模糊匹配（名称、编码类字段）、精准匹配（状态、类型类字段）、日期范围匹配
    请求参数（筛选维度 + 兼容参数名）：
    案例标识类（模糊匹配）：
    our_ref_number/ourNumber/ourRef：案例编码（case_code）
    application_no/applicationNumber/applicationNo：申请号
    registration_no/registrationNumber/registrationNo：注册号
    pct_number/pctNumber：PCT 申请号（pct_application_no）
    名称类（模糊匹配）：
    client_name/customerName/clientName：客户名称（客户表 customer_name/company_name）
    case_name/caseName：案例名称
    applicant：申请人信息（applicant_info）
    fee_name/feeName：费用名称
    人员类（精准 / 模糊匹配）：
    technical_lead/technologyMainNumber/techLead：技术负责人（tech_leader，模糊匹配）
    business_staff/caseSalesperson/salesperson：业务人员（business_person_id，精准匹配）
    类型 / 状态类（精准匹配）：
    case_type/caseCategory/caseType：案例类型（case_type）
    business_type/businessType：案例子类型（case_subtype，模糊匹配）
    application_type/applicationType：申请类型（模糊匹配）
    case_status/caseStatus：案例状态（case_status）
    agency_type/agentOrganization：代理机构 ID（agency_id，模糊匹配）
    fee_type/feeCategory/feeType：费用类型（fee_type）
    payment_status/paymentStatus：支付状态（payment_status）
    关联信息类（模糊匹配）：
    contract_no/contractNumber/contractNo：合同编号（合同表 contract_no）
    processing_item/handleItem：处理事项（案例关联的 processes 表 process_name）
    地域类（精准匹配）：
    application_country/applicationCountry：申请国家（country_code）
    日期范围类（数组格式 [开始日期，结束日期]）：
    application_date_range/applicationDate：案例申请日期（application_date）
    payment_deadline_range/paymentPeriod/paymentDeadline：付款截止日期
    receivable_date_range/payableDate/receivableDate：应收日期
    actual_receive_date_range/actualReceiveDate/actualReceivedDate：实际收款日期
    查询构建逻辑：
    基础关联：关联案例表，并预加载客户、业务人员、技术负责人、合同等关联数据
    案例维度筛选：通过 whereHas ('case') 实现案例相关字段的筛选（含关联表字段）
    费用维度筛选：直接筛选费用表自身字段（费用名称、类型、支付状态、日期范围等）
    格式校验：日期范围参数需为数组且长度为 2，否则不执行该条件筛选
    @param Request $request 请求对象，包含所有筛选参数
    @return \Illuminate\Database\Eloquent\Builder 案例费用查询构建器实例
     */
    private function buildFeeQuery(Request $request)
    {
        // 获取查询参数
        $ourRefNumber = $request->input('our_ref_number') ?? $request->input('ourNumber') ?? $request->input('ourRef');
        $applicationNo = $request->input('application_no') ?? $request->input('applicationNumber') ?? $request->input('applicationNo');
        $registrationNo = $request->input('registration_no') ?? $request->input('registrationNumber') ?? $request->input('registrationNo');
        $clientName = $request->input('client_name') ?? $request->input('customerName') ?? $request->input('clientName');
        $caseName = $request->input('case_name') ?? $request->input('caseName');
        $technicalLead = $request->input('technical_lead') ?? $request->input('technologyMainNumber') ?? $request->input('techLead');
        $caseType = $request->input('case_type') ?? $request->input('caseCategory') ?? $request->input('caseType');
        $businessType = $request->input('business_type') ?? $request->input('businessType');
        $applicationType = $request->input('application_type') ?? $request->input('applicationType');
        $processingItem = $request->input('processing_item') ?? $request->input('handleItem');
        $caseStatus = $request->input('case_status') ?? $request->input('caseStatus');
        $agencyType = $request->input('agency_type') ?? $request->input('agentOrganization');
        $applicant = $request->input('applicant');
        $applicationCountry = $request->input('application_country') ?? $request->input('applicationCountry');
        $businessStaff = $request->input('business_staff') ?? $request->input('caseSalesperson') ?? $request->input('salesperson');
        $feeName = $request->input('fee_name') ?? $request->input('feeName');
        $feeType = $request->input('fee_type') ?? $request->input('feeCategory') ?? $request->input('feeType');
        $contractNo = $request->input('contract_no') ?? $request->input('contractNumber') ?? $request->input('contractNo');
        $paymentStatus = $request->input('payment_status') ?? $request->input('paymentStatus');
        $pctNumber = $request->input('pct_number') ?? $request->input('pctNumber');

        // 日期范围参数
        $applicationDateRange = $request->input('application_date_range') ?? $request->input('applicationDate', []);
        $paymentDeadlineRange = $request->input('payment_deadline_range') ?? $request->input('paymentPeriod') ?? $request->input('paymentDeadline', []);
        $receivableDateRange = $request->input('receivable_date_range') ?? $request->input('payableDate') ?? $request->input('receivableDate', []);
        $actualReceiveDateRange = $request->input('actual_receive_date_range') ?? $request->input('actualReceiveDate') ?? $request->input('actualReceivedDate', []);

        // 构建基础查询
        $query = CaseFee::with([
            'case' => function($q) {
                $q->with(['customer', 'businessPerson', 'techLeader', 'contract']);
            }
        ]);

        // 通过案例信息筛选
        $query->whereHas('case', function($caseQuery) use (
            $ourRefNumber, $applicationNo, $registrationNo, $clientName,
            $caseName, $technicalLead, $caseType, $businessType, $applicationType,
            $processingItem, $caseStatus, $agencyType, $applicant,
            $applicationCountry, $businessStaff, $applicationDateRange, $contractNo, $pctNumber
        ) {
            if ($ourRefNumber) {
                $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
            }
            if ($applicationNo) {
                $caseQuery->where('application_no', 'like', "%{$applicationNo}%");
            }
            if ($registrationNo) {
                $caseQuery->where('registration_no', 'like', "%{$registrationNo}%");
            }
            if ($clientName) {
                $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                    $customerQuery->where('customer_name', 'like', "%{$clientName}%")
                                 ->orWhere('company_name', 'like', "%{$clientName}%");
                });
            }
            if ($caseName) {
                $caseQuery->where('case_name', 'like', "%{$caseName}%");
            }
            if ($technicalLead) {
                $caseQuery->where('tech_leader', 'like', "%{$technicalLead}%");
            }
            if ($caseType) {
                $caseQuery->where('case_type', $caseType);
            }
            if ($businessType) {
                $caseQuery->where('case_subtype', 'like', "%{$businessType}%");
            }
            if ($applicationType) {
                $caseQuery->where('application_type', 'like', "%{$applicationType}%");
            }
            if ($processingItem) {
                $caseQuery->whereHas('processes', function($processQuery) use ($processingItem) {
                    $processQuery->where('process_name', 'like', "%{$processingItem}%");
                });
            }
            if ($caseStatus) {
                $caseQuery->where('case_status', $caseStatus);
            }
            if ($agencyType) {
                $caseQuery->where('agency_id', 'like', "%{$agencyType}%");
            }
            if ($applicant) {
                $caseQuery->where('applicant_info', 'like', "%{$applicant}%");
            }
            if ($applicationCountry) {
                $caseQuery->where('country_code', $applicationCountry);
            }
            if ($businessStaff) {
                $caseQuery->where('business_person_id', $businessStaff);
            }
            if ($contractNo) {
                $caseQuery->whereHas('contract', function($contractQuery) use ($contractNo) {
                    $contractQuery->where('contract_no', 'like', "%{$contractNo}%");
                });
            }
            if ($pctNumber) {
                $caseQuery->where('pct_application_no', 'like', "%{$pctNumber}%");
            }
            if (!empty($applicationDateRange) && is_array($applicationDateRange) && count($applicationDateRange) == 2) {
                $caseQuery->whereBetween('application_date', $applicationDateRange);
            }
        });

        // 费用名称筛选
        if ($feeName) {
            $query->where('fee_name', 'like', "%{$feeName}%");
        }

        // 缴费类型筛选
        if ($feeType) {
            $query->where('fee_type', $feeType);
        }

        // 缴费期限范围
        if (!empty($paymentDeadlineRange) && is_array($paymentDeadlineRange) && count($paymentDeadlineRange) == 2) {
            $query->whereBetween('payment_deadline', $paymentDeadlineRange);
        }

        // 应收日期范围
        if (!empty($receivableDateRange) && is_array($receivableDateRange) && count($receivableDateRange) == 2) {
            $query->whereBetween('receivable_date', $receivableDateRange);
        }

        // 实收日期范围
        if (!empty($actualReceiveDateRange) && is_array($actualReceiveDateRange) && count($actualReceiveDateRange) == 2) {
            $query->whereBetween('actual_receive_date', $actualReceiveDateRange);
        }

        // 支付状态筛选
        if ($paymentStatus !== null && $paymentStatus !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        return $query;
    }


    /**
    导出事项监控数据
    功能说明：
    基于事项监控的筛选条件，导出结构化的 Excel 文件（.xlsx 格式）
    文件名包含 “事项监控” 前缀和导出时间戳，便于文件识别和管理
    异常时返回具体错误信息，适配前端错误提示场景
    核心逻辑：
    接收筛选参数：复用事项监控（itemMonitor）接口的所有筛选条件，参数格式完全一致
    导出实例化：通过 ItemMonitorExport 类处理数据查询、格式化和 Excel 构建
    文件下载：生成带时间戳的 Excel 文件，直接响应下载请求
    导出文件说明：
    格式：Excel（.xlsx），兼容主流办公软件
    文件名格式：事项监控_YYYY-MM-DD_HH-mm-ss.xlsx（如 “事项监控_20240618_143025.xlsx”）
    数据内容：与事项监控列表展示的字段一致，包含案例信息、处理事项信息、人员信息、时间信息等
    依赖说明：
    依赖 ItemMonitorExport 类实现数据查询和 Excel 生成逻辑
    筛选参数规则与 itemMonitor 接口完全一致，无需额外适配
    错误处理：
    导出过程中出现异常（如数据查询失败、Excel 生成失败），返回 500 状态码和具体错误描述
    @param Request $request 请求对象，包含事项监控的所有筛选参数（与 itemMonitor 接口一致）
    @return \Symfony\Component\HttpFoundation\BinaryFileResponse Excel 文件下载响应
     */
    public function exportItemMonitor(Request $request)
    {
        try {
            $export = new ItemMonitorExport($request);

            $filename = '事项监控_' . date('Y-m-d_H-i-s') . '.xlsx';

            return $export->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    导出官费监控数据
    功能说明：
    基于官费监控的筛选条件，导出结构化 Excel 文件（.xlsx 格式），适配费用数据存档、报表分析场景
    文件名含 “官费监控” 前缀和时间戳，确保文件唯一性和可识别性
    异常时返回 500 状态码和具体错误信息，便于前端排查导出失败原因
    核心逻辑：
    参数复用：完全复用 feeMonitor 接口的所有筛选参数，筛选规则保持一致，无需额外适配
    导出处理：通过 FeeMonitorExport 类完成数据查询、格式化（与列表页字段一致）及 Excel 文件构建
    下载响应：生成带时间戳的 Excel 文件，直接返回下载响应供用户保存或二次处理
    导出文件说明：
    格式：Excel（.xlsx），兼容 WPS、Office 等主流办公软件
    文件名格式：官费监控_YYYY-MM-DD_HH-mm-ss.xlsx（示例：官费监控_20240620_162035.xlsx）
    数据内容：与官费监控列表展示字段完全一致，包含项目信息、客户信息、费用明细、状态信息等（具体字段由 FeeMonitorExport 类定义）
    依赖说明：
    依赖 FeeMonitorExport 类实现数据筛选、字段格式化及 Excel 生成逻辑
    筛选参数需与 feeMonitor 接口保持一致，确保导出数据与查询结果匹配
    错误处理：
    若出现数据查询失败、Excel 文件生成异常、内存溢出等情况，返回错误信息并提示排查筛选条件或数据量
    @param Request $request 请求对象，包含官费监控查询的所有筛选参数
    @return \Symfony\Component\HttpFoundation\BinaryFileResponse Excel 文件下载响应
     */
    public function exportFeeMonitor(Request $request)
    {
        try {
            $export = new FeeMonitorExport($request);

            $filename = '官费监控_' . date('Y-m-d_H-i-s') . '.xlsx';

            return $export->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    导出异常官费数据
    功能说明：
    基于异常官费的筛选条件，导出结构化 Excel 文件（.xlsx 格式），适配费用异常监控和数据存档场景
    文件名含 “异常官费” 前缀和时间戳，确保文件唯一性和可识别性
    异常时返回 500 状态码和具体错误信息，便于前端排查问题
    核心逻辑：
    参数复用：接收与异常官费查询相关的筛选参数，筛选规则与对应查询接口保持一致
    导出处理：通过 AbnormalFeeExport 类完成数据查询、异常数据筛选、Excel 文件构建
    下载响应：生成带时间戳的 Excel 文件，直接返回下载响应供用户保存
    导出文件说明：
    格式：Excel（.xlsx），兼容 WPS、Office 等主流办公软件
    文件名格式：异常官费_YYYY-MM-DD_HH-mm-ss.xlsx（示例：异常官费_20240619_094512.xlsx）
    数据内容：包含异常官费的核心字段，如案例编码、客户信息、费用金额、异常类型、支付状态等（具体字段由 AbnormalFeeExport 类定义）
    依赖说明：
    依赖 AbnormalFeeExport 类实现数据筛选、格式化及 Excel 生成逻辑
    筛选参数需与异常官费查询接口一致，无需额外定义新参数
    错误处理：
    若出现数据查询失败、Excel 文件生成异常等情况，返回错误信息并记录异常详情
    @param Request $request 请求对象，包含异常官费查询的所有筛选参数
    @return \Symfony\Component\HttpFoundation\BinaryFileResponse Excel 文件下载响应
     */
    public function exportAbnormalFee(Request $request)
    {
        try {
            $export = new AbnormalFeeExport($request);

            $filename = '异常官费_' . date('Y-m-d_H-i-s') . '.xlsx';

            return $export->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '导出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    标记异常费用已处理
    功能说明：
    支持批量将指定 ID 的异常费用记录标记为 “已处理” 状态，更新业务流转状态
    校验请求参数合法性，无选中数据时返回明确提示，异常时返回具体错误信息
    请求参数：
    ids（异常费用 ID 数组）：必填，数组类型，元素为整数，需标记为已处理的异常费用记录 ID 集合
    业务逻辑：
    参数校验：若 ids 为空数组或未传，返回 400 状态码和 “请选择要标记的数据” 提示
    状态更新：根据实际业务逻辑，将指定 ID 的异常费用记录更新为 “已处理” 状态（当前为占位逻辑，需补充具体实现）
    响应返回：标记成功后返回成功提示，失败则返回错误详情
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 “标记处理成功”“请选择要标记的数据” 或 “标记处理失败 + 具体错误信息”
    错误状态码：
    400：未选择任何要标记的异常费用数据
    500：服务器内部错误（如数据库更新异常等）
    待补充实现：
    需完善 “标记已处理” 的具体业务逻辑（如更新异常费用表的 status 字段、记录处理人 / 处理时间等）
    @param Request $request 请求对象，包含待标记的异常费用 ID 数组
    @return \Illuminate\Http\JsonResponse 标记操作结果响应
     */
    public function markAbnormalFeeProcessed(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要标记的数据'
                ], 400);
            }

            // 这里需要根据具体业务逻辑来标记异常费用
            // 暂时返回成功状态

            return response()->json([
                'success' => true,
                'message' => '标记处理成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '标记处理失败：' . $e->getMessage()
            ], 500);
        }
    }
}
