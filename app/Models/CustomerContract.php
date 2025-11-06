<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户合同模型
 * 用于管理客户相关的合同信息
 */
class CustomerContract extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'customer_contracts';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'customer_id',              // 客户ID
        'business_opportunity_id',   // 商机ID
        'contract_no',              // 合同编号
        'contract_name',            // 合同名称
        'contract_amount',          // 合同金额
        'sign_date',                // 签约日期
        'start_date',               // 开始日期
        'end_date',                 // 结束日期
        'contract_type',            // 合同类型
        'status',                   // 合同状态
        'business_person_id',       // 业务人员ID
        'contract_content',         // 合同内容
        'payment_method',           // 付款方式
        'paid_amount',              // 已付金额
        'unpaid_amount',            // 未付金额
        'remark',                   // 备注
        'created_by',               // 创建人ID
        'updated_by',               // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',                 // 客户ID转换为整数
        'business_opportunity_id' => 'integer',     // 商机ID转换为整数
        'contract_amount' => 'decimal:2',           // 合同金额转换为保留2位小数的浮点数
        'sign_date' => 'date',                      // 签约日期转换为日期
        'start_date' => 'date',                     // 开始日期转换为日期
        'end_date' => 'date',                       // 结束日期转换为日期
        'business_person_id' => 'integer',          // 业务人员ID转换为整数
        'paid_amount' => 'decimal:2',               // 已付金额转换为保留2位小数的浮点数
        'unpaid_amount' => 'decimal:2',             // 未付金额转换为保留2位小数的浮点数
        'created_by' => 'integer',                  // 创建人ID转换为整数
        'updated_by' => 'integer',                  // 更新人ID转换为整数
        'created_at' => 'datetime',                 // 创建时间转换为日期时间
        'updated_at' => 'datetime',                 // 更新时间转换为日期时间
        'deleted_at' => 'datetime',                 // 删除时间转换为日期时间
    ];

    /**
     * 合同状态常量
     */
    const STATUS_EXECUTING = '执行中';      // 执行中状态
    const STATUS_COMPLETED = '已完成';      // 已完成状态
    const STATUS_TERMINATED = '已终止';     // 已终止状态

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
     * 获取商机关联关系
     * 建立与 `BusinessOpportunity` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessOpportunity()
    {
        return $this->belongsTo(BusinessOpportunity::class, 'business_opportunity_id');
    }

    /**
     * 获取业务人员关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessPerson()
    {
        return $this->belongsTo(User::class, 'business_person_id');
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
     * 获取合同状态标签类型的访问器
     * 根据合同状态返回对应的标签类型
     * @return string 标签类型
     */
    public function getStatusTagTypeAttribute()
    {
        $statusMap = [
            self::STATUS_EXECUTING => 'primary',    // 执行中状态对应primary标签
            self::STATUS_COMPLETED => 'success',    // 已完成状态对应success标签
            self::STATUS_TERMINATED => 'danger'     // 已终止状态对应danger标签
        ];
        return $statusMap[$this->status] ?? 'info'; // 默认返回info标签
    }

    /**
     * 获取付款进度百分比的访问器
     * 计算已付金额占合同总金额的百分比
     * @return float 付款进度百分比
     */
    public function getPaymentProgressAttribute()
    {
        if ($this->contract_amount == 0) {
            return 0;  // 合同金额为0时返回0%
        }
        return round(($this->paid_amount / $this->contract_amount) * 100, 2);  // 计算并四舍五入到2位小数
    }

    /**
     * 生成合同编号
     * 格式为 HT+年月日+三位数字序号，如 HT20231201001
     * @return string 生成的合同编号
     */
    public static function generateContractNo()
    {
        $prefix = 'HT' . date('Ymd');  // 前缀为 HT+当前日期
        $lastContract = static::withTrashed()
            ->where('contract_no', 'like', $prefix . '%')
            ->orderBy('contract_no', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = intval(substr($lastContract->contract_no, strlen($prefix)));  // 获取最后编号的数字部分
            $newNumber = $lastNumber + 1;  // 新编号递增
        } else {
            $newNumber = 1;  // 如果没有记录则从1开始
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);  // 补齐3位数字并拼接前缀
    }
}
