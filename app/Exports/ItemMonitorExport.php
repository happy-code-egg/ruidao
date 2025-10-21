<?php

namespace App\Exports;

use App\Models\CaseProcess;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class ItemMonitorExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        // 构建基础查询 - 查询处理事项及关联的案例信息
        $query = CaseProcess::with([
            'case' => function($q) {
                $q->with(['customer', 'businessPerson', 'techLeader']);
            },
            'assignedUser',
            'assigneeUser'
        ]);

        // 应用筛选条件（复制自控制器的筛选逻辑）
        $ourRefNumber = $this->request->input('our_ref_number');
        $applicationNo = $this->request->input('application_no');
        $registrationNo = $this->request->input('registration_no');
        $businessStaff = $this->request->input('business_staff');
        $clientName = $this->request->input('client_name');
        $applicant = $this->request->input('applicant');
        $caseName = $this->request->input('case_name');
        $technicalLead = $this->request->input('technical_lead');
        $caseType = $this->request->input('case_type');
        $applicationType = $this->request->input('application_type');
        $applicationCountry = $this->request->input('application_country');
        $agencyType = $this->request->input('agency_type');
        $processingItemType = $this->request->input('processing_item_type');
        $processingItem = $this->request->input('processing_item');
        $caseStatus = $this->request->input('case_status');
        $processingStatus = $this->request->input('processing_status');
        $itemResponsible = $this->request->input('item_responsible');

        // 通过案例信息筛选
        $query->whereHas('case', function($caseQuery) use (
            $ourRefNumber, $applicationNo, $registrationNo, $clientName, 
            $applicant, $caseName, $technicalLead, $caseType, $applicationType, 
            $applicationCountry, $agencyType, $caseStatus
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
                    '专利' => \App\Models\Cases::TYPE_PATENT,
                    '商标' => \App\Models\Cases::TYPE_TRADEMARK,
                    '版权' => \App\Models\Cases::TYPE_COPYRIGHT,
                    '科服' => \App\Models\Cases::TYPE_TECH_SERVICE
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
                    '待处理' => \App\Models\Cases::STATUS_SUBMITTED,
                    '处理中' => \App\Models\Cases::STATUS_PROCESSING,
                    '已完成' => \App\Models\Cases::STATUS_COMPLETED
                ];
                if (isset($statusMap[$caseStatus])) {
                    $caseQuery->where('case_status', $statusMap[$caseStatus]);
                }
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
        $receiveDateRange = $this->request->input('receive_date_range', []);
        $internalDeadlineRange = $this->request->input('internal_deadline_range', []);
        $clientDeadlineRange = $this->request->input('client_deadline_range', []);
        $officialDeadlineRange = $this->request->input('official_deadline_range', []);
        $openingDateRange = $this->request->input('opening_date_range', []);
        $documentationDateRange = $this->request->input('documentation_date_range', []);
        $applicationDateRange = $this->request->input('application_date_range', []);
        $processingCreatedDateRange = $this->request->input('processing_created_date_range', []);

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

        return $query;
    }

    public function headings(): array
    {
        return [
            '我方文号',
            '客户名称',
            '案件类型',
            '申请类型',
            '案件名称',
            '申请号',
            '注册号',
            '处理事项',
            '案件阶段',
            '处理事项状态',
            '处理事项处理人',
            '官方期限',
            '收文日',
            '发文日',
            '业务人员',
            '申请日',
            '类别',
            '技术主导',
            '申请人'
        ];
    }

    public function map($process): array
    {
        $case = $process->case;
        $customer = $case->customer;
        
        return [
            $case->case_code ?? '',
            $customer ? $customer->customer_name : '',
            $case->type_text,
            $case->application_type ?? '',
            $case->case_name ?? '',
            $case->application_no ?? '',
            $case->registration_no ?? '',
            $process->process_name ?? '',
            $case->case_phase ?? '',
            $process->status_text,
            $process->assignedUser ? $process->assignedUser->name : '',
            $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
            $process->issue_date ? $process->issue_date->format('Y-m-d') : '',
            $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
            $case->businessPerson ? $case->businessPerson->name : '',
            $case->application_date ? $case->application_date->format('Y-m-d') : '',
            $case->trademark_category ?? '',
            $case->techLeader ? $case->techLeader->name : '',
            $this->getApplicantName($case->applicant_info),
        ];
    }

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
