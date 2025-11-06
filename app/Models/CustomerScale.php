<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 客户规模模型
 * 用于管理客户规模信息
 */
class CustomerScale extends Model
{
    // 指定数据库表名
    protected $table = 'customer_scales';

    // 定义可批量赋值的字段
    protected $fillable = [
        'scale_name',    // 规模名称
        'is_valid',      // 是否有效
        'sort',          // 排序
        'created_by',    // 创建人ID
        'updated_by'     // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'is_valid' => 'boolean',    // 有效状态转为布尔类型
        'sort' => 'integer',        // 排序字段转为整数类型
        'created_by' => 'integer',  // 创建人ID转为整数类型
        'updated_by' => 'integer'   // 更新人ID转为整数类型
    ];

    /**
     * 格式化 created_at 时间
     * 将创建时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     * 将更新时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 获取状态文本
     * 根据 `is_valid` 字段值返回对应的状态文本
     * @return string 状态文本（'是' 或 '否'）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：启用状态
     * 用于查询启用状态的客户规模记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 用于按 `sort` 字段和 `id` 字段排序查询结果
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 处理API响应格式
     * 自定义模型数组序列化格式，适配前端API需求
     * @return array 格式化后的数组
     */
    public function toArray()
    {
        // 如果是select查询，保持原始字段
        if (isset($this->attributes['value']) || isset($this->attributes['label'])) {
            return parent::toArray();
        }

        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'scaleName' => $this->scale_name,
            'isValid' => $this->is_valid,
            'updatedAt' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',
            'updated_by' => $this->updater->real_name ?? ''
        ];
    }
}
