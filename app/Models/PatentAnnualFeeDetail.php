<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatentAnnualFeeDetail extends Model
{
    protected $table = 'patent_annual_fee_details';

    protected $fillable = [
        'patent_annual_fee_id',
        'stage_code',
        'rank',
        'official_year',
        'official_month',
        'official_day',
        'start_year',
        'end_year',
        'base_fee',
        'small_fee',
        'micro_fee',
        'authorization_fee',
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
     * 关联主表
     */
    public function patentAnnualFee()
    {
        return $this->belongsTo(PatentAnnualFee::class);
    }

    /**
     * 作用域：按排序查询
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('rank', 'asc');
    }

    /**
     * 作用域：按年度范围查询
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
