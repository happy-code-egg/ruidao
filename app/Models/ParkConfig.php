<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 园区配置模型
 * 用于管理系统中的园区信息配置
 */
class ParkConfig extends Model
{
    protected $table = 'parks_config';

    protected $fillable = [
        'park_name',      // 园区名称
        'park_code',      // 园区代码
        'description',    // 园区描述
        'address',        // 园区地址
        'contact_person', // 联系人
        'contact_phone',  // 联系电话
        'is_valid',       // 是否有效（0:无效, 1:有效）
        'sort_order',     // 排序顺序
        'created_by',     // 创建人ID
        'updated_by',     // 更新人ID
    ];

    protected $casts = [
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 格式化创建时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化更新时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 作用域：启用状态
     * 查询 is_valid = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', self::STATUS_ENABLED);
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

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '启用' : '禁用';
    }

    /**
     * 获取完整地址
     * 组合园区名称和地址信息
     * @return string 完整地址（园区名称 - 地址）
     */
    public function getFullAddressAttribute()
    {
        return $this->park_name . ($this->address ? ' - ' . $this->address : '');
    }

    /**
     * 获取关联的客户
     * 通过 `park_id` 字段一对多关联 `Customer` 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'park_id', 'id');
    }
}
