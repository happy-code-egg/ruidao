<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationRule extends Model
{
    use SoftDeletes;

    protected $table = 'notification_rules';

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'file_category_id',
        'conditions',
        'actions',
        'is_config',
        'process_item',
        'process_status',
        'is_upload',
        'transfer_target',
        'attachment_config',
        'generated_filename',
        'processor',
        'fixed_personnel',
        'internal_deadline',
        'customer_deadline',
        'official_deadline',
        'internal_priority_deadline',
        'customer_priority_deadline',
        'official_priority_deadline',
        'internal_precheck_deadline',
        'customer_precheck_deadline',
        'official_precheck_deadline',
        'complete_date',
        'is_effective',
        'status',
        'priority',
        'sort_order',
        'created_by',
        'updated_by',
        'updater'
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
     * 文件分类关联
     */
    public function fileCategory()
    {
        return $this->belongsTo(FileCategories::class, 'file_category_id');
    }

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
    public function updaterRelation()
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
     * 获取是否有效文本
     */
    public function getIsEffectiveTextAttribute()
    {
        return $this->is_effective == 1 ? '有效' : '无效';
    }

    /**
     * 获取规则类型文本
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
     */
    public function scopeEffective($query)
    {
        return $query->where('is_effective', 1);
    }

    /**
     * 作用域：只获取启用的
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按文件分类获取
     */
    public function scopeByFileCategory($query, $fileCategoryId)
    {
        return $query->where('file_category_id', $fileCategoryId);
    }
}
