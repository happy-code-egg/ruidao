<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechServiceRegion extends Model
{
    protected $table = 'tech_service_regions';

    protected $fillable = [
        'apply_type',
        'service_name',
        'service_level',
        'main_area',
        'project_year',
        'deadline',
        'batch_number',
        'is_valid',
        'sort_order',
        'updater',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'integer',
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'batch_number' => 'integer',
        'deadline' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }
    
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getDeadlineAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 获取有效性文本
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
    }

    /**
     * 作用域：启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：有效状态
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 关联科技服务事项
     */
    public function techServiceItems()
    {
        return $this->hasMany(TechServiceItem::class, 'tech_service_region_id');
    }
}
