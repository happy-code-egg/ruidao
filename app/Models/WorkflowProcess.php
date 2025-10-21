<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowProcess extends Model
{

    protected $fillable = [
        'instance_id',
        'node_index',
        'node_name',
        'assignee_id',
        'processor_id',
        'action',
        'comment',
        'processed_at',
    ];

    protected $casts = [
        'node_index' => 'integer',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 动作常量
     */
    const ACTION_PENDING = 'pending';   // 待处理
    const ACTION_APPROVE = 'approve';   // 通过
    const ACTION_REJECT = 'reject';     // 驳回/退回
    const ACTION_AUTO = 'auto';         // 自动通过

    /**
     * 关联工作流实例
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    /**
     * 关联指定处理人
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * 关联实际处理人
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    /**
     * 关联指定处理人（别名）
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * 关联实际处理人（别名）
     */
    public function processedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    /**
     * 获取动作文本
     */
    public function getActionText()
    {
        switch ($this->action) {
            case self::ACTION_PENDING:
                return '待处理';
            case self::ACTION_APPROVE:
                return '通过';
            case self::ACTION_REJECT:
                return '驳回';
            case self::ACTION_AUTO:
                return '自动通过';
            default:
                return '未知动作';
        }
    }

    /**
     * 获取动作颜色
     */
    public function getActionColor()
    {
        switch ($this->action) {
            case self::ACTION_PENDING:
                return 'warning';
            case self::ACTION_APPROVE:
                return 'success';
            case self::ACTION_REJECT:
                return 'danger';
            case self::ACTION_AUTO:
                return 'info';
            default:
                return 'default';
        }
    }

    /**
     * 检查是否已处理
     */
    public function isProcessed()
    {
        return in_array($this->action, [self::ACTION_APPROVE, self::ACTION_REJECT, self::ACTION_AUTO]);
    }

    /**
     * 检查是否待处理
     */
    public function isPending()
    {
        return $this->action === self::ACTION_PENDING;
    }

    /**
     * 检查是否通过
     */
    public function isApproved()
    {
        return in_array($this->action, [self::ACTION_APPROVE, self::ACTION_AUTO]);
    }

    /**
     * 检查是否驳回
     */
    public function isRejected()
    {
        return $this->action === self::ACTION_REJECT;
    }
}
