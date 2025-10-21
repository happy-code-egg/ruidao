<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessInformation extends Model
{

    protected $table = 'process_informations';

    protected $fillable = [
        'case_type',
        'business_type',
        'application_type',
        'country',
        'process_name',
        'flow_completed',
        'proposal_inquiry',
        'data_updater_inquiry',
        'update_case_handler',
        'process_status',
        'case_phase',
        'process_type',
        'is_case_node',
        'is_commission',
        'is_valid',
        'sort_order',
        'consultant_contract',
        'created_by',
        'updated_by',
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
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc');
    }

    /**
     * 获取状态文本
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 获取申请类型文本
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
     */
    public function scopeByProcessName($query, $processName)
    {
        if (empty($processName)) {
            return $query;
        }
        return $query->where('process_name', 'like', "%{$processName}%");
    }

    /**
     * 关联关系：创建人
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * 关联关系：更新人
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }
}
