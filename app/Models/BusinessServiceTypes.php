<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessServiceTypes extends Model
{
    protected $table = 'business_service_types';

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
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
     * 处理API响应格式
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'category' => $this->category,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',
            'updated_by' => $this->updater->real_name ?? ''
        ];
    }
}
