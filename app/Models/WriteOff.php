<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriteOff extends Model
{
    use SoftDeletes;

    protected $table = 'write_offs';

    protected $fillable = [
        'write_off_no',
        'payment_received_id',
        'payment_request_id',
        'customer_id',
        'contract_id',
        'write_off_amount',
        'write_off_date',
        'status',
        'remark',
        'write_off_by',
        'write_off_at',
        'reverted_by',
        'reverted_at',
        'revert_reason',
        'created_by',
    ];

    protected $dates = ['deleted_at', 'write_off_at', 'reverted_at', 'write_off_date'];

    /**
     * 关联到款单
     */
    public function paymentReceived()
    {
        return $this->belongsTo(PaymentReceived::class, 'payment_received_id');
    }

    /**
     * 关联请款单
     */
    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class, 'payment_request_id');
    }

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
     * 关联核销人
     */
    public function writeOffUser()
    {
        return $this->belongsTo(User::class, 'write_off_by');
    }

    /**
     * 关联撤销人
     */
    public function revertUser()
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    /**
     * 关联创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 生成核销单号
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

