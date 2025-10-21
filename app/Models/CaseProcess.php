<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseProcess extends Model
{
    protected $table = 'case_processes';

    protected $fillable = [
        'case_id',
        'process_code',
        'process_name',
        'process_type',
        'process_status',
        'priority_level',
        'assigned_to',
        'assignee',
        'is_assign',
        'due_date',
        'internal_deadline',
        'official_deadline',
        'customer_deadline',
        'expected_complete_date',
        'completion_date',
        'issue_date',
        'case_stage',
        'reviewer',
        'contract_code',
        'estimated_hours',
        'actual_hours',
        'process_coefficient',
        'process_description',
        'process_result',
        'process_remark',
        'attachments',
        'service_fees',
        'official_fees',
        'parent_process_id',
        'created_by',
        'updated_by',
        'completed_time',
    ];

    protected $casts = [
        'case_id' => 'integer',
        'process_status' => 'integer',
        'priority_level' => 'integer',
        'assigned_to' => 'integer',
        'assignee' => 'integer',
        'reviewer' => 'integer',
        'is_assign' => 'boolean',
        'due_date' => 'date',
        'internal_deadline' => 'date',
        'official_deadline' => 'date',
        'customer_deadline' => 'date',
        'expected_complete_date' => 'date',
        'completion_date' => 'date',
        'issue_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'attachments' => 'json',
        'service_fees' => 'json',
        'official_fees' => 'json',
        'parent_process_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'completed_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 处理状态常量
     */
    const STATUS_DRAFT = 0;        // 待提交（草稿）- 文件还在撰写中
    const STATUS_PENDING = 1;      // 待开始 - 已提交，等待开始核稿
    const STATUS_IN_PROGRESS = 2;  // 审核中 - 正在进行核稿审核  
    const STATUS_COMPLETED = 3;    // 审核完成 - 核稿审核已完成
    const STATUS_ASSIGNED = 4;     // 已分配 - 已分配处理人但未开始
    const STATUS_NOT_STARTED = 5;  // 未开始
    const STATUS_CANCELLED = 6;    // 已取消

    /**
     * 优先级常量
     */
    const PRIORITY_HIGH = 1;       // 高
    const PRIORITY_MEDIUM = 2;     // 中
    const PRIORITY_LOW = 3;        // 低

    /**
     * 获取项目
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 获取负责人
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * 获取配案人
     */
    public function assigneeUser()
    {
        return $this->belongsTo(User::class, 'assignee');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取核稿人
     */
    public function reviewerUser()
    {
        return $this->belongsTo(User::class, 'reviewer');
    }

    /**
     * 获取父处理事项
     */
    public function parent()
    {
        return $this->belongsTo(CaseProcess::class, 'parent_process_id');
    }

    /**
     * 获取子处理事项
     */
    public function children()
    {
        return $this->hasMany(CaseProcess::class, 'parent_process_id');
    }

    /**
     * 状态范围查询
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('process_status', $status);
    }

    /**
     * 项目范围查询
     */
    public function scopeByCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * 负责人范围查询
     */
    public function scopeByAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * 优先级范围查询
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    /**
     * 排序范围查询
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_level')->orderBy('due_date')->orderBy('id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
        ];

        return $statusMap[$this->process_status] ?? '未知';
    }

    /**
     * 获取优先级文本
     */
    public function getPriorityTextAttribute()
    {
        $priorityMap = [
            self::PRIORITY_HIGH => '高',
            self::PRIORITY_MEDIUM => '中',
            self::PRIORITY_LOW => '低',
        ];

        return $priorityMap[$this->priority_level] ?? '未知';
    }

    /**
     * 检查是否已完成
     */
    public function isCompleted()
    {
        return $this->process_status === self::STATUS_COMPLETED;
    }

    /**
     * 检查是否逾期
     */
    public function isOverdue()
    {
        if (!$this->due_date || $this->isCompleted()) {
            return false;
        }

        return $this->due_date < now()->toDateString();
    }
}
