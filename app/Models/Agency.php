<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 代理机构模型
 * 代表系统中的代理机构信息，包含机构基本信息、联系方式、资质信息等
 */
class Agency extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定对应的数据库表名
    protected $table = 'agencies';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'sort',                      // 排序字段
        'agency_code',               // 机构编码
        'agency_name_original',      // 原始机构名称
        'agency_name_cn',            // 中文机构名称
        'agency_name_en',            // 英文机构名称
        'country',                   // 国家
        'social_credit_code',        // 统一社会信用代码
        'create_time',               // 创建时间（日期）
        'account',                   // 账户
        'password',                  // 密码
        'province',                  // 省份
        'city',                      // 城市
        'province_en',               // 省份英文名
        'city_en',                   // 城市英文名
        'address_cn',                // 中文地址
        'address_en',                // 英文地址
        'postcode',                  // 邮政编码
        'manager',                   // 管理员
        'contact',                   // 联系人
        'modifier',                  // 修改人
        'agent_type',                // 代理类型
        'is_valid',                  // 是否有效
        'is_supplier',               // 是否供应商
        'requirements',              // 要求条件
        'remark',                    // 备注
        'creator',                   // 创建人
        'creation_time',             // 创建时间（详细时间）
        'update_time',               // 更新时间
        'agency_type',               // 机构类型
        'license_no',                // 营业执照号码
        'legal_person',              // 法人代表
        'contact_person',            // 联系人姓名
        'contact_phone',             // 联系电话
        'contact_email',             // 联系邮箱
        'website',                   // 网站
        'business_scope',            // 经营范围
        'cooperation_level',         // 合作等级
        'status',                    // 状态
        'remarks',                   // 备注信息
        'created_by',                // 创建者ID
        'updated_by'                 // 更新者ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'sort' => 'integer',           // 排序 - 整数类型
        'is_valid' => 'boolean',       // 是否有效 - 布尔类型
        'is_supplier' => 'boolean',    // 是否供应商 - 布尔类型
        'agency_type' => 'integer',    // 机构类型 - 整数类型
        'cooperation_level' => 'integer', // 合作等级 - 整数类型
        'status' => 'integer',         // 状态 - 整数类型
        'created_by' => 'integer',     // 创建者ID - 整数类型
        'updated_by' => 'integer',     // 更新者ID - 整数类型
        'create_time' => 'date',       // 创建时间 - 日期类型
        'creation_time' => 'datetime', // 创建详细时间 - 日期时间类型
        'update_time' => 'datetime',   // 更新时间 - 日期时间类型
        'created_at' => 'datetime',    // Laravel默认创建时间 - 日期时间类型
        'updated_at' => 'datetime',    // Laravel默认更新时间 - 日期时间类型
        'deleted_at' => 'datetime',    // 软删除时间 - 日期时间类型
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
     * @param string|null $value 数据库中的删除时间值
     * @return string|null 格式化后的时间字符串或null
     */
    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 格式化 create_time 时间
     * @param string|null $value 数据库中的创建日期值
     * @return string|null 格式化后的日期字符串或null
     */
    public function getCreateTimeAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d') : null;
    }

    /**
     * 创建者关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
