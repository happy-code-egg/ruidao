<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrademarkSearchExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
            ->where('cc.case_type', 2)
            ->select([
                'cc.our_ref_number',
                'cc.customer_ref_number',
                'cc.app_number',
                'cc.reg_number',
                'c.name as customer_name',
                'cc.case_name as trademark_name',
                'cc.applicant_name',
                'cc.trademark_class',
                'cc.application_type',
                'cc.business_type',
                'cc.case_status',
                'cc.app_date',
                'cc.open_date',
                'cc.reg_announce_date',
                'cc.renewal_date',
                'cc.app_country',
                'business_user.name as business_person',
                'case_handler.name as case_handler'
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
            '注册号',
            '客户名称',
            '商标名称',
            '申请人名称',
            '商标类别',
            '申请类型',
            '业务类型',
            '项目状态',
            '申请日',
            '开卷日期',
            '注册公告日',
            '续展日',
            '申请国家(地区)',
            '业务人员',
            '项目处理人'
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
            'D' => 20, // 注册号
            'E' => 25, // 客户名称
            'F' => 20, // 商标名称
            'G' => 20, // 申请人名称
            'H' => 12, // 商标类别
            'I' => 12, // 申请类型
            'J' => 12, // 业务类型
            'K' => 12, // 项目状态
            'L' => 12, // 申请日
            'M' => 12, // 开卷日期
            'N' => 12, // 注册公告日
            'O' => 12, // 续展日
            'P' => 15, // 申请国家(地区)
            'Q' => 12, // 业务人员
            'R' => 12, // 项目处理人
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

        if (!empty($this->filters['appNumber'])) {
            $query->where('cc.app_number', 'like', '%' . $this->filters['appNumber'] . '%');
        }

        if (!empty($this->filters['customerName'])) {
            $query->where('c.name', 'like', '%' . $this->filters['customerName'] . '%');
        }

        if (!empty($this->filters['trademarkName'])) {
            $query->where('cc.case_name', 'like', '%' . $this->filters['trademarkName'] . '%');
        }

        if (!empty($this->filters['applicantName'])) {
            $query->where('cc.applicant_name', 'like', '%' . $this->filters['applicantName'] . '%');
        }

        if (!empty($this->filters['trademarkClass'])) {
            $query->where('cc.trademark_class', $this->filters['trademarkClass']);
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
