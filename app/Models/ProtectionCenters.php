<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtectionCenters extends Model
{
    protected $table = 'protection_centers';

    protected $fillable = [
        'sort',
        'name',
        'code',
        'center_name',
        'description',
        'status',
        'updated_by',
        'created_by'
    ];

    protected $casts = [
        'status' => 'integer',
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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
        return $query->orderBy('sort')->orderBy('id');
    }
}
