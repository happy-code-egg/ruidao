<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestDetail extends Model
{
    protected $table = 'payment_request_details';

    protected $fillable = [
        'request_id',
        'case_id',
        'case_fee_id',
        'fee_type',
        'fee_name',
        'quantity',
        'unit_price',
        'amount',
        'currency',
        'fee_description',
        'invoice_number',
    ];

    public $timestamps = true;

    /**
     * 关联请款单
     */
    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class, 'request_id');
    }

    /**
     * 关联案件
     */
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    /**
     * 关联案件费用
     */
    public function caseFee()
    {
        return $this->belongsTo(CaseFee::class, 'case_fee_id');
    }
}

