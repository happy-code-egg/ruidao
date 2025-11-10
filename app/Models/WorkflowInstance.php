<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工作流实例模型
 * 表示工作流的具体运行实例，包含当前状态、进度等信息
 */
class WorkflowInstance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workflow_id',          // 工作流ID
        'business_id',          // 业务ID
        'business_type',        // 业务类型(contract/case/payment)
        'business_title',       // 业务标题
        'current_node_index',   // 当前节点索引
        'status',               // 状态(pending/completed/rejected/cancelled)
        'created_by',           // 创建人ID
    ];

    protected $casts = [
        'current_node_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_PENDING = 'pending';      // 进行中
    const STATUS_COMPLETED = 'completed';  // 已完成
    const STATUS_REJECTED = 'rejected';    // 已驳回
    const STATUS_CANCELLED = 'cancelled';  // 已取消

    /**
     * 业务类型常量
     */
    const BUSINESS_TYPE_CONTRACT = 'contract';  // 合同
    const BUSINESS_TYPE_CASE = 'case';          // 案件
    const BUSINESS_TYPE_PAYMENT = 'payment';    // 请款

    /**
     * 关联工作流配置
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * 关联创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联处理记录
     */
    public function processes(): HasMany
    {
        return $this->hasMany(WorkflowProcess::class, 'instance_id')->orderBy('node_index');
    }

    /**
     * 关联案件（当business_type为case时）
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(Cases::class, 'business_id');
    }

    /**
     * 关联合同（当business_type为contract时）
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'business_id');
    }

    /**
     * 获取当前节点信息
     */
    public function getCurrentNode()
    {
        $nodes = json_decode($this->workflow->nodes, true);
        return $nodes[$this->current_node_index] ?? null;
    }

    /**
     * 获取下一个节点信息
     */
    public function getNextNode()
    {
        $nodes = json_decode($this->workflow->nodes, true);
        $nextIndex = $this->current_node_index + 1;
        return $nodes[$nextIndex] ?? null;
    }

    /**
     * 检查是否为最后一个节点
     */
    public function isLastNode()
    {
        $nodes = json_decode($this->workflow->nodes, true);
        return $this->current_node_index >= count($nodes) - 1;
    }

    /**
     * 获取状态文本
     */
    public function getStatusText()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '进行中';
            case self::STATUS_COMPLETED:
                return '已完成';
            case self::STATUS_REJECTED:
                return '已驳回';
            case self::STATUS_CANCELLED:
                return '已取消';
            default:
                return '未知状态';
        }
    }

    /**
     * 获取业务类型文本
     */
    public function getBusinessTypeText()
    {
        switch ($this->business_type) {
            case self::BUSINESS_TYPE_CONTRACT:
                return '合同';
            case self::BUSINESS_TYPE_CASE:
                return '案件';
            case self::BUSINESS_TYPE_PAYMENT:
                return '请款';
            default:
                return '未知类型';
        }
    }

    /**
     * 获取进度百分比
     */
    public function getProgressPercentage()
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 100;
        }
        
        $nodes = json_decode($this->workflow->nodes, true);
        $totalNodes = count($nodes);
        
        if ($totalNodes === 0) {
            return 0;
        }
        
        return intval(($this->current_node_index / $totalNodes) * 100);
    }

    /**
     * 获取当前节点名称
     */
    public function getCurrentNodeName(): string
    {
        $nodes = $this->workflow->nodes ?? [];
        $currentIndex = $this->current_node_index;

        if (isset($nodes[$currentIndex])) {
            return $nodes[$currentIndex]['name'] ?? "节点 " . ($currentIndex + 1);
        }

        return "未知节点";
    }
}
