<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sort',
        'product_code',
        'project_type',
        'apply_type',
        'specification',
        'product_name',
        'official_fee',
        'standard_price',
        'min_price',
        'is_valid',
        'update_user'
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
     * 获取状态文本
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '有效' : '无效';
    }

    /**
     * 作用域：有效状态
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 作用域：按项目类型
     */
    public function scopeByProjectType($query, $projectType)
    {
        return $query->where('project_type', $projectType);
    }

    /**
     * 作用域：按申请类型
     */
    public function scopeByApplyType($query, $applyType)
    {
        return $query->where('apply_type', 'like', "%$applyType%");
    }

    /**
     * 作用域：按产品名称
     */
    public function scopeByProductName($query, $productName)
    {
        return $query->where('product_name', 'like', "%$productName%");
    }
}
