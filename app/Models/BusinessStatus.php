<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 业务状态模型
 * 用于管理系统中各类业务的状态配置，包含状态名称、有效性等信息
 */
class BusinessStatus extends Model
{
    // 指定对应的数据库表名
    protected $table = 'business_statuses';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'status_name',    // 状态名称
        'is_valid',       // 是否有效(布尔值)
        'sort',           // 排序字段
        'updated_by'      // 更新者
    ];

    // 字段类型转换定义
    protected $casts = [
        'is_valid' => 'boolean',  // 是否有效 - 布尔类型
        'sort' => 'integer'       // 排序 - 整数类型
    ];

    /**
     * 获取状态文本
     * 根据 is_valid 字段值返回对应的中文状态描述
     * @return string 状态文本（是或否）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：启用状态
     * 查询作用域 - 只获取有效状态(is_valid=true)的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 查询作用域 - 按 sort 字段和 id 字段进行升序排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 处理API响应格式
     * 自定义模型数组序列化格式，指定返回给API的字段及其格式
     * @return array 格式化后的API响应数据
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'statusName' => $this->status_name,
            'isValid' => $this->is_valid,
            'updatedBy' => $this->updated_by ?: '系统记录',  // 如果更新者为空则显示"系统记录"
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '系统记录'  // 格式化更新时间
        ];
    }
}
