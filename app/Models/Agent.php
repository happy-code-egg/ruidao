<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 代理人模型
 * 代表系统中的代理人信息，包含代理人的基本信息、资格证书信息、联系方式等
 */
class Agent extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定对应的数据库表名
    protected $table = 'agents';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'sort',                     // 排序字段
        'name_cn',                  // 中文姓名
        'name_en',                  // 英文姓名
        'last_name_cn',             // 中文姓氏
        'first_name_cn',            // 中文中名
        'last_name_en',             // 英文姓氏
        'first_name_en',            // 英文中名
        'license_number',           // 执照编号
        'qualification_number',     // 资格证书编号
        'license_date',             // 执照日期
        'agency',                   // 所属代理机构
        'phone',                    // 电话
        'email',                    // 邮箱
        'gender',                   // 性别
        'license_expiry',           // 执照有效期
        'specialty',                // 专业特长
        'is_default_agent',         // 是否默认代理人
        'is_valid',                 // 是否有效
        'credit_rating',            // 信用评级
        'status',                   // 状态
        'remarks',                  // 备注
        'creator',                  // 创建人
        'creation_time',            // 创建时间
        'modifier',                 // 修改人
        'update_time',              // 更新时间
        'created_by',               // 创建者ID
        'updated_by'                // 更新者ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'sort' => 'integer',           // 排序 - 整数类型
        'agency' => 'string',          // 代理机构 - 字符串类型
        'is_default_agent' => 'boolean', // 是否默认代理人 - 布尔类型
        'is_valid' => 'boolean',       // 是否有效 - 布尔类型
        'status' => 'integer',         // 状态 - 整数类型
        'created_by' => 'integer',     // 创建者ID - 整数类型
        'updated_by' => 'integer',     // 更新者ID - 整数类型
        'license_date' => 'date',      // 执照日期 - 日期类型
        'creation_time' => 'datetime', // 创建时间 - 日期时间类型
        'update_time' => 'datetime'    // 更新时间 - 日期时间类型
    ];

    // 需要被Carbon\Carbon实例化的日期属性
    protected $dates = [
        'license_date',     // 执照日期
        'license_expiry',   // 执照有效期
        'creation_time',    // 创建时间
        'update_time'       // 更新时间
    ];

    /**
     * 格式化 created_at 时间
     * @param string $value 数据库中的创建时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     * @param string $value 数据库中的更新时间值
     * @return string 格式化后的时间字符串
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
     * 格式化 license_expiry 时间
     * @param string|null $value 数据库中的执照有效期
     * @return string|null 格式化后的日期字符串或null
     */
    public function getLicenseExpiryAttribute($value)
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

    /**
     * 获取代理机构名称属性
     * @return string 代理机构中文名称
     */
    public function getAgencyNameAttribute()
    {
        return $this->agency ? $this->agency->agency_name_cn : '';
    }

    /**
     * 获取性别文本
     * @return mixed 性别值
     */
    public function getGenderTextAttribute()
    {
        return $this->gender;
    }

    /**
     * 获取状态文本
     * @return string 状态描述文本（有效/无效）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '有效' : '无效';
    }
}
