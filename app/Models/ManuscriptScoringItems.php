<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManuscriptScoringItems extends Model
{
    protected $table = 'manuscript_scoring_items';

    protected $fillable = [
        'sort',
        'name',
        'code',
        'major_category',
        'minor_category',
        'description',
        'score',
        'max_score',
        'weight',
        'status',
        'sort_order',
        'updated_by',
        'created_by'
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
            'sort' => $this->sort,
            'name' => $this->name,
            'code' => $this->code,
            'major_category' => $this->major_category,
            'minor_category' => $this->minor_category,
            'description' => $this->description,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'weight' => $this->weight,
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
