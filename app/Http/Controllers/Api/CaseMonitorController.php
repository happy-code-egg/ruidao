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
     * 事项监控
     */
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
     * 官费监控 - 支持项目费用管理、客户费用查询、专利官费查询
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
     * 异常官费
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
     * 获取申请人名称
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
     * 获取代理机构类型
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
     * 获取费用统计数据
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
     * 构建费用查询
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
     * 导出事项监控数据
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
     * 导出官费监控数据
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
     * 导出异常官费数据
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
     * 标记异常费用已处理
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
