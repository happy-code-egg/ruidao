<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 产品模型
 * 用于管理系统中的产品信息，包括产品类型、价格和规格等
 */
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sort',          // 排序号
        'product_code',  // 产品编码
        'project_type',  // 项目类型
        'apply_type',    // 申请类型
        'specification', // 规格
        'product_name',  // 产品名称
        'official_fee',  // 官方费用
        'standard_price', // 标准价格
        'min_price',     // 最低价格
        'is_valid',      // 是否有效
        'update_user'    // 更新用户
    ];

    protected $casts = [
        'sort' => 'integer',
        'official_fee' => 'decimal:2',
        'standard_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'is_valid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 获取有效性状态文本
     * 将 is_valid 字段值转换为对应的中文状态文本
     * @return string 状态文本（有效或无效）
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '有效' : '无效';
    }

    /**
     * 作用域：有效状态
     * 查询 is_valid = true 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 先按 sort 字段排序，再按 id 字段排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 作用域：按项目类型
     * 根据项目类型筛选产品
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $projectType 项目类型
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeByProjectType($query, $projectType)
    {
        return $query->where('project_type', $projectType);
    }

    /**
     * 作用域：按申请类型
     * 根据申请类型模糊匹配产品
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $applyType 申请类型
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeByApplyType($query, $applyType)
    {
        return $query->where('apply_type', 'like', "%$applyType%");
    }

    /**
     * 作用域：按产品名称
     * 根据产品名称模糊匹配产品
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $productName 产品名称
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeByProductName($query, $productName)
    {
        return $query->where('product_name', 'like', "%$productName%");
    }
}
