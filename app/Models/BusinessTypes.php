<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 业务类型模型
 * 用于管理系统中的业务类型配置，支持业务分类和状态管理
 */
class BusinessTypes extends Model
{
    // 指定对应的数据库表名
    protected $table = 'business_types';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 业务类型名称
        'code',           // 业务类型编码
        'description',    // 描述信息
        'status',         // 状态(1:启用, 0:禁用)
        'sort_order',     // 排序顺序
        'type_name',      // 类型名称
        'category',       // 分类
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
     * 用于查询 status=1 的启用状态业务类型
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
