<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerScale extends Model
{
    protected $table = 'customer_scales';

    protected $fillable = [
        'scale_name',
        'is_valid',
        'sort',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sort' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];


    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

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
        // 如果是select查询，保持原始字段
        if (isset($this->attributes['value']) || isset($this->attributes['label'])) {
            return parent::toArray();
        }

        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'scaleName' => $this->scale_name,
            'isValid' => $this->is_valid,
            'updatedAt' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',
            'updated_by' => $this->updater->real_name ?? ''
        ];
    }
}
