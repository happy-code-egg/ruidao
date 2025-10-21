<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentReceived extends Model
{
    use SoftDeletes;

    protected $table = 'payment_receiveds';

    protected $fillable = [
        'payment_no',
        'customer_id',
        'contract_id',
        'status',
        'amount',
        'claimed_amount',
        'unclaimed_amount',
        'currency',
        'payer',
        'payer_account',
        'bank_account',
        'payment_method',
        'transaction_ref',
        'received_date',
        'claimed_by',
        'claimed_at',
        'remark',
        'created_by',
    ];

    protected $dates = ['deleted_at', 'claimed_at', 'received_date'];

    /**
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联合同
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 关联创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联认领人
     */
    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * 关联请款单(多对多)
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
     */
    public function writeOffs()
    {
        return $this->hasMany(WriteOff::class, 'payment_received_id');
    }
}

