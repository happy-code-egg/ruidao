<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFee extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_fees';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'case_id',              // 案例ID
        'fee_type',             // 费用类型
        'fee_name',             // 费用名称
        'fee_description',      // 费用描述
        'amount',               // 金额
        'currency',             // 货币类型
        'payment_deadline',     // 缴费截止日期
        'receivable_date',      // 应收日期
        'actual_receive_date',  // 实际收款日期
        'payment_status',       // 支付状态
        'is_reduction',         // 是否减免(布尔值)
        'remarks',              // 备注
    ];

    // 字段类型转换定义
    protected $casts = [
        'case_id' => 'integer',             // 案例ID - 整数类型
        'amount' => 'decimal:2',            // 金额 - 精确到小数点后2位的十进制数
        'payment_deadline' => 'date',       // 缴费截止日期 - 日期类型
        'receivable_date' => 'date',        // 应收日期 - 日期类型
        'actual_receive_date' => 'date',    // 实际收款日期 - 日期类型
        'is_reduction' => 'boolean',        // 是否减免 - 布尔类型
        'created_at' => 'datetime',         // 创建时间 - 日期时间类型
        'updated_at' => 'datetime',         // 更新时间 - 日期时间类型
    ];

    /**
     * 获取关联的案例
     * 建立与 `Cases` 模型的一对多反向关联
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 费用类型常量
     */
    const TYPE_APPLICATION = 'application';   // 申请费
    const TYPE_EXAMINATION = 'examination';   // 审查费
    const TYPE_ANNUAL = 'annual';            // 年费
    const TYPE_REGISTRATION = 'registration'; // 登记费
    const TYPE_SERVICE = 'service';          // 服务费
    const TYPE_OFFICIAL = 'official';        // 官费

    /**
     * 支付状态常量
     */
    const STATUS_UNPAID = 0;    // 未缴费
    const STATUS_PAID = 1;      // 已缴费
    const STATUS_OVERDUE = 2;   // 逾期

    /**
     * 获取费用类型文本
     * 根据 `fee_type` 字段值返回对应的中文费用类型描述
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
     * 根据 `payment_status` 字段值返回对应的中文支付状态描述
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
     * 判断当前费用是否已经超过缴费截止日期且未支付
     */
    public function isOverdue()
    {
        // 如果没有设置缴费截止日期或者已经支付，则不逾期
        if (!$this->payment_deadline || $this->payment_status === self::STATUS_PAID) {
            return false;
        }

        // 比较缴费截止日期与当前日期，判断是否逾期
        return $this->payment_deadline < now()->toDateString();
    }
}
