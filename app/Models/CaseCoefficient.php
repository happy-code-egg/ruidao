<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseCoefficient extends Model
{

    protected $table = 'case_coefficients';

    protected $fillable = [
        'name',
        'sort',
        'is_valid',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_valid' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取创建者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新者
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 作用域：有效的记录
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc');
    }

    /**
     * 获取状态文本
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }
}
