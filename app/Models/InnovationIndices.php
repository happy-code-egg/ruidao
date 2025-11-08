<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 创新指标模型
 * 用于管理各种创新相关的指标数据和统计信息
 */
class InnovationIndices extends Model
{
    protected $table = 'innovation_indices';

    protected $fillable = [
        'name',         // 指标名称
        'code',         // 指标代码
        'index_name',   // 索引名称
        'description',  // 描述
        'base_value',   // 基准值
        'current_value',// 当前值
        'status',       // 状态（1:启用，0:禁用）
        'sort_order',   // 排序顺序
        'updated_by',   // 更新人ID
        'created_by'    // 创建人ID
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * 格式化创建时间
     * 将创建时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 格式化更新时间
     * 将更新时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（"启用"或"禁用"）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 用于查询 status=1 的启用状态创新指标
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序
     * 按照 sort_order 和 id 字段进行排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
