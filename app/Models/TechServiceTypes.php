<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechServiceTypes extends Model
{
    protected $table = 'tech_service_types';

    protected $fillable = [
        'name',
        'code',
        'apply_type',
        'description',
        'status',
        'sort_order',
        'updater',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
    ];
    

    public function items()
    {
        return $this->hasMany(TechServiceItem::class, 'type_id', 'id');
    }

    /**
     * 格式化 created_at 时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function getItemsAttribute()
    {
        return $this->items()->get();
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
}
