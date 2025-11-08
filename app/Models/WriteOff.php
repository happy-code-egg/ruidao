<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 核销模型
 * 用于管理系统中的财务核销记录，关联到款单和请款单
 */
class WriteOff extends Model
{
    use SoftDeletes;

    protected $table = 'write_offs';

    protected $fillable = [
        'write_off_no',         // 核销单号
        'payment_received_id',  // 到款单ID
        'payment_request_id',   // 请款单ID
        'customer_id',          // 客户ID
        'contract_id',          // 合同ID
        'write_off_amount',     // 核销金额
        'write_off_date',       // 核销日期
        'status',               // 状态（1:已核销, 0:已撤销）
        'remark',               // 备注
        'write_off_by',         // 核销人ID
        'write_off_at',         // 核销时间
        'reverted_by',          // 撤销人ID
        'reverted_at',          // 撤销时间
        'revert_reason',        // 撤销原因
        'created_by',           // 创建人ID
    ];

    protected $dates = ['deleted_at', 'write_off_at', 'reverted_at', 'write_off_date'];

    /**
     * 关联到款单
     * 通过 payment_received_id 字段关联 PaymentReceived 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentReceived()
    {
        return $this->belongsTo(PaymentReceived::class, 'payment_received_id');
    }

    /**
     * 关联请款单
     * 通过 payment_request_id 字段关联 PaymentRequest 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class, 'payment_request_id');
    }

    /**
     * 关联客户
     * 通过 customer_id 字段关联 Customer 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联合同
     * 通过 contract_id 字段关联 Contract 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 关联核销人
     * 通过 write_off_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function writeOffUser()
    {
        return $this->belongsTo(User::class, 'write_off_by');
    }

    /**
     * 关联撤销人
     * 通过 reverted_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revertUser()
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    /**
     * 关联创建人
     * 通过 created_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 生成核销单号
     * 规则：HX + 年月日 + 4位序号
     * @return string 核销单号
     */
    public static function generateWriteOffNo()
    {
        $prefix = 'HX';
        $date = date('Ymd');
        $lastRecord = self::where('write_off_no', 'like', $prefix . $date . '%')
            ->orderBy('write_off_no', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNo = intval(substr($lastRecord->write_off_no, -4));
            $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }

        return $prefix . $date . $newNo;
    }
}

