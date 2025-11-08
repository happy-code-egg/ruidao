<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 代理商等级模型
 * 代表系统中的代理商等级设置，包含等级名称、编码、佣金比率等信息
 */
class AgentLevels extends Model
{
    // 指定对应的数据库表名
    protected $table = 'agent_levels';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 等级名称
        'code',           // 等级编码
        'description',    // 描述信息
        'status',         // 状态(1:启用, 0:禁用)
        'sort_order',     // 排序顺序
        'level_name',     // 级别名称
        'level_code',     // 级别编码
        'commission_rate', // 佣金比率
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
     * 用于查询 status=1 的启用状态代理商等级
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
