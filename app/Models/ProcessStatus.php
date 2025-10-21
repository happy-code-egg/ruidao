<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessStatus extends Model
{
    protected $table = 'process_statuses';

    protected $fillable = [
        'sort',
        'status_name',
        'status_code',
        'trigger_rule',
        'is_valid',
        'updater'
    ];

    protected $casts = [
        'sort' => 'integer',
        'trigger_rule' => 'boolean',
        'is_valid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
    }

    /**
     * 获取触发规则文本
     */
    public function getTriggerRuleTextAttribute()
    {
        return $this->trigger_rule === 1 ? '是' : '否';
    }

    /**
     * 作用域：有效状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 格式化更新时间显示
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }
}