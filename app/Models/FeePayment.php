<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 缴费单模型
 * 用于管理缴费单的基本信息和关联数据
 */
class FeePayment extends Model
{
    use SoftDeletes;

    protected $table = 'fee_payments';

    protected $fillable = [
        'payment_no',        // 缴费单编号
        'payment_name',      // 缴费单名称
        'customer_id',       // 客户ID
        'customer_name',     // 客户名称
        'company_id',        // 出款公司ID
        'company_name',      // 出款公司名称
        'agency_id',         // 代理机构ID
        'agency_name',       // 代理机构名称
        'status',            // 状态
        'total_amount',      // 总金额
        'currency',          // 币种
        'payment_date',      // 缴费日期
        'actual_payment_date', // 实际缴费日期
        'payment_method',    // 缴费方式
        'payment_account',   // 付款账户
        'remark',            // 备注
        'creator_id',        // 创建人ID
        'creator_name',      // 创建人名称
        'modifier_id',       // 修改人ID
        'modifier_name',     // 修改人名称
        'submitted_at',      // 提交时间
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_date' => 'date',
        'actual_payment_date' => 'date',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联缴费单明细
     */
    public function items()
    {
        return $this->hasMany(FeePaymentItem::class, 'fee_payment_id');
    }

    /**
     * 关联缴费单历史
     */
    public function history()
    {
        return $this->hasMany(FeePaymentHistory::class, 'fee_payment_id');
    }

    /**
     * 关联客户信息
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联公司信息
     */
    public function company()
    {
        return $this->belongsTo(OurCompanies::class, 'company_id');
    }

    /**
     * 关联代理机构
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}

