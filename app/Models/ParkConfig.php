<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkConfig extends Model
{
    protected $table = 'parks_config';

    protected $fillable = [
        'park_name',
        'park_code',
        'description',
        'address',
        'contact_person',
        'contact_phone',
        'is_valid',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 作用域：启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', self::STATUS_ENABLED);
    }

    /**
     * 作用域：按排序排列
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '启用' : '禁用';
    }

    /**
     * 获取完整地址
     */
    public function getFullAddressAttribute()
    {
        return $this->park_name . ($this->address ? ' - ' . $this->address : '');
    }

    /**
     * 获取客户
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'park_id', 'id');
    }
}
