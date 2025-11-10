<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 实体类型模型
 * 用于管理各种实体类型的配置信息
 */
class EntityTypes extends Model
{
    // 指定数据库表名
    protected $table = 'entity_types';

    // 定义可批量赋值的字段
    protected $fillable = [
        'name',         // 类型名称
        'code',         // 类型编码
        'description',  // 类型描述
        'status',       // 状态（1:启用, 0:禁用）
        'sort_order',   // 排序顺序
        'type_name',    // 类型名称（可能与name字段有不同用途）
        'created_by',   // 创建人ID
        'updated_by'    // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'status' => 'integer',      // 状态字段转为整数类型
        'sort_order' => 'integer',  // 排序字段转为整数类型
        'created_by' => 'integer',  // 创建人ID转为整数类型
        'updated_by' => 'integer'   // 更新人ID转为整数类型
    ];

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 作用域：激活状态
     * 用于查询状态为启用（status=1）的实体类型记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序排列
     * 用于按 `sort_order` 字段和 `id` 字段排序查询结果
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
