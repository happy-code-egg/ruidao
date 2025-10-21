<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentLevels extends Model
{
    protected $table = 'agent_levels';
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'sort_order',
        'level_name',
        'level_code',
        'commission_rate',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    // 关联创建人
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 关联更新人
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 状态范围查询
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // 排序范围查询
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}