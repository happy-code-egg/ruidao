<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes;

    protected $table = 'workflows';

    protected $fillable = [
        'name',
        'code',
        'description',
        'case_type',
        'status',
        'nodes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'nodes' => 'array',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
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
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 获取节点数量
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
     */
    public function workflowNodes()
    {
        return $this->hasMany(WorkflowNode::class)->orderBy('sort_order');
    }
}
