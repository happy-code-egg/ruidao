<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 收款记录模型
 * 管理客户付款信息、认领状态及关联关系
 */
class PaymentReceived extends Model
{
    use SoftDeletes;

    protected $table = 'payment_receiveds';

    protected $fillable = [
        'payment_no',           // 收款单号
        'customer_id',          // 客户ID
        'contract_id',          // 合同ID
        'status',               // 状态（0:未认领, 1:已认领, 2:部分认领）
        'amount',               // 收款金额
        'claimed_amount',       // 已认领金额
        'unclaimed_amount',     // 未认领金额
        'currency',             // 货币类型
        'payer',                // 付款人
        'payer_account',        // 付款人账户
        'bank_account',         // 收款银行账户
        'payment_method',       // 付款方式
        'transaction_ref',      // 交易凭证号
        'received_date',        // 收款日期
        'claimed_by',           // 认领人ID
        'claimed_at',           // 认领时间
        'remark',               // 备注
        'created_by',           // 创建人ID
    ];

    protected $dates = ['deleted_at', 'claimed_at', 'received_date'];

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
     * 关联认领人
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * 关联请款单(多对多)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentRequests()
    {
        return $this->belongsToMany(
            PaymentRequest::class,
            'payment_received_requests',
            'payment_received_id',
            'payment_request_id'
        )->withPivot('allocated_amount')->withTimestamps();
    }

    /**
     * 关联核销记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function writeOffs()
    {
        return $this->hasMany(WriteOff::class, 'payment_received_id');
    }
}

