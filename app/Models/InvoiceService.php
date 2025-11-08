<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 发票服务类型模型
 * 用于管理发票开具的服务类型配置
 */
class InvoiceService extends Model
{
    protected $table = 'invoice_services';

    protected $fillable = [
        'service_name',   // 服务名称
        'service_code',   // 服务代码
        'description',    // 服务描述
        'is_valid',       // 是否有效（0:禁用, 1:启用）
        'sort_order',     // 排序序号
        'created_by',     // 创建人ID
        'updated_by',     // 更新人ID
    ];

    protected $casts = [
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取更新者
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取创建者
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 格式化 created_at 时间
     * 将时间戳转换为 Y-m-d H:i:s 格式的时间字符串
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     * 将时间戳转换为 Y-m-d H:i:s 格式的时间字符串
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 状态常量定义
     * STATUS_DISABLED: 禁用状态
     * STATUS_ENABLED: 启用状态
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
}
