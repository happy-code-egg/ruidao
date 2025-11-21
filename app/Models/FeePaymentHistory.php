<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 缴费单历史记录模型
 * 用于记录缴费单的操作历史
 */
class FeePaymentHistory extends Model
{
    protected $table = 'fee_payment_history';

    protected $fillable = [
        'fee_payment_id',  // 缴费单ID
        'operation',       // 操作类型
        'operator_id',     // 操作人ID
        'operator_name',   // 操作人名称
        'remark',          // 备注
    ];

    protected $casts = [
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
}

