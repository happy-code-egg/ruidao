<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 证件类型模型
 * 用于管理各种身份证件类型的配置信息
 */
class IdTypes extends Model
{
    protected $table = 'id_types';
    
    protected $fillable = [
        'name',         // 证件名称
        'code',         // 证件代码
        'description',  // 描述
        'status',       // 状态（1:启用，0:禁用）
        'sort_order',   // 排序顺序
        'type_name',    // 类型名称
        'type_code',    // 类型代码
        'created_by',   // 创建人ID
        'updated_by'    // 更新人ID
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

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
     * 作用域：有效状态
     * 用于查询 status=1 的启用状态证件类型
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
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