<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRequest extends Model
{
    use SoftDeletes;

    protected $table = 'payment_requests';

    protected $fillable = [
        'request_no',
        'customer_id',
        'contract_id',
        'request_type',
        'request_status',
        'total_amount',
        'paid_amount',
        'unpaid_amount',
        'currency',
        'request_date',
        'due_date',
        'payment_terms',
        'bank_account',
        'created_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'remark',
        'approve_remark',
    ];

    protected $dates = ['deleted_at', 'submitted_at', 'approved_at'];

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
     * 关联审批人
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 关联请款明细
     */
    public function details()
    {
        return $this->hasMany(PaymentRequestDetail::class, 'request_id');
    }
}

