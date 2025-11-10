<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 申请类型模型
 * 定义系统中的申请类型，包含国家、案件类型、申请类型名称等信息
 */
class ApplyType extends Model
{
    // 指定对应的数据库表名
    protected $table = 'apply_types';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'country',          // 国家
        'case_type',        // 案件类型
        'apply_type_name',  // 申请类型名称
        'apply_type_code',  // 申请类型编码
        'description',      // 描述信息
        'status',           // 状态(1:启用, 0:禁用)
        'is_valid',         // 是否有效(1:有效, 0:无效)
        'sort_order',       // 排序顺序
        'sort',             // 添加前端使用的字段名(用于前端交互)
        'update_user'       // 更新用户
    ];

    // 字段类型转换定义
    protected $casts = [
        'status' => 'integer',      // 状态 - 整数类型
        'is_valid' => 'integer',    // 是否有效 - 整数类型
        'sort_order' => 'integer',  // 排序顺序 - 整数类型
        'created_at' => 'datetime', // 创建时间 - 日期时间类型
        'updated_at' => 'datetime'  // 更新时间 - 日期时间类型
    ];

    /**
     * 字段映射 - 前端使用sort，数据库使用sort_order
     * 获取排序字段值 - 将数据库字段sort_order映射为前端字段sort
     * @return integer 排序字段值
     */
    public function getSortAttribute()
    {
        return $this->sort_order;
    }

    /**
     * 设置排序字段值 - 将前端字段sort映射为数据库字段sort_order
     * @param integer $value 排序字段值
     * @return void
     */
    public function setSortAttribute($value)
    {
        $this->attributes['sort_order'] = $value;
    }

    /**
     * 获取状态文本
     * 根据status字段值返回对应的中文状态描述
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 查询作用域 - 只获取状态为启用(status=1)的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序
     * 查询作用域 - 按sort_order字段和id字段进行升序排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
