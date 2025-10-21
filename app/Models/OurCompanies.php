<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurCompanies extends Model
{
    protected $table = 'our_companies';

    protected $fillable = [
        'name',
        'code',
        'short_name',
        'full_name',
        'credit_code',
        'address',
        'contact_person',
        'contact_phone',
        'tax_number',
        'bank',
        'account',
        'invoice_phone',
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
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
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
