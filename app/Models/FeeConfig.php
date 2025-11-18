<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 费用配置模型
 * 用于管理系统中的费用标准配置信息，支持不同类型、不同国家和不同业务场景的费用定义
 */
class FeeConfig extends Model
{
    protected $table = 'fee_configs';

    protected $fillable = [
        'name',              // 配置名称
        'type',              // 类型
        'amount',            // 金额
        'unit',              // 单位
        'description',       // 描述
        'status',            // 状态
        'sort_order',        // 排序顺序
        'sort',              // 排序字段
        'case_type',         // 案件类型（数组）
        'business_type',     // 业务类型（数组）
        'apply_type',        // 申请类型（数组）
        'country',           // 国家（数组）
        'fee_type',          // 费用类型
        'fee_name',          // 费用名称
        'fee_name_en',       // 费用英文名称
        'currency',          // 货币类型
        'fee_code',          // 费用编码
        'base_fee',          // 基础费用
        'small_entity_fee',  // 小型实体费用
        'micro_entity_fee',  // 微型实体费用
        'role',              // 角色（数组）
        'use_stage',         // 使用阶段
        'is_valid',          // 是否有效
        'updated_by',        // 更新人ID
        'created_by',        // 创建人ID
    ];

    protected $casts = [
        'sort' => 'integer',
        'case_type' => 'array',
        'business_type' => 'array',
        'apply_type' => 'array',
        'country' => 'array',
        'role' => 'array',
        'use_stage' => 'array',
        'base_fee' => 'decimal:2',
        'small_entity_fee' => 'decimal:2',
        'micro_entity_fee' => 'decimal:2',
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * 格式化创建时间
     * 将创建时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化更新时间
     * 将更新时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为对应的中文状态文本
     * @return string 状态文本（"有效"或"无效"）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
    }

    /**
     * 作用域：有效状态
     * 用于查询 is_valid=1 的有效费用配置
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序
     * 按照 sort_order、sort 和 id 字段进行排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('sort')->orderBy('id');
    }

    /**
     * 格式化更新时间显示
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * 格式化业务类型显示
     */
    public function getBusinessTypeDisplayAttribute()
    {
        if (is_array($this->business_type)) {
            return implode(', ', $this->business_type);
        }
        return $this->business_type;
    }

    /**
     * 格式化申请类型显示
     */
    public function getApplyTypeDisplayAttribute()
    {
        if (is_array($this->apply_type)) {
            return implode(', ', $this->apply_type);
        }
        return $this->apply_type;
    }
}