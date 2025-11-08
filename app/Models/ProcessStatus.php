<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 流程状态模型，用于管理业务流程中的各类状态信息
 */
class ProcessStatus extends Model
{
    protected $table = 'process_statuses';

    protected $fillable = [
        'sort',          // 排序序号
        'status_name',   // 状态名称
        'status_code',   // 状态编码
        'trigger_rule',  // 是否触发规则：1-是，0-否
        'is_valid',      // 是否有效：1-有效，0-无效
        'updater'        // 更新人ID
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
     * @return string 状态文本（有效/无效）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid === 1 ? '有效' : '无效';
    }

    /**
     * 获取触发规则文本
     * @return string 触发规则文本（是/否）
     */
    public function getTriggerRuleTextAttribute()
    {
        return $this->trigger_rule === 1 ? '是' : '否';
    }

    /**
     * 作用域：有效状态
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 格式化更新时间显示
     * @return string 格式化后的更新时间字符串
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }
}