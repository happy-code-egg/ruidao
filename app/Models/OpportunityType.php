<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 商机类型模型
 * 用于管理系统中的商机类型配置
 */
class OpportunityType extends Model
{
    protected $table = 'opportunity_types';

    protected $fillable = [
        'status_name',  // 状态名称
        'is_valid',     // 是否有效
        'sort',         // 排序号
        'updated_by'    // 更新人ID
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sort' => 'integer'
    ];

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为对应的中文状态文本
     * @return string 状态文本（是或否）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：启用状态
     * 查询 is_valid = true 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 先按 sort 字段排序，再按 id 字段排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 处理API响应格式
     * 自定义模型的数组序列化格式，转换字段名为驼峰式，并处理日期格式和默认值
     * @return array 格式化后的API响应数组
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'statusName' => $this->status_name,
            'isValid' => $this->is_valid,
            'updatedBy' => $this->updated_by ?: '系统记录',
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '系统记录'
        ];
    }
}
