<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLevel extends Model
{
    protected $table = 'customer_levels';

    protected $fillable = [
        'sort',
        'level_name',
        'level_code',
        'description',
        'is_valid',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_valid' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取更新者
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

                /**
     * 格式化 created_at 时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     */
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
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '启用' : '禁用';
    }

    /**
     * 获取客户
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_level', 'id');
    }
}