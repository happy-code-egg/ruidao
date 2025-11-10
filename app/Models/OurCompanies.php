<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 本公司信息模型
 * 用于管理系统中本公司的详细信息，包括基本信息、财务信息和联系方式等
 */
class OurCompanies extends Model
{
    protected $table = 'our_companies';

    protected $fillable = [
        'name',          // 公司名称
        'code',          // 公司代码
        'short_name',    // 简称
        'full_name',     // 全称
        'credit_code',   // 统一社会信用代码
        'address',       // 地址
        'contact_person', // 联系人
        'contact_phone', // 联系电话
        'tax_number',    // 税号
        'bank',          // 开户银行
        'account',       // 银行账户
        'invoice_phone', // 发票电话
        'status',        // 状态（0:禁用, 1:启用）
        'sort_order',    // 排序顺序
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
     * 获取创建人
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
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
     * 获取更新人
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 查询 status = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEnabled($query)
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
