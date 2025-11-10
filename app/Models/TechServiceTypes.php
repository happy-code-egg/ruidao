<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 科技服务类型模型，用于管理科技服务的分类信息
 */
class TechServiceTypes extends Model
{
    protected $table = 'tech_service_types';

    protected $fillable = [
        'name',              // 服务类型名称
        'code',              // 服务类型编码
        'apply_type',        // 适用类型
        'description',       // 描述信息
        'status',            // 状态：1-启用，0-禁用
        'sort_order',        // 排序序号
        'updater',           // 更新人ID
        'created_at',        // 创建时间
        'updated_at'         // 更新时间
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
    ];
    
    /**
     * 关联科技服务事项（一对多）
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(TechServiceItem::class, 'type_id', 'id');
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
     * 获取所有关联的科技服务事项
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getItemsAttribute()
    {
        return $this->items()->get();
    }

    /**
     * 获取状态文本
     * @return string 状态文本（启用/禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
