<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户跟进记录模型
 * 用于管理客户跟进记录的数据操作
 */
class CustomerFollowupRecord extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定数据库表名
    protected $table = 'customer_followup_records';

    // 定义可批量赋值的字段
    protected $fillable = [
        'customer_id',                // 客户ID
        'business_opportunity_id',    // 商机ID
        'followup_type',              // 跟进类型
        'location',                   // 跟进地点
        'contact_person',             // 联系人
        'contact_phone',              // 联系电话
        'content',                    // 跟进内容
        'followup_time',              // 跟进时间
        'next_followup_time',         // 下次跟进时间
        'result',                     // 跟进结果
        'followup_person_id',         // 跟进人员ID
        'remark',                     // 备注
        'created_by',                 // 创建人ID
        'updated_by',                 // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'customer_id' => 'integer',
        'business_opportunity_id' => 'integer',
        'followup_time' => 'datetime',        // 跟进时间转为日期时间类型
        'next_followup_time' => 'datetime',   // 下次跟进时间转为日期时间类型
        'followup_person_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',           // 创建时间转为日期时间类型
        'updated_at' => 'datetime',           // 更新时间转为日期时间类型
        'deleted_at' => 'datetime',           // 删除时间转为日期时间类型
    ];

    /**
     * 跟进类型常量定义
     */
    const TYPE_PHONE = '电话';        // 电话跟进
    const TYPE_VISIT = '上门拜访';    // 上门拜访跟进
    const TYPE_MEETING = '商务洽谈';  // 商务洽谈跟进
    const TYPE_EMAIL = '邮件';        // 邮件跟进
    const TYPE_WECHAT = '微信';       // 微信跟进
    const TYPE_OTHER = '其他';        // 其他类型跟进

    /**
     * 关联客户信息
     * 通过 customer_id 字段关联 Customer 模型
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联商机信息
     * 通过 business_opportunity_id 字段关联 BusinessOpportunity 模型
     */
    public function businessOpportunity()
    {
        return $this->belongsTo(BusinessOpportunity::class, 'business_opportunity_id');
    }

    /**
     * 关联跟进人员信息
     * 通过 followup_person_id 字段关联 User 模型
     */
    public function followupPerson()
    {
        return $this->belongsTo(User::class, 'followup_person_id');
    }

    /**
     * 关联创建人信息
     * 通过 created_by 字段关联 User 模型
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 通过 updated_by 字段关联 User 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取所有可用的跟进类型选项
     * 返回一个关联数组，键为常量值，值为显示名称
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
