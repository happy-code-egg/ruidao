<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文件分类模型
 * 用于管理系统中文件的分类信息，支持主分类和子分类两级结构
 */
class FileCategories extends Model
{
    protected $table = 'file_categories';

    protected $fillable = [
        'main_category',   // 主分类
        'sub_category',    // 子分类
        'is_valid',        // 是否有效
        'sort',            // 排序
        'updated_by',      // 更新人ID
        'created_by'       // 创建人ID
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sort' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

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
     * 将 is_valid 字段布尔值转换为中文文本
     * @return string 状态文本（"是"或"否"）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：有效状态
     * 用于查询 is_valid=true 的有效文件分类
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 按照 sort 和 id 字段进行排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 处理API响应格式
     * 自定义模型数据转换为数组的格式，包含驼峰命名的键和关联用户信息
     * @return array 格式化后的数组数据
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'mainCategory' => $this->main_category,
            'subCategory' => $this->sub_category,
            'isValid' => $this->is_valid,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
            'createdBy' => $this->creator->real_name ?? '',
            'updatedBy' => $this->updater->real_name ?? ''
        ];
    }
}
