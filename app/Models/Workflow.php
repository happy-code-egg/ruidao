<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 工作流模型
 * 定义系统中的工作流配置，包括节点设置、流转规则等
 */
class Workflow extends Model
{
    use SoftDeletes;

    protected $table = 'workflows';

    protected $fillable = [
        'name',         // 工作流名称
        'code',         // 工作流编码
        'description',  // 工作流描述
        'case_type',    // 案例类型
        'status',       // 状态（1:启用, 0:禁用）
        'nodes',        // 节点配置（JSON格式）
        'created_by',   // 创建人ID
        'updated_by'    // 更新人ID
    ];

    protected $casts = [
        'nodes' => 'array',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * 创建者关联
     * 通过 created_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者关联
     * 通过 updated_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态描述
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 获取节点数量
     * 计算工作流中的节点总数
     * @return integer 节点数量
     */
    public function getNodeCountAttribute()
    {
        if (!$this->nodes) {
            return 0;
        }
        return count($this->nodes);
    }

    /**
     * 工作流节点关联（备用）
     * 一对多关联 WorkflowNode 模型，并按排序字段排序
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowNodes()
    {
        return $this->hasMany(WorkflowNode::class)->orderBy('sort_order');
    }
}
