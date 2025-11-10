<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户联系人模型
 * 用于管理客户相关的联系人信息
 */
class CustomerContact extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'customer_contacts';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'customer_id',          // 客户ID
        'contact_name',         // 联系人姓名
        'contact_type',         // 联系人类型
        'contact_type_text',    // 联系人类型文本
        'gender',               // 性别
        'position',             // 职位
        'phone',                // 手机号码
        'telephone',            // 固定电话
        'email',                // 个人邮箱
        'work_email',           // 工作邮箱
        'wechat',               // 微信
        'qq',                   // QQ
        'address',              // 个人地址
        'work_address',         // 工作地址
        'id_card',              // 身份证号
        'is_primary',           // 是否为主要联系人
        'is_on_job',            // 是否在职
        'department',           // 部门
        'title',                // 职称
        'business_staff_id',    // 业务人员ID
        'status',               // 状态
        'remarks',              // 备注
        'created_by',           // 创建人ID
        'updated_by',           // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',         // 客户ID转换为整数
        'contact_type' => 'integer',        // 联系人类型转换为整数
        'is_primary' => 'integer',          // 是否为主要联系人转换为整数
        'is_on_job' => 'boolean',           // 是否在职转换为布尔值
        'status' => 'integer',              // 状态转换为整数
        'created_by' => 'integer',          // 创建人ID转换为整数
        'updated_by' => 'integer',          // 更新人ID转换为整数
        'created_at' => 'datetime',         // 创建时间转换为日期时间
        'updated_at' => 'datetime',         // 更新时间转换为日期时间
        'deleted_at' => 'datetime',         // 删除时间转换为日期时间
    ];

    /**
     * 联系人类型常量
     */
    const TYPE_HANDLER = 1;         // 经办人
    const TYPE_TECHNICAL = 2;       // 技术人员
    const TYPE_FINANCIAL = 3;       // 财务人员
    const TYPE_IPR = 4;             // IPR
    const TYPE_INVENTOR = 5;        // 发明人

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
     * 获取联系人类型文本的访问器
     * 将数字类型的联系人类型转换为中文文本
     * @return string 联系人类型文本
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_HANDLER => '经办人',
            self::TYPE_TECHNICAL => '技术人员',
            self::TYPE_FINANCIAL => '财务人员',
            self::TYPE_IPR => 'IPR',
            self::TYPE_INVENTOR => '发明人',
        ];

        return $types[$this->contact_type] ?? '未知';
    }

    /**
     * 获取状态文本的访问器
     * 将数字状态转换为中文文本显示
     * @return string 状态文本('启用'或'禁用')
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 获取是否主要联系人文本的访问器
     * 将是否主要联系人转换为中文文本显示
     * @return string 是否主要联系人文本('是'或'否')
     */
    public function getPrimaryTextAttribute()
    {
        return $this->is_primary == 1 ? '是' : '否';
    }

    /**
     * 获取主要联系方式的访问器
     * 按优先级返回联系人的主要联系方式
     * @return string 主要联系方式或'无'
     */
    public function getPrimaryContactAttribute()
    {
        if ($this->phone) {
            return $this->phone;        // 优先返回手机号码
        }

        if ($this->telephone) {
            return $this->telephone;    // 其次返回固定电话
        }

        if ($this->email) {
            return $this->email;        // 最后返回邮箱
        }

        return '无';                    // 无联系方式时返回'无'
    }
}
