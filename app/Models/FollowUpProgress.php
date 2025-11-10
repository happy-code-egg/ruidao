<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 跟进进度模型
 * 用于管理客户跟进的各种进度状态及其完成百分比
 */
class FollowUpProgress extends Model
{
    protected $table = 'follow_up_progresses';

    protected $fillable = [
        'name',         // 进度名称
        'code',         // 进度编码
        'percentage',   // 完成百分比
        'description',  // 描述
        'status',       // 状态（1:启用，0:禁用）
        'sort_order'    // 排序顺序
    ];

    protected $casts = [
        'percentage' => 'integer',
        'status' => 'integer',
        'sort_order' => 'integer'
    ];

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
     * 获取格式化进度
     * 将百分比数值转换为带百分号的字符串格式
     * @return string 格式化后的百分比文本（如"80%"）
     */
    public function getFormattedPercentageAttribute()
    {
        return $this->percentage . '%';
    }

    /**
     * 作用域：启用状态
     * 用于查询 status=1 的启用状态跟进进度
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
