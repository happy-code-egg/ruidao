<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 通知规则模型
 * 用于管理系统中的各种通知规则配置，包括条件触发和执行动作
 */
class NotificationRule extends Model
{
    use SoftDeletes;

    protected $table = 'notification_rules';

    protected $fillable = [
        'name',                     // 规则名称
        'description',              // 规则描述
        'rule_type',                // 规则类型
        'file_category_id',         // 文件分类ID
        'conditions',               // 触发条件（数组）
        'actions',                  // 执行动作（数组）
        'is_config',                // 是否配置
        'process_item',             // 处理事项
        'process_status',           // 处理状态
        'is_upload',                // 是否上传
        'transfer_target',          // 转交目标
        'attachment_config',        // 附件配置（数组）
        'generated_filename',       // 生成的文件名
        'processor',                // 处理人
        'fixed_personnel',          // 固定人员
        'internal_deadline',        // 内部截止时间（数组）
        'customer_deadline',        // 客户截止时间（数组）
        'official_deadline',        // 官方截止时间（数组）
        'internal_priority_deadline', // 内部优先截止时间（数组）
        'customer_priority_deadline', // 客户优先截止时间（数组）
        'official_priority_deadline', // 官方优先截止时间（数组）
        'internal_precheck_deadline', // 内部预检截止时间（数组）
        'customer_precheck_deadline', // 客户预检截止时间（数组）
        'official_precheck_deadline', // 官方预检截止时间（数组）
        'complete_date',            // 完成日期（数组）
        'is_effective',             // 是否有效（0:无效, 1:有效）
        'status',                   // 状态（0:禁用, 1:启用）
        'priority',                 // 优先级
        'sort_order',               // 排序顺序
        'created_by',               // 创建人ID
        'updated_by',               // 更新人ID
        'updater'                   // 更新人
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'attachment_config' => 'array',
        'internal_deadline' => 'array',
        'customer_deadline' => 'array',
        'official_deadline' => 'array',
        'internal_priority_deadline' => 'array',
        'customer_priority_deadline' => 'array',
        'official_priority_deadline' => 'array',
        'internal_precheck_deadline' => 'array',
        'customer_precheck_deadline' => 'array',
        'official_precheck_deadline' => 'array',
        'complete_date' => 'array',
        'file_category_id' => 'integer',
        'is_effective' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * 关联文件分类
     * 通过 `file_category_id` 字段关联 `FileCategories` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fileCategory()
    {
        return $this->belongsTo(FileCategories::class, 'file_category_id');
    }

    /**
     * 关联创建者
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新者
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updaterRelation()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
     * 获取是否有效文本
     * 将 is_effective 字段值转换为对应的中文文本
     * @return string 文本（有效或无效）
     */
    public function getIsEffectiveTextAttribute()
    {
        return $this->is_effective == 1 ? '有效' : '无效';
    }

    /**
     * 获取规则类型文本
     * 将 rule_type 字段值转换为对应的中文规则类型文本
     * @return string 规则类型文本（新增处理事项、更新处理事项等）
     */
    public function getRuleTypeTextAttribute()
    {
        $types = [
            'add_process' => '新增处理事项',
            'update_process' => '更新处理事项',
            'update_status' => '更新项目状态',
            'update_info' => '更新项目信息'
        ];
        return $types[$this->rule_type] ?? $this->rule_type;
    }

    /**
     * 作用域：只获取有效的
     * 查询 is_effective = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEffective($query)
    {
        return $query->where('is_effective', 1);
    }

    /**
     * 作用域：只获取启用的
     * 查询 status = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按文件分类获取
     * 根据指定的文件分类ID查询记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param int $fileCategoryId 文件分类ID
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeByFileCategory($query, $fileCategoryId)
    {
        return $query->where('file_category_id', $fileCategoryId);
    }
}
