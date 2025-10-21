<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedTypes extends Model
{
    protected $table = 'related_types';

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer'
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
}
