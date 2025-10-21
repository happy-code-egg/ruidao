<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessRule extends Model
{
    use SoftDeletes;

    protected $table = 'process_rules';

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'process_item_id',
        'case_type',
        'business_type',
        'application_type',
        'country',
        'process_item_type',
        'conditions',
        'actions',
        'generate_or_complete',
        'processor',
        'fixed_personnel',
        'is_assign_case',
        'internal_deadline',
        'customer_deadline',
        'official_deadline',
        'complete_date',
        'status',
        'priority',
        'is_effective',
        'sort_order',
        'updated_by',
        'created_by',
        'updated_by_id'
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'internal_deadline' => 'array',
        'customer_deadline' => 'array',
        'official_deadline' => 'array',
        'complete_date' => 'array',
        'process_item_id' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'sort_order' => 'integer',
        'is_assign_case' => 'boolean',
        'is_effective' => 'boolean',
        'created_by' => 'integer',
        'updated_by_id' => 'integer'
    ];

    /**
     * 创建者关联
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者关联
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * 处理事项关联
     */
    public function processItem()
    {
        return $this->belongsTo(ProcessInformation::class, 'process_item_id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 获取规则类型文本
     */
    public function getRuleTypeTextAttribute()
    {
        $types = [
            1 => '自动分配规则',
            2 => '提醒通知规则',
            3 => '状态变更规则'
        ];
        return $types[$this->rule_type] ?? '未知类型';
    }
}
