<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpType extends Model
{
    protected $table = 'follow_up_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
    ];

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
