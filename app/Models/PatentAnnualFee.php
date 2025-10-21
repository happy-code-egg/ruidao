<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatentAnnualFee extends Model
{

    protected $table = 'patent_annual_fees';

    protected $fillable = [
        'case_type',
        'apply_type',
        'country',
        'start_date',
        'currency',
        'has_fee_guide',
        'sort_order',
        'is_valid',
        'updated_by',
        'created_by',
    ];

    protected $casts = [
        'has_fee_guide' => 'integer',
        'sort_order' => 'integer',
        'is_valid' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_by' => 'string',
        'created_by' => 'string',
    ];

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 关联年费详情
     */
    public function details()
    {
        return $this->hasMany(PatentAnnualFeeDetail::class);
    }

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
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * 获取状态文本
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 获取缴费导览文本
     */
    public function getHasFeeGuideTextAttribute()
    {
        return $this->has_fee_guide ? '是' : '否';
    }

    /**
     * 作用域：按项目类型查询
     */
    public function scopeByCaseType($query, $caseType)
    {
        if (empty($caseType)) {
            return $query;
        }
        return $query->where('case_type', $caseType);
    }

    /**
     * 作用域：按申请类型查询
     */
    public function scopeByApplyType($query, $applyType)
    {
        if (empty($applyType)) {
            return $query;
        }
        return $query->where('apply_type', $applyType);
    }

    /**
     * 作用域：按国家查询
     */
    public function scopeByCountry($query, $country)
    {
        if (empty($country)) {
            return $query;
        }
        return $query->where('country', $country);
    }
}
