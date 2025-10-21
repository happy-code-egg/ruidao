<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityType extends Model
{
    protected $table = 'opportunity_types';

    protected $fillable = [
        'status_name',
        'is_valid',
        'sort',
        'updated_by'
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sort' => 'integer'
    ];

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：启用状态
     */
    public function scopeEnabled($query)
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
     * 处理API响应格式
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'statusName' => $this->status_name,
            'isValid' => $this->is_valid,
            'updatedBy' => $this->updated_by ?: '系统记录',
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '系统记录'
        ];
    }
}
