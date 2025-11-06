<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 版权加急类型模型
 * 用于管理版权申请的加急服务类型信息
 */
class CopyrightExpediteTypes extends Model
{
    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'copyright_expedite_types';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'name',          // 加急类型名称
        'code',          // 加急类型编码
        'days',          // 处理天数
        'extra_fee',     // 额外费用
        'description',   // 描述信息
        'status',        // 状态(1为启用，其他为禁用)
        'sort_order'     // 排序字段
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'status' => 'integer',      // 状态字段转换为整数
        'sort_order' => 'integer'   // 排序字段转换为整数
    ];

    /**
     * 获取状态文本的访问器
     * 将数字状态转换为中文文本显示
     * @return string 状态文本('启用'或'禁用')
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 启用状态范围查询
     * 查询状态为启用(值为1)的记录
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 排序范围查询
     * 按照 `sort_order` 字段和 `id` 进行升序排列
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
