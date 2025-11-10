<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支出单项目模型
 * 用于管理支出单中的具体项目明细信息
 */
class ExpenseItem extends Model
{
    protected $table = 'expense_items';

    protected $fillable = [
        'expense_id',          // 支出单ID
        'request_no',          // 申请单号
        'request_date',        // 申请日期
        'our_no',              // 我方编号
        'case_name',           // 案件名称
        'client_name',         // 客户名称
        'applicant',           // 申请人
        'process_item',        // 处理项目
        'expense_name',        // 费用名称
        'payable_amount',      // 应付金额
        'payment_date',        // 计划支付日期
        'actual_pay_date',     // 实际支付日期
        'expense_remark',      // 费用备注
        'cooperative_agency',  // 合作机构
        'expense_type',        // 费用类型
    ];

    protected $casts = [
        'payable_amount' => 'decimal:2',
        'request_date' => 'date',
        'payment_date' => 'date',
        'actual_pay_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联支出单
     * 通过 `expense_id` 字段关联 `Expense` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}//这里

