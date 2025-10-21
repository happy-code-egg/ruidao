<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CopyrightSearchExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
            ->leftJoin('users as assistant', 'cc.assistant_id', '=', 'assistant.id')
            ->where('cc.case_type', 3)
            ->select([
                'cc.our_ref_number',
                'cc.customer_ref_number',
                'cc.external_ref_number',
                'cc.case_name as work_name',
                'cc.reg_number',
                'c.name as customer_name',
                'cc.applicant_name',
                'cc.author_name',
                'cc.application_type as work_type',
                'cc.business_type',
                'cc.case_status',
                'cc.app_date',
                'cc.reg_date',
                'cc.open_date',
                'business_user.name as business_person',
                'case_handler.name as case_handler',
                'cc.case_remark'
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
            '外部文号',
            '作品名称',
            '登记号',
            '客户名称',
            '申请人名称',
            '作者',
            '作品类型',
            '业务类型',
            '项目状态',
            '申请日',
            '登记日',
            '开卷日期',
            '业务人员',
            '项目处理人',
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
            'C' => 15, // 外部文号
            'D' => 25, // 作品名称
            'E' => 20, // 登记号
            'F' => 25, // 客户名称
            'G' => 20, // 申请人名称
            'H' => 12, // 作者
            'I' => 12, // 作品类型
            'J' => 12, // 业务类型
            'K' => 12, // 项目状态
            'L' => 12, // 申请日
            'M' => 12, // 登记日
            'N' => 12, // 开卷日期
            'O' => 12, // 业务人员
            'P' => 12, // 项目处理人
            'Q' => 20, // 项目备注
        ];
    }

    private function applyFilters($query)
    {
        if (!empty($this->filters['ourRefNumber'])) {
            $query->where('cc.our_ref_number', 'like', '%' . $this->filters['ourRefNumber'] . '%');
        }

        if (!empty($this->filters['regNumber'])) {
            $query->where('cc.reg_number', 'like', '%' . $this->filters['regNumber'] . '%');
        }

        if (!empty($this->filters['workName'])) {
            $query->where('cc.case_name', 'like', '%' . $this->filters['workName'] . '%');
        }

        if (!empty($this->filters['customerName'])) {
            $query->where('c.name', 'like', '%' . $this->filters['customerName'] . '%');
        }

        if (!empty($this->filters['applicantName'])) {
            $query->where('cc.applicant_name', 'like', '%' . $this->filters['applicantName'] . '%');
        }

        if (!empty($this->filters['workType'])) {
            $query->where('cc.application_type', $this->filters['workType']);
        }

        if (!empty($this->filters['businessType'])) {
            $query->where('cc.business_type', $this->filters['businessType']);
        }

        if (!empty($this->filters['caseStatus'])) {
            $query->where('cc.case_status', $this->filters['caseStatus']);
        }

        if (!empty($this->filters['businessPerson'])) {
            $query->where('business_user.name', $this->filters['businessPerson']);
        }
    }
}
