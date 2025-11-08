<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 流程系数模型，用于管理业务流程中的各类系数信息
 */
class ProcessCoefficient extends Model
{

    protected $table = 'process_coefficients';

    protected $fillable = [
        'name',        // 系数名称
        'sort',        // 排序序号
        'is_valid',    // 是否有效：1-有效，0-无效
        'created_by',  // 创建人ID
        'updated_by',  // 更新人ID
        'created_at',  // 创建时间
        'updated_at',  // 更新时间
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_valid' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取更新者（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
     * 作用域：有效的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc');
    }

    /**
     * 获取状态文本
     * @return string 状态文本（是/否）
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }
}
