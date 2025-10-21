<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessOpportunity extends Model
{
    use SoftDeletes;

    protected $table = 'business_opportunities';

    protected $fillable = [
        'customer_id',
        'business_code',
        'name',
        'contact_person',
        'contact_phone',
        'business_person_id',
        'next_time',
        'second_time',
        'content',
        'case_type',
        'business_type',
        'estimated_amount',
        'estimated_sign_time',
        'status',
        'is_contract',
        'background',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'business_person_id' => 'integer',
        'next_time' => 'datetime',
        'second_time' => 'date',
        'estimated_amount' => 'decimal:2',
        'estimated_sign_time' => 'date',
        'is_contract' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 商机状态常量
     */
    const STATUS_GENERAL = '一般';
    const STATUS_IMPORTANT = '重要';
    const STATUS_URGENT = '紧急';

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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
     * 获取跟进记录
     */
    public function followupRecords()
    {
        return $this->hasMany(CustomerFollowupRecord::class, 'business_opportunity_id');
    }

    /**
     * 获取合同
     */
    public function contracts()
    {
        return $this->hasMany(CustomerContract::class, 'business_opportunity_id');
    }

    /**
     * 获取状态标签类型
     */
    public function getStatusTagTypeAttribute()
    {
        $statusMap = [
            self::STATUS_GENERAL => 'info',
            self::STATUS_IMPORTANT => 'warning',
            self::STATUS_URGENT => 'danger'
        ];
        return $statusMap[$this->status] ?? 'info';
    }

    /**
     * 生成商机编号
     */
    public static function generateCode()
    {
        $prefix = 'BIZ' . date('Ymd');
        $lastOpportunity = static::withTrashed()
            ->where('business_code', 'like', $prefix . '%')
            ->orderBy('business_code', 'desc')
            ->first();

        if ($lastOpportunity) {
            $lastNumber = intval(substr($lastOpportunity->business_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}