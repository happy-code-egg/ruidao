<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户等级模型
 * 用于管理客户等级信息
 */
class CustomerLevel extends Model
{
    // 指定数据库表名
    protected $table = 'customer_levels';

    // 定义可批量赋值的字段
    protected $fillable = [
        'sort',           // 排序字段
        'level_name',     // 等级名称
        'level_code',     // 等级编码
        'description',    // 描述信息
        'is_valid',       // 是否有效（状态）
        'created_by',     // 创建人ID
        'updated_by',     // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'sort' => 'integer',           // 排序字段转为整数类型
        'is_valid' => 'integer',       // 状态字段转为整数类型
        'created_at' => 'datetime',    // 创建时间转为日期时间类型
        'updated_at' => 'datetime',    // 更新时间转为日期时间类型
    ];

    /**
     * 关联更新者信息
     * 通过 updated_by 字段关联 User 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

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
     * 状态常量定义
     */
    const STATUS_DISABLED = 0;  // 禁用状态
    const STATUS_ENABLED = 1;   // 启用状态

    /**
     * 作用域：启用状态
     * 用于查询启用状态的客户等级记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', self::STATUS_ENABLED);
    }

    /**
     * 作用域：按排序排列
     * 用于按 sort 字段和 id 字段排序查询结果
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 获取状态文本
     * 根据 is_valid 字段值返回对应的状态文本
     * @return string 状态文本（'启用' 或 '禁用'）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '启用' : '禁用';
    }

    /**
     * 关联客户信息
     * 通过 customer_level 字段关联 Customer 模型
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_level', 'id');
    }
}
