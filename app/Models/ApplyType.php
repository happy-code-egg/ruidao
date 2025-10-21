<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplyType extends Model
{

    protected $table = 'apply_types';

    protected $fillable = [
        'country',
        'case_type',
        'apply_type_name',
        'apply_type_code',
        'description',
        'status',
        'is_valid',
        'sort_order',
        'sort', // 添加前端使用的字段名
        'update_user'
    ];

    protected $casts = [
        'status' => 'integer',
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 字段映射 - 前端使用sort，数据库使用sort_order
     */
    public function getSortAttribute()
    {
        return $this->sort_order;
    }

    public function setSortAttribute($value)
    {
        $this->attributes['sort_order'] = $value;
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
