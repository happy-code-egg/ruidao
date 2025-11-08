<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 地区模型
 * 用于管理系统中的地区信息配置，支持多级地区结构
 */
class Regions extends Model
{
    protected $table = 'regions';
    
    protected $fillable = [
        'name',          // 名称
        'code',          // 编码
        'description',   // 描述
        'status',        // 状态（1:启用, 0:禁用）
        'sort_order',    // 排序顺序
        'region_name',   // 地区名称
        'region_code',   // 地区编码
        'parent_id',     // 父地区ID
        'level',         // 地区级别
        'created_by',    // 创建人ID
        'updated_by'     // 更新人ID
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * 获取创建人
     * 通过 created_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     * 通过 updated_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 作用域：启用状态
     * 查询 status = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
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
}