<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSettings extends Model
{
    protected $table = 'commission_settings';

    protected $fillable = [
        'name',
        'code',
        'handler_level',
        'case_type',
        'business_type',
        'application_type',
        'case_coefficient',
        'matter_coefficient',
        'processing_matter',
        'case_stage',
        'commission_type',
        'piece_ratio',
        'piece_points',
        'country',
        'rate',
        'status',
        'sort_order',
        'description',
        'updater'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'case_coefficient' => 'string',
        'matter_coefficient' => 'string'
    ];

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
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }
}
