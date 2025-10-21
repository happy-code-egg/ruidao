<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContract extends Model
{
    use SoftDeletes;

    protected $table = 'customer_contracts';

    protected $fillable = [
        'customer_id',
        'business_opportunity_id',
        'contract_no',
        'contract_name',
        'contract_amount',
        'sign_date',
        'start_date',
        'end_date',
        'contract_type',
        'status',
        'business_person_id',
        'contract_content',
        'payment_method',
        'paid_amount',
        'unpaid_amount',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'business_opportunity_id' => 'integer',
        'contract_amount' => 'decimal:2',
        'sign_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'business_person_id' => 'integer',
        'paid_amount' => 'decimal:2',
        'unpaid_amount' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 合同状态常量
     */
    const STATUS_EXECUTING = '执行中';
    const STATUS_COMPLETED = '已完成';
    const STATUS_TERMINATED = '已终止';

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取商机
     */
    public function businessOpportunity()
    {
        return $this->belongsTo(BusinessOpportunity::class, 'business_opportunity_id');
    }

    /**
     * 获取业务人员
     */
    public function businessPerson()
    {
        return $this->belongsTo(User::class, 'business_person_id');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取合同状态标签类型
     */
    public function getStatusTagTypeAttribute()
    {
        $statusMap = [
            self::STATUS_EXECUTING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_TERMINATED => 'danger'
        ];
        return $statusMap[$this->status] ?? 'info';
    }

    /**
     * 获取付款进度百分比
     */
    public function getPaymentProgressAttribute()
    {
        if ($this->contract_amount == 0) {
            return 0;
        }
        return round(($this->paid_amount / $this->contract_amount) * 100, 2);
    }

    /**
     * 生成合同编号
     */
    public static function generateContractNo()
    {
        $prefix = 'HT' . date('Ymd');
        $lastContract = static::withTrashed()
            ->where('contract_no', 'like', $prefix . '%')
            ->orderBy('contract_no', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = intval(substr($lastContract->contract_no, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
