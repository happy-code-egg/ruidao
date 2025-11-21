<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 缴费单明细模型
 * 用于管理缴费单中的具体项目明细信息
 */
class FeePaymentItem extends Model
{
    protected $table = 'fee_payment_items';

    protected $fillable = [
        'fee_payment_id',      // 缴费单ID
        'case_id',             // 案件ID
        'our_ref',             // 我方编号
        'application_no',      // 申请号
        'case_name',           // 案件名称
        'applicant',           // 申请人
        'process_item',        // 处理项目
        'fee_name',            // 费用名称
        'fee_type',            // 费用类型
        'amount',              // 金额
        'currency',            // 币种
        'payment_deadline',    // 缴费期限
        'actual_payment_date', // 实际缴费日期
        'receipt_no',          // 缴费回执号
        'pickup_code',         // 取票码
        'remark',              // 备注
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_deadline' => 'date',
        'actual_payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联缴费单
     */
    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    /**
     * 关联案件
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }
}

