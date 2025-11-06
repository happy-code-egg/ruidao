<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessOpportunity extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定对应的数据库表名
    protected $table = 'business_opportunities';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'customer_id',              // 客户ID
        'business_code',            // 商机编号
        'name',                     // 商机名称
        'contact_person',           // 联系人
        'contact_phone',            // 联系电话
        'business_person_id',       // 业务人员ID
        'next_time',                // 下次联系时间
        'second_time',              // 第二次联系时间
        'content',                  // 商机内容
        'case_type',                // 案件类型
        'business_type',            // 商机类型
        'estimated_amount',         // 预计金额
        'estimated_sign_time',      // 预计签约时间
        'status',                   // 状态
        'is_contract',              // 是否合同
        'background',               // 背景信息
        'remark',                   // 备注
        'created_by',               // 创建人ID
        'updated_by',               // 更新人ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'customer_id' => 'integer',         // 客户ID - 整数类型
        'business_person_id' => 'integer',  // 业务人员ID - 整数类型
        'next_time' => 'datetime',          // 下次联系时间 - 日期时间类型
        'second_time' => 'date',            // 第二次联系时间 - 日期类型
        'estimated_amount' => 'decimal:2',  // 预计金额 - 精确到小数点后2位的十进制数
        'estimated_sign_time' => 'date',    // 预计签约时间 - 日期类型
        'is_contract' => 'boolean',         // 是否合同 - 布尔类型
        'created_by' => 'integer',          // 创建人ID - 整数类型
        'updated_by' => 'integer',          // 更新人ID - 整数类型
        'created_at' => 'datetime',         // 创建时间 - 日期时间类型
        'updated_at' => 'datetime',         // 更新时间 - 日期时间类型
        'deleted_at' => 'datetime',         // 删除时间 - 日期时间类型
    ];

    /**
     * 商机状态常量
     */
    const STATUS_GENERAL = '一般';   // 一般状态
    const STATUS_IMPORTANT = '重要'; // 重要状态
    const STATUS_URGENT = '紧急';    // 紧急状态

    /**
     * 获取客户
     * 建立与 Customer 模型的一对多反向关联
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取业务人员
     * 建立与 User 模型的一对多反向关联，关联业务人员
     */
    public function businessPerson()
    {
        return $this->belongsTo(User::class, 'business_person_id');
    }

    /**
     * 获取创建人
     * 建立与 User 模型的一对多反向关联，关联记录创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     * 建立与 User 模型的一对多反向关联，关联记录更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取跟进记录
     * 建立与 CustomerFollowupRecord 模型的一对多关联
     */
    public function followupRecords()
    {
        return $this->hasMany(CustomerFollowupRecord::class, 'business_opportunity_id');
    }

    /**
     * 获取合同
     * 建立与 CustomerContract 模型的一对多关联
     */
    public function contracts()
    {
        return $this->hasMany(CustomerContract::class, 'business_opportunity_id');
    }

    /**
     * 获取状态标签类型
     * 根据商机状态返回对应的标签样式类型
     */
    public function getStatusTagTypeAttribute()
    {
        $statusMap = [
            self::STATUS_GENERAL => 'info',    // 一般 - 信息样式
            self::STATUS_IMPORTANT => 'warning', // 重要 - 警告样式
            self::STATUS_URGENT => 'danger'    // 紧急 - 危险样式
        ];
        return $statusMap[$this->status] ?? 'info'; // 默认返回信息样式
    }

    /**
     * 生成商机编号
     * 按照 BIZ+日期+序号 的格式生成唯一商机编号
     */
    public static function generateCode()
    {
        $prefix = 'BIZ' . date('Ymd'); // 前缀格式：BIZ+当前日期(YYYYMMDD)
        $lastOpportunity = static::withTrashed()
            ->where('business_code', 'like', $prefix . '%') // 查找相同日期前缀的商机
            ->orderBy('business_code', 'desc') // 按编号降序排列
            ->first();

        if ($lastOpportunity) {
            // 如果存在已有商机，获取最后一个编号并加1
            $lastNumber = intval(substr($lastOpportunity->business_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            // 如果不存在相同日期前缀的商机，从1开始编号
            $newNumber = 1;
        }

        // 返回格式化后的商机编号，序号补零到3位
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
