<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户关联人员模型
 * 用于管理客户相关的联系人员信息
 */
class CustomerRelatedPerson extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定数据库表名
    protected $table = 'customer_related_persons';

    // 定义可批量赋值的字段
    protected $fillable = [
        'customer_id',                  // 客户ID
        'related_business_person_id',   // 关联的业务人员ID
        'person_name',                  // 人员姓名
        'person_type',                  // 人员类型
        'phone',                        // 电话
        'email',                        // 邮箱
        'position',                     // 职位
        'department',                   // 部门
        'relationship',                 // 关系
        'responsibility',               // 职责
        'is_active',                    // 是否在职
        'remark',                       // 备注
        'created_by',                   // 创建人ID
        'updated_by',                   // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'customer_id' => 'integer',
        'related_business_person_id' => 'integer',
        'is_active' => 'boolean',       // 在职状态转为布尔类型
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',     // 创建时间转为日期时间类型
        'updated_at' => 'datetime',     // 更新时间转为日期时间类型
        'deleted_at' => 'datetime',     // 删除时间转为日期时间类型
    ];

    /**
     * 人员类型常量定义
     */
    const TYPE_TECH_LEADER = '技术负责人';        // 技术负责人
    const TYPE_BUSINESS_LEADER = '商务负责人';    // 商务负责人
    const TYPE_FINANCE_LEADER = '财务负责人';     // 财务负责人
    const TYPE_PROJECT_MANAGER = '项目负责人';   // 项目负责人
    const TYPE_BUSINESS_ASSISTANT = '业务助理';   // 业务助理
    const TYPE_BUSINESS_COLLABORATOR = '业务协作人'; // 业务协作人
    const TYPE_OTHER = '其他';                    // 其他类型

    /**
     * 关联客户信息
     * 通过 `customer_id` 字段关联 `Customer` 模型
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 关联业务人员信息
     * 通过 `related_business_person_id` 字段关联 `User` 模型
     */
    public function relatedBusinessPerson()
    {
        return $this->belongsTo(User::class, 'related_business_person_id');
    }

    /**
     * 获取在职状态文本
     * 根据 `is_active` 字段值返回对应的在职状态文本
     * @return string 在职状态文本（'在职' 或 '离职'）
     */
    public function getActiveStatusTextAttribute()
    {
        return $this->is_active ? '在职' : '离职';
    }

    /**
     * 获取人员类型选项
     * 返回一个关联数组，包含所有可用的人员类型选项
     * @return array 人员类型选项数组
     */
    public static function getPersonTypes()
    {
        return [
            self::TYPE_TECH_LEADER => '技术负责人',
            self::TYPE_BUSINESS_LEADER => '商务负责人',
            self::TYPE_FINANCE_LEADER => '财务负责人',
            self::TYPE_PROJECT_MANAGER => '项目负责人',
            self::TYPE_BUSINESS_ASSISTANT => '业务助理',
            self::TYPE_BUSINESS_COLLABORATOR => '业务协作人',
            self::TYPE_OTHER => '其他',
        ];
    }
}
