<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectSearchExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
            ->where('cc.case_type', 4)
            ->select([
                'cc.project_number',
                'cc.apply_stage',
                'cc.apply_result',
                'c.name as customer_name',
                'cc.applicant_name',
                'cc.application_type',
                'cc.business_type',
                'cc.tech_service_name',
                'cc.project_year',
                'cc.apply_batch',
                'cc.tech_service_level',
                'cc.gov_estimated_reward',
                'cc.gov_actual_reward',
                'cc.fee_ratio',
                'cc.is_urgent',
                'cc.apply_deadline',
                'cc.actual_submit_date',
                'cc.receive_date',
                'cc.internal_deadline',
                'cc.estimated_start_date',
                'cc.estimated_completion_date',
                'business_user.name as business_person',
                'case_handler.name as case_handler',
                'tech_leader.name as tech_lead'
            ]);

        // 应用过滤条件
        $this->applyFilters($query);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '项目编号',
            '申报阶段',
            '申报结果',
            '客户名称',
            '申请人',
            '申请类型',
            '业务类型',
            '科技服务名称',
            '项目年份',
            '申报批次',
            '科技服务级别',
            '政府预估奖励',
            '政府实际奖励',
            '费用比例',
            '是否加急',
            '申报截止日',
            '实际提交日',
            '收单日期',
            '内部期限',
            '预计启动日',
            '预计完成日',
            '业务人员',
            '项目处理人',
            '技术主导'
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
            'A' => 15, // 项目编号
            'B' => 12, // 申报阶段
            'C' => 12, // 申报结果
            'D' => 25, // 客户名称
            'E' => 20, // 申请人
            'F' => 12, // 申请类型
            'G' => 12, // 业务类型
            'H' => 20, // 科技服务名称
            'I' => 10, // 项目年份
            'J' => 15, // 申报批次
            'K' => 12, // 科技服务级别
            'L' => 15, // 政府预估奖励
            'M' => 15, // 政府实际奖励
            'N' => 10, // 费用比例
            'O' => 10, // 是否加急
            'P' => 12, // 申报截止日
            'Q' => 12, // 实际提交日
            'R' => 12, // 收单日期
            'S' => 12, // 内部期限
            'T' => 12, // 预计启动日
            'U' => 12, // 预计完成日
            'V' => 12, // 业务人员
            'W' => 12, // 项目处理人
            'X' => 12, // 技术主导
        ];
    }

    private function applyFilters($query)
    {
        if (!empty($this->filters['projectNumber'])) {
            $query->where('cc.project_number', 'like', '%' . $this->filters['projectNumber'] . '%');
        }

        if (!empty($this->filters['applyStage'])) {
            if ($this->filters['applyStage'] === '__empty__') {
                $query->whereNull('cc.apply_stage');
            } else {
                $query->where('cc.apply_stage', $this->filters['applyStage']);
            }
        }

        if (!empty($this->filters['applyResult'])) {
            if ($this->filters['applyResult'] === '__empty__') {
                $query->whereNull('cc.apply_result');
            } else {
                $query->where('cc.apply_result', $this->filters['applyResult']);
            }
        }

        if (!empty($this->filters['customerName'])) {
            $query->where('c.name', 'like', '%' . $this->filters['customerName'] . '%');
        }

        if (!empty($this->filters['applicantName'])) {
            $query->where('cc.applicant_name', 'like', '%' . $this->filters['applicantName'] . '%');
        }

        if (!empty($this->filters['businessType'])) {
            $query->where('cc.business_type', $this->filters['businessType']);
        }

        if (!empty($this->filters['applicationType'])) {
            $query->where('cc.application_type', $this->filters['applicationType']);
        }

        if (!empty($this->filters['techServiceName'])) {
            $query->where('cc.tech_service_name', 'like', '%' . $this->filters['techServiceName'] . '%');
        }

        if (!empty($this->filters['projectYear'])) {
            $query->where('cc.project_year', $this->filters['projectYear']);
        }

        if (!empty($this->filters['businessPerson'])) {
            if ($this->filters['businessPerson'] === '__empty__') {
                $query->whereNull('cc.business_person_id');
            } else {
                $query->where('business_user.name', $this->filters['businessPerson']);
            }
        }
    }
}
