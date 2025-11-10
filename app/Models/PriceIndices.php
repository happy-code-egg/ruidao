<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 价格指数模型，用于管理各类价格指数的信息数据
 */
class PriceIndices extends Model
{
    protected $table = 'price_indices';

    protected $fillable = [
        'name',          // 指数名称
        'code',          // 指数编码
        'index_name',    // 指数标识名称
        'description',   // 描述信息
        'base_value',    // 基础值
        'current_value', // 当前值
        'status',        // 状态：1-启用，0-禁用
        'sort_order',    // 排序序号
        'updated_by',    // 更新人ID
        'created_by'     // 创建人ID
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
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
     * 关联创建人（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
