<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 关联类型模型
 * 用于管理系统中的各类关联关系类型配置
 */
class RelatedTypes extends Model
{
    protected $table = 'related_types';

    protected $fillable = [
        'name',         // 类型名称
        'code',         // 类型编码
        'category',     // 分类
        'description',  // 描述信息
        'status',       // 状态（1:启用, 0:禁用）
        'sort_order'    // 排序顺序
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 用于查询 status=1 的启用状态关联类型
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
