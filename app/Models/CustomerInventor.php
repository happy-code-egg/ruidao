<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户发明人模型
 * 用于管理客户相关的发明人信息
 */
class CustomerInventor extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定数据库表名
    protected $table = 'customer_inventors';

    // 定义可批量赋值的字段
    protected $fillable = [
        'customer_id',          // 客户ID
        'inventor_name_cn',     // 发明人中文名
        'inventor_name_en',     // 发明人英文名
        'inventor_code',        // 发明人编号
        'inventor_type',        // 发明人类型
        'gender',               // 性别
        'id_type',              // 证件类型
        'id_number',            // 证件号码
        'country',              // 国家
        'province',             // 省份
        'city',                 // 城市
        'district',             // 区县
        'street',               // 街道
        'postal_code',          // 邮政编码
        'address',              // 地址
        'address_en',           // 英文地址
        'phone',                // 手机号码
        'landline',             // 固定电话
        'wechat',               // 微信
        'email',                // 邮箱
        'work_unit',            // 工作单位
        'department',           // 部门
        'position',             // 职位
        'business_staff',       // 业务人员
        'remark',               // 备注
        'created_by',           // 创建人ID
        'updated_by',           // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'customer_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',    // 创建时间转为日期时间类型
        'updated_at' => 'datetime',    // 更新时间转为日期时间类型
        'deleted_at' => 'datetime',    // 删除时间转为日期时间类型
    ];

    /**
     * 关联客户信息
     * 通过 customer_id 字段关联 Customer 模型
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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
     * 获取完整地址
     * 将省、市、区、街道、地址等信息拼接成完整地址
     * @return string 完整地址字符串
     */
    public function getFullAddressAttribute()
    {
        // 过滤掉空值的地址部分
        $parts = array_filter([
            $this->province,
            $this->city,
            $this->district,
            $this->street,
            $this->address
        ]);
        // 将地址部分拼接成完整地址
        return implode('', $parts);
    }

    /**
     * 生成发明人编号
     * 按照 INV + 6位数字的格式生成唯一编号
     * @return string 生成的发明人编号
     */
    public static function generateCode()
    {
        $prefix = 'INV';  // 编号前缀
        // 查询已存在的最大编号（包括软删除的记录）
        $lastInventor = static::withTrashed()
            ->where('inventor_code', 'like', $prefix . '%')
            ->orderBy('inventor_code', 'desc')
            ->first();

        if ($lastInventor) {
            // 提取编号中的数字部分并加1
            $lastNumber = intval(substr($lastInventor->inventor_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            // 如果没有记录，则从1开始
            $newNumber = 1;
        }

        // 返回格式化后的编号（前缀+6位数字，不足的补0）
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
