<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionTypes extends Model
{
    protected $table = 'commission_types';

    protected $fillable = [
        'name',
        'code',
        'rate',
        'description',
        'status',
        'sort_order',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'rate' => $this->rate,
            'description' => $this->description,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',
            'updated_by' => $this->updater->real_name ?? '',
        ];
    }
}
