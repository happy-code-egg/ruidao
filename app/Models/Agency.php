<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use SoftDeletes;

    protected $table = 'agencies';

    protected $fillable = [
        'sort',
        'agency_code',
        'agency_name_original',
        'agency_name_cn',
        'agency_name_en',
        'country',
        'social_credit_code',
        'create_time',
        'account',
        'password',
        'province',
        'city',
        'province_en',
        'city_en',
        'address_cn',
        'address_en',
        'postcode',
        'manager',
        'contact',
        'modifier',
        'agent_type',
        'is_valid',
        'is_supplier',
        'requirements',
        'remark',
        'creator',
        'creation_time',
        'update_time',
        'agency_type',
        'license_no',
        'legal_person',
        'contact_person',
        'contact_phone',
        'contact_email',
        'website',
        'business_scope',
        'cooperation_level',
        'status',
        'remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_valid' => 'boolean',
        'is_supplier' => 'boolean',
        'agency_type' => 'integer',
        'cooperation_level' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'date',
        'creation_time' => 'datetime',
        'update_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

        /**
     * 格式化 created_at 时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 deleted_at 时间
     */
    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 格式化 create_time 时间
     */
    public function getCreateTimeAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d') : null;
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
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
