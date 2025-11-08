<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 流程信息模型，用于管理业务流程中的各类处理事项信息
 */
class ProcessInformation extends Model
{

    protected $table = 'process_informations';

    protected $fillable = [
        'case_type',            // 案件类型
        'business_type',        // 业务类型（数组）
        'application_type',     // 申请类型（数组）
        'country',              // 国家（数组）
        'process_name',         // 处理事项名称
        'flow_completed',       // 流程是否完成
        'proposal_inquiry',     // 提案查询
        'data_updater_inquiry', // 数据更新查询
        'update_case_handler',  // 更新案件处理人
        'process_status',       // 处理状态（数组）
        'case_phase',           // 案件阶段
        'process_type',         // 处理类型
        'is_case_node',         // 是否为案件节点
        'is_commission',        // 是否有佣金
        'is_valid',             // 是否有效：1-有效，0-无效
        'sort_order',           // 排序序号
        'consultant_contract',  // 顾问合同
        'created_by',           // 创建人ID
        'updated_by',           // 更新人ID
    ];

    protected $casts = [
        'application_type' => 'array',
        'business_type' => 'array',
        'country' => 'array',
        'process_status' => 'array',
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 作用域：有效的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc');
    }

    /**
     * 获取状态文本
     * @return string 状态文本（是/否）
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 获取申请类型文本
     * @return string 申请类型文本（数组转字符串）
     */
    public function getApplicationTypeTextAttribute()
    {
        if (is_array($this->application_type)) {
            return implode(',', $this->application_type);
        }
        return $this->application_type;
    }

    /**
     * 获取处理状态文本
     * @return string 处理状态文本（数组转字符串）
     */
    public function getProcessStatusTextAttribute()
    {
        if (is_array($this->process_status)) {
            return implode(',', $this->process_status);
        }
        return $this->process_status;
    }

    /**
     * 获取业务类型文本
     * @return string 业务类型文本（数组转字符串）
     */
    public function getBusinessTypeTextAttribute()
    {
        if (is_array($this->business_type)) {
            return implode(',', $this->business_type);
        }
        return $this->business_type;
    }

    /**
     * 获取国家文本
     * @return string 国家文本（数组转字符串）
     */
    public function getCountryTextAttribute()
    {
        if (is_array($this->country)) {
            return implode(',', $this->country);
        }
        return $this->country;
    }



    /**
     * 作用域：按项目类型查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $caseType 项目类型
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCaseType($query, $caseType)
    {
        if (empty($caseType)) {
            return $query;
        }
        return $query->where('case_type', 'like', "%{$caseType}%");
    }

    /**
     * 作用域：按业务类型查询（支持多选）
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string|array $businessType 业务类型
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBusinessType($query, $businessType)
    {
        if (empty($businessType)) {
            return $query;
        }

        // 如果是数组，查找包含任一值的记录
        if (is_array($businessType)) {
            return $query->where(function ($q) use ($businessType) {
                foreach ($businessType as $type) {
                    $q->orWhereJsonContains('business_type', $type);
                }
            });
        }

        // 如果是字符串，查找包含该值的记录
        return $query->whereJsonContains('business_type', $businessType);
    }

    /**
     * 作用域：按国家查询（支持多选）
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string|array $country 国家
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCountry($query, $country)
    {
        if (empty($country)) {
            return $query;
        }

        // 如果是数组，查找包含任一值的记录
        if (is_array($country)) {
            return $query->where(function ($q) use ($country) {
                foreach ($country as $c) {
                    $q->orWhereJsonContains('country', $c);
                }
            });
        }

        // 如果是字符串，查找包含该值的记录
        return $query->whereJsonContains('country', $country);
    }

    /**
     * 作用域：按处理事项名称查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param string $processName 处理事项名称
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProcessName($query, $processName)
    {
        if (empty($processName)) {
            return $query;
        }
        return $query->where('process_name', 'like', "%{$processName}%");
    }

    /**
     * 关联关系：创建人（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * 关联关系：更新人（多对一）
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * 格式化更新时间
     * @param string|null $value 数据库中的更新时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化创建时间
     * @param string|null $value 数据库中的创建时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }
}
