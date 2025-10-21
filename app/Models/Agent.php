<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes;

    protected $table = 'agents';

    protected $fillable = [
        'sort',
        'name_cn',
        'name_en',
        'last_name_cn',
        'first_name_cn',
        'last_name_en',
        'first_name_en',
        'license_number',
        'qualification_number',
        'license_date',
        'agency',
        'phone',
        'email',
        'gender',
        'license_expiry',
        'specialty',
        'is_default_agent',
        'is_valid',
        'credit_rating',
        'status',
        'remarks',
        'creator',
        'creation_time',
        'modifier',
        'update_time',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'sort' => 'integer',
        'agency' => 'string',
        'is_default_agent' => 'boolean',
        'is_valid' => 'boolean',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'license_date' => 'date',
        'creation_time' => 'datetime',
        'update_time' => 'datetime'
    ];

    protected $dates = [
        'license_date',
        'license_expiry',
        'creation_time',
        'update_time'
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
     * 格式化 license_expiry 时间
     */
    public function getLicenseExpiryAttribute($value)
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

    /**
     * 获取代理机构名称属性
     */
    public function getAgencyNameAttribute()
    {
        return $this->agency ? $this->agency->agency_name_cn : '';
    }

    /**
     * 获取性别文本
     */
    public function getGenderTextAttribute()
    {
        return $this->gender;
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '有效' : '无效';
    }
}
