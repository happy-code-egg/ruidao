<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatentSearchExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DB::table('cases as cc')
            ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
            ->leftJoin('users as business_user', 'cc.business_person_id', '=', 'business_user.id')
            ->leftJoin('users as agent_user', 'cc.agent_id', '=', 'agent_user.id')
            ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
            ->where('cc.case_type', 1)
            ->select([
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
                'cc.case_description',
                'cc.technical_field',
                'cc.innovation_points',
                'cc.remarks',
                'c.customer_name',
                'business_user.real_name as business_person',
                'agent_user.real_name as tech_leader',
                'assistant.real_name as assistant_name'
            ]);

        // 应用过滤条件
        $this->applyFilters($query);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '我方文号',
            '客户文号',
            '申请号',
            '客户名称',
            '项目名称',
            '提案名称',
            '申请人名称',
            '申请类型',
            '业务类型',
            '项目状态',
            '申请日',
            '开卷日期',
            '申请国家(地区)',
            '项目流向',
            '业务人员',
            '技术主导',
            '项目处理人',
            '费减比例',
            '项目备注'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFE6E6E6',
                    ],
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // 我方文号
            'B' => 15, // 客户文号
            'C' => 20, // 申请号
            'D' => 25, // 客户名称
            'E' => 30, // 项目名称
            'F' => 25, // 提案名称
            'G' => 20, // 申请人名称
            'H' => 12, // 申请类型
            'I' => 12, // 业务类型
            'J' => 12, // 项目状态
            'K' => 12, // 申请日
            'L' => 12, // 开卷日期
            'M' => 15, // 申请国家(地区)
            'N' => 12, // 项目流向
            'O' => 12, // 业务人员
            'P' => 12, // 技术主导
            'Q' => 12, // 项目处理人
            'R' => 12, // 费减比例
            'S' => 20, // 项目备注
        ];
    }

    private function applyFilters($query)
    {
        if (!empty($this->filters['ourRefNumber'])) {
            $query->where('cc.our_ref_number', 'like', '%' . $this->filters['ourRefNumber'] . '%');
        }

        if (!empty($this->filters['customerRefNumber'])) {
            $query->where('cc.customer_ref_number', 'like', '%' . $this->filters['customerRefNumber'] . '%');
        }

        if (!empty($this->filters['appNumber'])) {
            $query->where('cc.app_number', 'like', '%' . $this->filters['appNumber'] . '%');
        }

        if (!empty($this->filters['customerName'])) {
            $query->where('c.name', 'like', '%' . $this->filters['customerName'] . '%');
        }

        if (!empty($this->filters['applicantName'])) {
            $query->where('cc.applicant_name', 'like', '%' . $this->filters['applicantName'] . '%');
        }

        if (!empty($this->filters['applicationType'])) {
            $query->where('cc.application_type', $this->filters['applicationType']);
        }

        if (!empty($this->filters['businessType'])) {
            $query->where('cc.business_type', $this->filters['businessType']);
        }

        if (!empty($this->filters['caseStatus'])) {
            $query->where('cc.case_status', $this->filters['caseStatus']);
        }

        if (!empty($this->filters['appDateRange']) && is_array($this->filters['appDateRange']) && count($this->filters['appDateRange']) == 2) {
            $query->whereBetween('cc.app_date', $this->filters['appDateRange']);
        }

        if (!empty($this->filters['businessPerson'])) {
            $query->where('business_user.name', $this->filters['businessPerson']);
        }
    }
}
