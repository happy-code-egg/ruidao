<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 流程规则模型
 * 定义案例处理流程的规则配置，包括条件判断、动作执行、期限设置等
 */
class ProcessRule extends Model
{
    use SoftDeletes;

    protected $table = 'process_rules';

    protected $fillable = [
        'name',               // 规则名称
        'description',        // 规则描述
        'rule_type',          // 规则类型（1:自动分配规则, 2:提醒通知规则, 3:状态变更规则）
        'process_item_id',    // 处理事项ID
        'case_type',          // 案例类型
        'business_type',      // 业务类型
        'application_type',   // 申请类型
        'country',            // 国家
        'process_item_type',  // 处理事项类型
        'conditions',         // 条件配置（JSON格式）
        'actions',            // 动作配置（JSON格式）
        'generate_or_complete', // 生成或完成
        'processor',          // 处理者
        'fixed_personnel',    // 固定人员
        'is_assign_case',     // 是否分配案例
        'internal_deadline',  // 内部期限（JSON格式）
        'customer_deadline',  // 客户期限（JSON格式）
        'official_deadline',  // 官方期限（JSON格式）
        'complete_date',      // 完成日期配置（JSON格式）
        'status',             // 状态（1:启用, 0:禁用）
        'priority',           // 优先级
        'is_effective',       // 是否有效
        'sort_order',         // 排序顺序
        'updated_by',         // 更新者名称
        'created_by',         // 创建者ID
        'updated_by_id'       // 更新者ID
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
     * 获取创建者
     * 通过 created_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新者
     * 通过 updated_by_id 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * 获取处理事项
     * 通过 process_item_id 字段关联 ProcessInformation 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processItem()
    {
        return $this->belongsTo(ProcessInformation::class, 'process_item_id');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 获取规则类型文本
     * 将 rule_type 字段值转换为对应的中文规则类型名称
     * @return string 规则类型文本
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
