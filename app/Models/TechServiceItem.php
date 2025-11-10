<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 科技服务事项模型，用于管理具体的科技服务项目信息
 */
class TechServiceItem extends Model
{
    protected $table = 'tech_service_items';

    protected $fillable = [
        'tech_service_type_id',    // 科技服务类型ID
        'tech_service_region_id',  // 科技服务地区ID
        'name',                    // 服务事项名称
        'code',                    // 服务事项编码
        'description',             // 描述信息
        'expected_start_date',     // 预计开始日期
        'internal_deadline',       // 内部截止日期
        'official_deadline',       // 官方截止日期
        'status',                  // 状态：1-启用，0-禁用
        'sort_order',              // 排序序号
        'updater'                  // 更新人ID
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
     * 关联科技服务类型（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function techServiceType()
    {
        return $this->belongsTo(TechServiceTypes::class, 'tech_service_type_id');
    }

    /**
     * 关联科技服务地区（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function techServiceRegion()
    {
        return $this->belongsTo(TechServiceRegion::class, 'tech_service_region_id');
    }
    
        /**
     * 格式化创建时间
     * @param string|null $value 数据库中的创建时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化更新时间
     * @param string|null $value 数据库中的更新时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
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
