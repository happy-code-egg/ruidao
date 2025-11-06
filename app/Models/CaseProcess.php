<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseProcess extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_processes';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'case_id',                  // 案例ID
        'process_code',             // 处理事项编码
        'process_name',             // 处理事项名称
        'process_type',             // 处理事项类型
        'process_status',           // 处理状态
        'priority_level',           // 优先级
        'assigned_to',              // 负责人ID
        'assignee',                 // 配案人ID
        'is_assign',                // 是否已分配(布尔值)
        'due_date',                 // 截止日期
        'internal_deadline',        // 内部截止日期
        'official_deadline',        // 官方截止日期
        'customer_deadline',        // 客户截止日期
        'expected_complete_date',   // 预计完成日期
        'completion_date',          // 实际完成日期
        'issue_date',               // 发文日期
        'case_stage',               // 案件阶段
        'reviewer',                 // 核稿人ID
        'contract_code',            // 合同编码
        'estimated_hours',          // 预计工时
        'actual_hours',             // 实际工时
        'process_coefficient',      // 处理系数
        'process_description',      // 处理描述
        'process_result',           // 处理结果
        'process_remark',           // 处理备注
        'attachments',              // 附件信息(JSON格式)
        'service_fees',             // 服务费用(JSON格式)
        'official_fees',            // 官方费用(JSON格式)
        'parent_process_id',        // 父处理事项ID
        'created_by',               // 创建者ID
        'updated_by',               // 更新者ID
        'completed_time',           // 完成时间
    ];

    // 字段类型转换定义
    protected $casts = [
        'case_id' => 'integer',             // 案例ID - 整数类型
        'process_status' => 'integer',      // 处理状态 - 整数类型
        'priority_level' => 'integer',      // 优先级 - 整数类型
        'assigned_to' => 'integer',         // 负责人ID - 整数类型
        'assignee' => 'integer',            // 配案人ID - 整数类型
        'reviewer' => 'integer',            // 核稿人ID - 整数类型
        'is_assign' => 'boolean',           // 是否已分配 - 布尔类型
        'due_date' => 'date',               // 截止日期 - 日期类型
        'internal_deadline' => 'date',      // 内部截止日期 - 日期类型
        'official_deadline' => 'date',      // 官方截止日期 - 日期类型
        'customer_deadline' => 'date',      // 客户截止日期 - 日期类型
        'expected_complete_date' => 'date', // 预计完成日期 - 日期类型
        'completion_date' => 'date',        // 实际完成日期 - 日期类型
        'issue_date' => 'date',             // 发文日期 - 日期类型
        'estimated_hours' => 'decimal:2',   // 预计工时 - 精确到小数点后2位的十进制数
        'actual_hours' => 'decimal:2',      // 实际工时 - 精确到小数点后2位的十进制数
        'attachments' => 'json',            // 附件信息 - JSON格式
        'service_fees' => 'json',           // 服务费用 - JSON格式
        'official_fees' => 'json',          // 官方费用 - JSON格式
        'parent_process_id' => 'integer',   // 父处理事项ID - 整数类型
        'created_by' => 'integer',          // 创建者ID - 整数类型
        'updated_by' => 'integer',          // 更新者ID - 整数类型
        'completed_time' => 'datetime',     // 完成时间 - 日期时间类型
        'created_at' => 'datetime',         // 创建时间 - 日期时间类型
        'updated_at' => 'datetime',         // 更新时间 - 日期时间类型
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
    const PRIORITY_HIGH = 1;       // 高优先级
    const PRIORITY_MEDIUM = 2;     // 中优先级
    const PRIORITY_LOW = 3;        // 低优先级

    /**
     * 获取项目
     * 建立与 `Cases` 模型的一对多反向关联
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 获取负责人
     * 建立与 `User` 模型的一对多反向关联，关联处理事项负责人
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * 获取配案人
     * 建立与 `User` 模型的一对多反向关联，关联处理事项配案人
     */
    public function assigneeUser()
    {
        return $this->belongsTo(User::class, 'assignee');
    }

    /**
     * 获取创建人
     * 建立与 `User` 模型的一对多反向关联，关联记录创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     * 建立与 `User` 模型的一对多反向关联，关联记录更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取核稿人
     * 建立与 `User` 模型的一对多反向关联，关联处理事项核稿人
     */
    public function reviewerUser()
    {
        return $this->belongsTo(User::class, 'reviewer');
    }

    /**
     * 获取父处理事项
     * 建立与自身模型的一对多反向关联，关联父级处理事项
     */
    public function parent()
    {
        return $this->belongsTo(CaseProcess::class, 'parent_process_id');
    }

    /**
     * 获取子处理事项
     * 建立与自身模型的一对多关联，关联子级处理事项
     */
    public function children()
    {
        return $this->hasMany(CaseProcess::class, 'parent_process_id');
    }

    /**
     * 状态范围查询
     * 查询作用域 - 根据指定状态筛选处理事项
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('process_status', $status);
    }

    /**
     * 项目范围查询
     * 查询作用域 - 根据指定案例ID筛选处理事项
     */
    public function scopeByCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * 负责人范围查询
     * 查询作用域 - 根据指定负责人ID筛选处理事项
     */
    public function scopeByAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * 优先级范围查询
     * 查询作用域 - 根据指定优先级筛选处理事项
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    /**
     * 排序范围查询
     * 查询作用域 - 按优先级、截止日期、ID进行排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_level')->orderBy('due_date')->orderBy('id');
    }

    /**
     * 获取状态文本
     * 根据 `process_status` 字段值返回对应的中文状态描述
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
     * 根据 `priority_level` 字段值返回对应的中文优先级描述
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
     * 判断当前处理事项是否已完成状态
     */
    public function isCompleted()
    {
        return $this->process_status === self::STATUS_COMPLETED;
    }

    /**
     * 检查是否逾期
     * 判断当前处理事项是否已经超过截止日期且未完成
     */
    public function isOverdue()
    {
        // 如果没有设置截止日期或者已经完成，则不逾期
        if (!$this->due_date || $this->isCompleted()) {
            return false;
        }

        // 比较截止日期与当前日期，判断是否逾期
        return $this->due_date < now()->toDateString();
    }
}
