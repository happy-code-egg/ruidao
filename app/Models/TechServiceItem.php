<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechServiceItem extends Model
{
    protected $table = 'tech_service_items';

    protected $fillable = [
        'tech_service_type_id',
        'tech_service_region_id',
        'name',
        'code',
        'description',
        'expected_start_date',
        'internal_deadline',
        'official_deadline',
        'status',
        'sort_order',
        'updater'
    ];

    protected $casts = [
        'tech_service_type_id' => 'integer',
        'tech_service_region_id' => 'integer',
        'status' => 'integer',
        'sort_order' => 'integer',
        'expected_start_date' => 'date',
        'internal_deadline' => 'date',
        'official_deadline' => 'date'
    ];

    /**
     * 关联科技服务类型
     */
    public function techServiceType()
    {
        return $this->belongsTo(TechServiceTypes::class, 'tech_service_type_id');
    }

    /**
     * 关联科技服务地区
     */
    public function techServiceRegion()
    {
        return $this->belongsTo(TechServiceRegion::class, 'tech_service_region_id');
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
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 启用状态查询范围
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 排序查询范围
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
