<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerFollowupRecord extends Model
{
    use SoftDeletes;

    protected $table = 'customer_followup_records';

    protected $fillable = [
        'customer_id',
        'business_opportunity_id',
        'followup_type',
        'location',
        'contact_person',
        'contact_phone',
        'content',
        'followup_time',
        'next_followup_time',
        'result',
        'followup_person_id',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'business_opportunity_id' => 'integer',
        'followup_time' => 'datetime',
        'next_followup_time' => 'datetime',
        'followup_person_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 跟进类型常量
     */
    const TYPE_PHONE = '电话';
    const TYPE_VISIT = '上门拜访';
    const TYPE_MEETING = '商务洽谈';
    const TYPE_EMAIL = '邮件';
    const TYPE_WECHAT = '微信';
    const TYPE_OTHER = '其他';

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取商机
     */
    public function businessOpportunity()
    {
        return $this->belongsTo(BusinessOpportunity::class, 'business_opportunity_id');
    }

    /**
     * 获取跟进人员
     */
    public function followupPerson()
    {
        return $this->belongsTo(User::class, 'followup_person_id');
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
     * 获取跟进类型选项
     */
    public static function getFollowupTypes()
    {
        return [
            self::TYPE_PHONE => '电话',
            self::TYPE_VISIT => '上门拜访',
            self::TYPE_MEETING => '商务洽谈',
            self::TYPE_EMAIL => '邮件',
            self::TYPE_WECHAT => '微信',
            self::TYPE_OTHER => '其他',
        ];
    }
}
