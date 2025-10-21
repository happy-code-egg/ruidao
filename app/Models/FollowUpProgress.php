<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpProgress extends Model
{
    protected $table = 'follow_up_progresses';

    protected $fillable = [
        'name',
        'code',
        'percentage',
        'description',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'percentage' => 'integer',
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
     * 获取格式化进度
     */
    public function getFormattedPercentageAttribute()
    {
        return $this->percentage . '%';
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
