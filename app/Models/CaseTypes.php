<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 案件类型模型
 * 用于管理案件的各种类型信息
 */
class CaseTypes extends Model
{
    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'case_types';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'name',          // 类型名称
        'code',          // 类型编码
        'description',   // 类型描述
        'status',        // 状态(1为启用，其他为禁用)
        'sort_order',    // 排序字段
        'type_name',     // 类型显示名称
        'agency_name',   // 机构名称
        'created_by',    // 创建人ID
        'updated_by'     // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'status' => 'integer',      // 状态字段转换为整数
        'sort_order' => 'integer',  // 排序字段转换为整数
        'created_by' => 'integer',  // 创建人ID转换为整数
        'updated_by' => 'integer'   // 更新人ID转换为整数
    ];

    /**
     * 关联创建人信息
     * 建立与 `User` 模型的反向一对一关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 建立与 `User` 模型的反向一对一关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 状态范围查询
     * 用于查询启用状态(状态值为1)的记录
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
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
