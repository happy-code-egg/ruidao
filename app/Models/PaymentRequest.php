<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 请款单模型
 * 管理客户付款请求信息、审批流程及关联明细
 */
class PaymentRequest extends Model
{
    use SoftDeletes;

    protected $table = 'payment_requests';

    protected $fillable = [
        'request_no',           // 请款单号
        'customer_id',          // 客户ID
        'contract_id',          // 合同ID
        'request_type',         // 请款类型
        'request_status',       // 请款状态（0:草稿, 1:待审批, 2:已审批, 3:已拒绝）
        'total_amount',         // 请款总额
        'paid_amount',          // 已付款金额
        'unpaid_amount',        // 未付款金额
        'currency',             // 货币类型
        'request_date',         // 请款日期
        'due_date',             // 到期日期
        'payment_terms',        // 付款条件
        'bank_account',         // 收款银行账户
        'created_by',           // 创建人ID
        'approved_by',          // 审批人ID
        'submitted_at',         // 提交时间
        'approved_at',          // 审批时间
        'remark',               // 备注
        'approve_remark',       // 审批备注
    ];

    protected $dates = ['deleted_at', 'submitted_at', 'approved_at'];

    /**
     * 关联客户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联合同
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 关联创建人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联审批人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 关联请款明细
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(PaymentRequestDetail::class, 'request_id');
    }
}

