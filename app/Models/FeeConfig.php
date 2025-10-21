<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeConfig extends Model
{
    protected $table = 'fee_configs';

    protected $fillable = [
        'name',
        'type',
        'amount',
        'unit',
        'description',
        'status',
        'sort_order',
        'sort',
        'case_type',
        'business_type',
        'apply_type',
        'country',
        'fee_type',
        'fee_name',
        'fee_name_en',
        'currency',
        'fee_code',
        'base_fee',
        'small_entity_fee',
        'micro_entity_fee',
        'role',
        'use_stage',
        'is_valid',
        'updated_by',
        'created_by',
    ];

    protected $casts = [
        'sort' => 'integer',
        'case_type' => 'array',
        'business_type' => 'array',
        'apply_type' => 'array',
        'country' => 'array',
        'role' => 'array',
        'base_fee' => 'decimal:2',
        'small_entity_fee' => 'decimal:2',
        'micro_entity_fee' => 'decimal:2',
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
    }

    /**
     * 作用域：有效状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('sort')->orderBy('id');
    }

    /**
     * 格式化更新时间显示
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 格式化业务类型显示
     */
    public function getBusinessTypeDisplayAttribute()
    {
        if (is_array($this->business_type)) {
            return implode(', ', $this->business_type);
        }
        return $this->business_type;
    }

    /**
     * 格式化申请类型显示
     */
    public function getApplyTypeDisplayAttribute()
    {
        if (is_array($this->apply_type)) {
            return implode(', ', $this->apply_type);
        }
        return $this->apply_type;
    }
}