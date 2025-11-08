<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 科技服务地区模型，用于管理科技服务的地区相关信息
 */
class TechServiceRegion extends Model
{
    protected $table = 'tech_service_regions';

    protected $fillable = [
        'apply_type',        // 适用类型
        'service_name',      // 服务名称
        'service_level',     // 服务级别
        'main_area',         // 主要区域
        'project_year',      // 项目年份
        'deadline',          // 截止日期
        'batch_number',      // 批次号
        'is_valid',          // 是否有效：1-有效，0-无效
        'sort_order',        // 排序序号
        'updater',           // 更新人ID
        'description',       // 描述信息
        'status'             // 状态：1-启用，0-禁用
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
     * 格式化截止日期
     * @param string|null $value 数据库中的截止日期值
     * @return string 格式化后的日期字符串
     */
    public function getDeadlineAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
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
     * 获取有效性文本
     * @return string 有效性文本（有效/无效）
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
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
     * 作用域：有效状态
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
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

    /**
     * 关联科技服务事项（一对多）
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function techServiceItems()
    {
        return $this->hasMany(TechServiceItem::class, 'tech_service_region_id');
    }
}
