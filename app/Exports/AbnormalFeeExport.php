<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class AbnormalFeeExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
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
                DB::raw("COALESCE(cust.customer_name, '') as client_name"),
                DB::raw('COALESCE(cf.is_reduction, false) as is_reduction'),
                // 这里需要添加更多字段来判断异常
            ]);

        // 添加异常条件筛选
        // 例如：通知书官费与系统官费不一致
        // 这里需要具体的异常判断逻辑，暂时返回所有数据
        $query->whereNotNull('cf.id'); // 确保有费用记录

        return collect($query->get());
    }

    public function headings(): array
    {
        return [
            '勾选',
            '项目编号',
            '申请号',
            '申请日',
            '案件名称',
            '案件类型',
            '案件状态',
            '是否减缓',
            '通知书官费',
            '系统官费',
            '代理机构',
            '文件名称'
        ];
    }

    public function map($item): array
    {
        return [
            '', // 勾选状态
            $item->project_number ?? '',
            $item->application_no ?? '',
            $item->application_date ?? '',
            $item->case_name ?? '',
            $item->application_type ?? '',
            $this->getCaseStatusText($item->case_status),
            $item->is_reduction ? '是' : '否',
            $item->notice_fee ?? 0,
            $item->system_fee ?? 0,
            '', // 代理机构
            '', // 文件名称
        ];
    }

    private function getCaseStatusText($status)
    {
        $statusMap = [
            \App\Models\Cases::STATUS_DRAFT => '草稿',
            \App\Models\Cases::STATUS_TO_BE_FILED => '待立项',
            \App\Models\Cases::STATUS_SUBMITTED => '已提交',
            \App\Models\Cases::STATUS_PROCESSING => '处理中',
            \App\Models\Cases::STATUS_AUTHORIZED => '已授权',
            \App\Models\Cases::STATUS_REJECTED => '已驳回',
            \App\Models\Cases::STATUS_COMPLETED => '已完成',
        ];

        return $statusMap[$status] ?? '未知';
    }
}
