<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerApplicant extends Model
{
    use SoftDeletes;

    protected $table = 'customer_applicants';

    protected $fillable = [
        'customer_id',
        'applicant_name_cn',
        'applicant_name_en',
        'applicant_code',
        'applicant_type',
        'id_type',
        'id_number',
        'country',
        'business_location',
        'fee_reduction',
        'fee_reduction_start_date',
        'fee_reduction_end_date',
        'province',
        'city',
        'district',
        'street',
        'postal_code',
        'entity_type',
        'address_en',
        'total_condition_no',
        'sync_date',
        'email',
        'phone',
        'business_staff_id',
        'inventor_note',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'fee_reduction' => 'boolean',
        'fee_reduction_start_date' => 'date',
        'fee_reduction_end_date' => 'date',
        'sync_date' => 'date',
        'business_staff_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取业务人员
     */
    public function businessStaff()
    {
        return $this->belongsTo(User::class, 'business_staff_id');
    }

    /**
     * 获取行政区域完整地址
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->province, $this->city, $this->district, $this->street]);
        return implode('', $parts);
    }

    /**
     * 获取费减备案状态文本
     */
    public function getFeeReductionTextAttribute()
    {
        return $this->fee_reduction ? '是' : '否';
    }

    /**
     * 获取费减有效期文本
     */
    public function getFeeReductionPeriodAttribute()
    {
        if ($this->fee_reduction_start_date && $this->fee_reduction_end_date) {
            return $this->fee_reduction_start_date . ' 至 ' . $this->fee_reduction_end_date;
        }
        return '';
    }

    /**
     * 生成申请人编号
     */
    public static function generateCode()
    {
        $prefix = 'APP';
        $lastApplicant = static::withTrashed()
            ->where('applicant_code', 'like', $prefix . '%')
            ->orderBy('applicant_code', 'desc')
            ->first();

        if ($lastApplicant) {
            $lastNumber = intval(substr($lastApplicant->applicant_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
