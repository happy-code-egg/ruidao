<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 请款明细模型
 * 存储请款单中各项费用的详细信息
 */
class PaymentRequestDetail extends Model
{
    protected $table = 'payment_request_details';

    protected $fillable = [
        'request_id',           // 请款单ID
        'case_id',              // 案件ID
        'case_fee_id',          // 案件费用ID
        'fee_type',             // 费用类型
        'fee_name',             // 费用名称
        'quantity',             // 数量
        'unit_price',           // 单价
        'amount',               // 金额
        'currency',             // 货币类型
        'fee_description',      // 费用描述
        'invoice_number',       // 发票号码
    ];

    public $timestamps = true;

    /**
     * 关联请款单
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class, 'request_id');
    }

    /**
     * 关联案件
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    /**
     * 关联案件费用
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caseFee()
    {
        return $this->belongsTo(CaseFee::class, 'case_fee_id');
    }
}

