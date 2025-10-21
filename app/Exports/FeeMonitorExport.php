<?php

namespace App\Exports;

use App\Models\CaseFee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class FeeMonitorExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        // 构建基础查询 - 查询费用及关联的案例信息
        $query = CaseFee::with([
            'case' => function($q) {
                $q->with(['customer', 'businessPerson', 'techLeader']);
            }
        ]);

        // 应用筛选条件（复制自控制器的筛选逻辑）
        $ourRefNumber = $this->request->input('our_ref_number');
        $applicationNo = $this->request->input('application_no');
        $registrationNo = $this->request->input('registration_no');
        $clientName = $this->request->input('client_name');
        $caseName = $this->request->input('case_name');
        $technicalLead = $this->request->input('technical_lead');
        $caseType = $this->request->input('case_type');
        $businessType = $this->request->input('business_type');
        $applicationType = $this->request->input('application_type');
        $processingItem = $this->request->input('processing_item');
        $caseStatus = $this->request->input('case_status');
        $agencyType = $this->request->input('agency_type');
        $applicant = $this->request->input('applicant');
        $applicationCountry = $this->request->input('application_country');
        $businessStaff = $this->request->input('business_staff');
        $feeName = $this->request->input('fee_name');
        $feeType = $this->request->input('fee_type');

        // 通过案例信息筛选
        $query->whereHas('case', function($caseQuery) use (
            $ourRefNumber, $applicationNo, $registrationNo, $clientName, 
            $caseName, $technicalLead, $caseType, $businessType, $applicationType, 
            $processingItem, $caseStatus, $agencyType, $applicant, 
            $applicationCountry, $businessStaff
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
                $statusMap = [
                    '已提交' => \App\Models\Cases::STATUS_SUBMITTED,
                    '审批中' => \App\Models\Cases::STATUS_PROCESSING,
                    '已授权' => \App\Models\Cases::STATUS_AUTHORIZED
                ];
                if (isset($statusMap[$caseStatus])) {
                    $caseQuery->where('case_status', $statusMap[$caseStatus]);
                }
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
            if ($applicant) {
                $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                         ->orWhere('applicant_info', 'like', "%{$applicant}%");
            }
            if ($applicationCountry) {
                $caseQuery->where('country_code', $applicationCountry);
            }
            if ($businessStaff) {
                $caseQuery->whereHas('businessPerson', function($businessQuery) use ($businessStaff) {
                    $businessQuery->where('name', 'like', "%{$businessStaff}%");
                });
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

        // 日期范围筛选
        $applicationDateRange = $this->request->input('application_date_range', []);
        $paymentDeadlineRange = $this->request->input('payment_deadline_range', []);
        $receivableDateRange = $this->request->input('receivable_date_range', []);
        $actualReceiveDateRange = $this->request->input('actual_receive_date_range', []);

        // 通过案例的申请日期筛选
        if (!empty($applicationDateRange) && count($applicationDateRange) == 2) {
            $query->whereHas('case', function($caseQuery) use ($applicationDateRange) {
                $caseQuery->whereBetween('application_date', $applicationDateRange);
            });
        }

        if (!empty($paymentDeadlineRange) && count($paymentDeadlineRange) == 2) {
            $query->whereBetween('payment_deadline', $paymentDeadlineRange);
        }
        if (!empty($receivableDateRange) && count($receivableDateRange) == 2) {
            $query->whereBetween('receivable_date', $receivableDateRange);
        }
        if (!empty($actualReceiveDateRange) && count($actualReceiveDateRange) == 2) {
            $query->whereBetween('actual_receive_date', $actualReceiveDateRange);
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public function headings(): array
    {
        return [
            '我方文号',
            '案件名称',
            '申请号',
            '注册号',
            '申请类型',
            '处理事项',
            '案件阶段',
            '费用名称',
            '应收金额',
            '缴费期限',
            '缴费类型',
            '实收日期',
            '客户名称',
            '代理机构',
            '业务人员',
            '类别',
            '技术主导'
        ];
    }

    public function map($fee): array
    {
        $case = $fee->case;
        $customer = $case->customer;
        
        return [
            $case->case_code ?? '',
            $case->case_name ?? '',
            $case->application_no ?? '',
            $case->registration_no ?? '',
            $case->application_type ?? '',
            '', // 处理事项需要从处理事项中获取
            $case->case_phase ?? '',
            $fee->fee_name ?? '',
            $fee->amount ?? 0,
            $fee->payment_deadline ? $fee->payment_deadline->format('Y-m-d') : '',
            $fee->type_text ?? '',
            $fee->actual_receive_date ? $fee->actual_receive_date->format('Y-m-d') : '',
            $customer ? $customer->customer_name : '',
            $this->getAgencyType($case),
            $case->businessPerson ? $case->businessPerson->name : '',
            $case->trademark_category ?? '',
            $case->techLeader ? $case->techLeader->name : '',
        ];
    }

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
}
