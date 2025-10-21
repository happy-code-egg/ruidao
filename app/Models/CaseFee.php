<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFee extends Model
{
    protected $table = 'case_fees';

    protected $fillable = [
        'case_id',
        'fee_type',
        'fee_name',
        'fee_description',
        'amount',
        'currency',
        'payment_deadline',
        'receivable_date',
        'actual_receive_date',
        'payment_status',
        'is_reduction',
        'remarks',
    ];

    protected $casts = [
        'case_id' => 'integer',
        'amount' => 'decimal:2',
        'payment_deadline' => 'date',
        'receivable_date' => 'date',
        'actual_receive_date' => 'date',
        'is_reduction' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的案例
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 费用类型常量
     */
    const TYPE_APPLICATION = 'application';  // 申请费
    const TYPE_EXAMINATION = 'examination';  // 审查费
    const TYPE_ANNUAL = 'annual';           // 年费
    const TYPE_REGISTRATION = 'registration'; // 登记费
    const TYPE_SERVICE = 'service';         // 服务费
    const TYPE_OFFICIAL = 'official';       // 官费

    /**
     * 支付状态常量
     */
    const STATUS_UNPAID = 0;    // 未缴费
    const STATUS_PAID = 1;      // 已缴费
    const STATUS_OVERDUE = 2;   // 逾期

    /**
     * 获取费用类型文本
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_APPLICATION => '申请费',
            self::TYPE_EXAMINATION => '审查费',
            self::TYPE_ANNUAL => '年费',
            self::TYPE_REGISTRATION => '登记费',
            self::TYPE_SERVICE => '服务费',
            self::TYPE_OFFICIAL => '官费',
        ];

        return $types[$this->fee_type] ?? '其他';
    }

    /**
     * 获取支付状态文本
     */
    public function getPaymentStatusTextAttribute()
    {
        $statuses = [
            self::STATUS_UNPAID => '未缴费',
            self::STATUS_PAID => '已缴费',
            self::STATUS_OVERDUE => '逾期',
        ];

        return $statuses[$this->payment_status] ?? '未知';
    }

    /**
     * 检查是否逾期
     */
    public function isOverdue()
    {
        if (!$this->payment_deadline || $this->payment_status === self::STATUS_PAID) {
            return false;
        }

        return $this->payment_deadline < now()->toDateString();
    }
}


