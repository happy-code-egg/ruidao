<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户申请人模型
 * 用于管理客户相关的申请人信息
 */
class CustomerApplicant extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'customer_applicants';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'customer_id',                  // 客户ID
        'applicant_name_cn',            // 申请人中文名称
        'applicant_name_en',            // 申请人英文名称
        'applicant_code',               // 申请人编码
        'applicant_type',               // 申请人类型
        'id_type',                      // 证件类型
        'id_number',                    // 证件号码
        'country',                      // 国家
        'business_location',            // 营业地点
        'fee_reduction',                // 是否费减
        'fee_reduction_start_date',     // 费减开始日期
        'fee_reduction_end_date',       // 费减结束日期
        'province',                     // 省份
        'city',                         // 城市
        'district',                     // 区县
        'street',                       // 街道
        'postal_code',                  // 邮政编码
        'entity_type',                  // 实体类型
        'address_en',                   // 英文地址
        'total_condition_no',           // 总条件编号
        'sync_date',                    // 同步日期
        'email',                        // 邮箱
        'phone',                        // 电话
        'business_staff_id',            // 业务人员ID
        'inventor_note',                // 发明人备注
        'remark',                       // 备注
        'created_by',                   // 创建人ID
        'updated_by',                   // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',                     // 客户ID转换为整数
        'fee_reduction' => 'boolean',                   // 是否费减转换为布尔值
        'fee_reduction_start_date' => 'date',           // 费减开始日期转换为日期
        'fee_reduction_end_date' => 'date',             // 费减结束日期转换为日期
        'sync_date' => 'date',                          // 同步日期转换为日期
        'business_staff_id' => 'integer',               // 业务人员ID转换为整数
        'created_by' => 'integer',                      // 创建人ID转换为整数
        'updated_by' => 'integer',                      // 更新人ID转换为整数
        'created_at' => 'datetime',                     // 创建时间转换为日期时间
        'updated_at' => 'datetime',                     // 更新时间转换为日期时间
        'deleted_at' => 'datetime',                     // 删除时间转换为日期时间
    ];

    /**
     * 获取客户关联关系
     * 建立与 `Customer` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取创建人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取业务人员关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessStaff()
    {
        return $this->belongsTo(User::class, 'business_staff_id');
    }

    /**
     * 获取行政区域完整地址的访问器
     * 将省、市、区、街道信息拼接成完整地址
     * @return string 完整地址
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->province, $this->city, $this->district, $this->street]);
        return implode('', $parts);
    }

    /**
     * 获取费减备案状态文本的访问器
     * 将布尔值的费减状态转换为中文文本
     * @return string 费减状态文本('是'或'否')
     */
    public function getFeeReductionTextAttribute()
    {
        return $this->fee_reduction ? '是' : '否';
    }

    /**
     * 获取费减有效期文本的访问器
     * 格式化费减开始日期和结束日期为时间段文本
     * @return string 费减有效期文本
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
     * 格式为 APP+六位数字序号，如 APP000001
     * @return string 生成的申请人编号
     */
    public static function generateCode()
    {
        $prefix = 'APP';  // 编号前缀
        $lastApplicant = static::withTrashed()
            ->where('applicant_code', 'like', $prefix . '%')
            ->orderBy('applicant_code', 'desc')
            ->first();

        if ($lastApplicant) {
            $lastNumber = intval(substr($lastApplicant->applicant_code, strlen($prefix)));  // 获取最后编号的数字部分
            $newNumber = $lastNumber + 1;  // 新编号递增
        } else {
            $newNumber = 1;  // 如果没有记录则从1开始
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);  // 补齐6位数字并拼接前缀
    }
}
