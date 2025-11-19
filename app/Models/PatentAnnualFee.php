<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 专利年费模型
 * 管理不同国家、不同类型专利的年费基础信息
 */
class PatentAnnualFee extends Model
{

    protected $table = 'patent_annual_fees';

    protected $fillable = [
        'case_type',       // 案例类型
        'apply_type',      // 申请类型
        'country',         // 国家
        'start_date',      // 开始日期
        'currency',        // 货币类型
        'has_fee_guide',   // 是否有缴费导览
        'sort_order',      // 排序顺序
        'is_valid',        // 是否有效
        'updated_by',      // 更新人ID
        'created_by',      // 创建人ID
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

    /**
     * 更新时间格式化
     * @param string $value 原始更新时间
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 创建时间格式化
     * @param string $value 原始创建时间
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联更新者
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 关联创建者
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 关联年费详情
     * 一对多关联 PatentAnnualFeeDetail 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(PatentAnnualFeeDetail::class);
    }

    /**
     * 作用域：有效的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为对应的中文状态描述
     * @return string 返回状态的中文描述（"是"或"否"）
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 获取缴费导览文本
     * 将 has_fee_guide 字段值转换为对应的中文状态描述
     * @return string 返回缴费导览状态的中文描述（"是"或"否"）
     */
    public function getHasFeeGuideTextAttribute()
    {
        return $this->has_fee_guide ? '是' : '否';
    }

    /**
     * 作用域：按项目类型查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param mixed $caseType 项目类型
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeByCaseType($query, $caseType)
    {
        if (empty($caseType)) {
            return $query;
        }
        return $query->where('case_type', 'like', '%' . $caseType . '%');
    }

    /**
     * 作用域：按申请类型查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param mixed $applyType 申请类型
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeByApplyType($query, $applyType)
    {
        if (empty($applyType)) {
            return $query;
        }
        return $query->where('apply_type', 'like', '%' . $applyType . '%');
    }

    /**
     * 作用域：按国家查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param mixed $country 国家
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeByCountry($query, $country)
    {
        if (empty($country)) {
            return $query;
        }
        return $query->where('country', 'like', '%' . $country . '%');
    }
}
