<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 专利年费详情模型
 * 存储不同阶段、不同规模企业的专利年费具体费用信息
 */
class PatentAnnualFeeDetail extends Model
{
    protected $table = 'patent_annual_fee_details';

    protected $fillable = [
        'patent_annual_fee_id', // 专利年费主表ID
        'stage_code',          // 阶段编码
        'rank',                // 排序
        'official_year',       // 官方年
        'official_month',      // 官方月
        'official_day',        // 官方日
        'start_year',          // 开始年份
        'end_year',            // 结束年份
        'base_fee',            // 基础费用
        'small_fee',           // 小型企业费用
        'micro_fee',           // 微型企业费用
        'authorization_fee',   // 授权费用
    ];

    protected $casts = [
        'patent_annual_fee_id' => 'integer',
        'rank' => 'integer',
        'official_year' => 'integer',
        'official_month' => 'integer',
        'official_day' => 'integer',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'base_fee' => 'decimal:2',
        'small_fee' => 'decimal:2',
        'micro_fee' => 'decimal:2',
        'authorization_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联专利年费主表
     * 通过 patent_annual_fee_id 字段关联 PatentAnnualFee 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patentAnnualFee()
    {
        return $this->belongsTo(PatentAnnualFee::class);
    }

    /**
     * 作用域：按排序查询
     * 根据 rank 字段升序排列
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('rank', 'asc');
    }

    /**
     * 作用域：按年度范围查询
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param integer|null $startYear 起始年份
     * @param integer|null $endYear 结束年份
     * @return \Illuminate\Database\Eloquent\Builder 返回修改后的查询构建器
     */
    public function scopeByYearRange($query, $startYear, $endYear)
    {
        if (!empty($startYear)) {
            $query->where('start_year', '>=', $startYear);
        }
        if (!empty($endYear)) {
            $query->where('end_year', '<=', $endYear);
        }
        return $query;
    }
}
