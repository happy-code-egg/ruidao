<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 案例阶段模型
 * 定义系统中的案例处理阶段，包含阶段名称、编码、国家和案件类型关联等信息
 */
class CasePhases extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_phases';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 阶段名称
        'code',           // 阶段编码
        'description',    // 描述信息
        'status',         // 状态(1:启用, 0:禁用)
        'sort_order',     // 排序顺序
        'country',        // 国家
        'case_type',      // 案件类型
        'phase_name',     // 阶段名称
        'phase_name_en',  // 阶段英文名称
        'created_by',     // 创建者ID
        'updated_by'      // 更新者ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'status' => 'integer',      // 状态 - 整数类型
        'sort_order' => 'integer',  // 排序顺序 - 整数类型
        'created_by' => 'integer',  // 创建者ID - 整数类型
        'updated_by' => 'integer'   // 更新者ID - 整数类型
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
     * 用于查询 status=1 的启用状态案例阶段
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
