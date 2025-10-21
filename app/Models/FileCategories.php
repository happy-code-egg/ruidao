<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileCategories extends Model
{
    protected $table = 'file_categories';

    protected $fillable = [
        'main_category',
        'sub_category',
        'is_valid',
        'sort',
        'updated_by',
        'created_by'
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

    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

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
            'mainCategory' => $this->main_category,
            'subCategory' => $this->sub_category,
            'isValid' => $this->is_valid,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
            'createdBy' => $this->creator->real_name ?? '',
            'updatedBy' => $this->updater->real_name ?? ''
        ];
    }
}
