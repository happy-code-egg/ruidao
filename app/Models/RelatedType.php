<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 关联类型模型
 * 用于管理系统中的关联类型配置，如案例之间的关联关系类型
 */
class RelatedType extends Model
{
    protected $table = 'related_types';

    protected $fillable = [
        'case_type',   // 案例类型
        'type_name',   // 类型名称
        'type_code',   // 类型编码
        'description', // 描述
        'is_valid',    // 是否有效（1:有效, 0:无效）
        'sort_order',  // 排序顺序
        'updater',     // 更新人
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 作用域：启用状态
     * 查询 is_valid = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', self::STATUS_ENABLED);
    }

    /**
     * 作用域：按排序排列
     * 先按 sort_order 字段排序，再按 id 字段排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 作用域：按案例类型筛选
     * 根据案例类型筛选关联类型
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $caseType 案例类型
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeByCaseType($query, $caseType)
    {
        return $query->where('case_type', $caseType);
    }

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '启用' : '禁用';
    }
}