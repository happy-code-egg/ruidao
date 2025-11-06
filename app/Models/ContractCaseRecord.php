<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合同案件记录模型
 * 用于管理合同中包含的案件记录信息
 */
class ContractCaseRecord extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'contract_case_records';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'case_code',            // 案件编码
        'case_name',            // 案件名称
        'customer_id',          // 客户ID
        'contract_id',          // 合同ID
        'case_id',              // 案件ID
        'case_type',            // 案件类型
        'case_subtype',         // 案件子类型
        'application_type',     // 申请类型
        'case_status',          // 案件状态
        'case_phase',           // 案件阶段
        'priority_level',       // 优先级
        'application_no',       // 申请号
        'application_date',     // 申请日期
        'registration_no',      // 注册号
        'registration_date',    // 注册日期
        'acceptance_no',        // 受理号
        'country_code',         // 国家代码
        'presale_support',      // 售前支持人员ID
        'tech_leader',          // 技术主导ID
        'tech_contact',         // 技术联系人ID
        'is_authorized',        // 是否授权
        'project_no',           // 项目编号
        'tech_service_name',    // 科服名称
        'trademark_category',   // 商标分类
        'product_id',           // 产品ID
        'entity_type',          // 实体类型
        'applicant_info',       // 申请人信息
        'inventor_info',        // 发明人信息
        'business_person_id',   // 业务人员ID
        'agent_id',             // 代理师ID
        'assistant_id',         // 助理ID
        'agency_id',            // 代理机构ID
        'deadline_date',        // 截止日期
        'annual_fee_due_date',  // 年费截止日期
        'estimated_cost',       // 预估费用
        'actual_cost',          // 实际费用
        'service_fee',          // 服务费
        'official_fee',         // 官方费用
        'is_priority',          // 是否优先权
        'priority_info',        // 优先权信息
        'classification_info',  // 分类信息
        'case_description',     // 案件描述
        'technical_field',      // 技术领域
        'innovation_points',    // 创新点
        'remarks',              // 备注
        'service_fees',         // 服务费用明细
        'official_fees',        // 官方费用明细
        'attachments',          // 附件信息
        'is_filed',             // 是否已立项
        'filed_at',             // 立项时间
        'filed_by',             // 立项人ID
        'created_by',           // 创建人ID
        'updated_by'            // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',         // 客户ID转换为整数
        'contract_id' => 'integer',         // 合同ID转换为整数
        'case_id' => 'integer',             // 案件ID转换为整数
        'case_type' => 'integer',           // 案件类型转换为整数
        'case_status' => 'integer',         // 案件状态转换为整数
        'priority_level' => 'integer',      // 优先级转换为整数
        'application_date' => 'date',       // 申请日期转换为日期
        'registration_date' => 'date',      // 注册日期转换为日期
        'entity_type' => 'integer',         // 实体类型转换为整数
        'presale_support' => 'integer',     // 售前支持人员ID转换为整数
        'tech_leader' => 'integer',         // 技术主导ID转换为整数
        'tech_contact' => 'integer',        // 技术联系人ID转换为整数
        'is_authorized' => 'integer',       // 是否授权转换为整数
        'product_id' => 'integer',          // 产品ID转换为整数
        'business_person_id' => 'integer',  // 业务人员ID转换为整数
        'agent_id' => 'integer',            // 代理师ID转换为整数
        'assistant_id' => 'integer',        // 助理ID转换为整数
        'agency_id' => 'integer',           // 代理机构ID转换为整数
        'deadline_date' => 'date',          // 截止日期转换为日期
        'annual_fee_due_date' => 'date',    // 年费截止日期转换为日期
        'estimated_cost' => 'decimal:2',    // 预估费用转换为保留2位小数的浮点数
        'actual_cost' => 'decimal:2',       // 实际费用转换为保留2位小数的浮点数
        'service_fee' => 'decimal:2',       // 服务费转换为保留2位小数的浮点数
        'official_fee' => 'decimal:2',      // 官方费用转换为保留2位小数的浮点数
        'is_priority' => 'integer',         // 是否优先权转换为整数
        'applicant_info' => 'json',         // 申请人信息转换为JSON
        'inventor_info' => 'json',          // 发明人信息转换为JSON
        'priority_info' => 'json',          // 优先权信息转换为JSON
        'classification_info' => 'json',    // 分类信息转换为JSON
        'service_fees' => 'json',           // 服务费用明细转换为JSON
        'official_fees' => 'json',          // 官方费用明细转换为JSON
        'attachments' => 'json',            // 附件信息转换为JSON
        'is_filed' => 'boolean',            // 是否已立项转换为布尔值
        'filed_at' => 'datetime',           // 立项时间转换为日期时间
        'filed_by' => 'integer',            // 立项人ID转换为整数
        'created_by' => 'integer',          // 创建人ID转换为整数
        'updated_by' => 'integer',          // 更新人ID转换为整数
        'created_at' => 'datetime',         // 创建时间转换为日期时间
        'updated_at' => 'datetime',         // 更新时间转换为日期时间
        'deleted_at' => 'datetime',         // 删除时间转换为日期时间
    ];

    /**
     * 案例类型常量
     */
    const TYPE_PATENT = 1;          // 专利
    const TYPE_TRADEMARK = 2;       // 商标
    const TYPE_COPYRIGHT = 3;       // 版权
    const TYPE_TECH_SERVICE = 4;    // 科服

    /**
     * 案例状态常量
     */
    const STATUS_DRAFT = 1;             // 草稿
    const STATUS_TO_BE_FILED = 2;       // 待立项
    const STATUS_SUBMITTED = 3;         // 已提交
    const STATUS_PROCESSING = 4;        // 处理中
    const STATUS_AUTHORIZED = 5;        // 已授权
    const STATUS_REJECTED = 6;          // 已驳回
    const STATUS_COMPLETED = 7;         // 已完成

    /**
     * 优先级常量
     */
    const PRIORITY_HIGH = 1;        // 高优先级
    const PRIORITY_MEDIUM = 2;      // 中优先级
    const PRIORITY_LOW = 3;         // 低优先级

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
     * 获取合同关联关系
     * 建立与 `Contract` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取关联的案件关联关系
     * 建立与 `Cases` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 获取产品信息关联关系
     * 建立与 `Product` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
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
     * 获取代理师关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * 获取助理关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
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
     * 获取立项人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function filer()
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    /**
     * 获取技术主导关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function techLeader()
    {
        return $this->belongsTo(User::class, 'tech_leader');
    }

    /**
     * 获取售前支持关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function presaleSupport()
    {
        return $this->belongsTo(User::class, 'presale_support');
    }

    /**
     * 获取案例类型文本的访问器
     * 将数字类型的案例类型转换为中文文本
     * @return string 案例类型文本
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_PATENT => '专利',
            self::TYPE_TRADEMARK => '商标',
            self::TYPE_COPYRIGHT => '版权',
            self::TYPE_TECH_SERVICE => '科服',
        ];

        return $types[$this->case_type] ?? '未知';
    }

    /**
     * 获取案例状态文本的访问器
     * 将数字类型的案例状态转换为中文文本
     * @return string 案例状态文本
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            self::STATUS_DRAFT => '草稿',
            self::STATUS_TO_BE_FILED => '待立项',
            self::STATUS_SUBMITTED => '已提交',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_AUTHORIZED => '已授权',
            self::STATUS_REJECTED => '已驳回',
            self::STATUS_COMPLETED => '已完成',
        ];

        return $statuses[$this->case_status] ?? '未知';
    }

    /**
     * 待立项记录的作用域查询
     * 查询状态为待立项且未正式立项的记录
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToBeField($query)
    {
        return $query->where('case_status', self::STATUS_TO_BE_FILED)
                    ->where('is_filed', false);
    }

    /**
     * 已立项记录的作用域查询
     * 查询已正式立项的记录
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFiled($query)
    {
        return $query->where('is_filed', true);
    }

    /**
     * 按项目类型筛选的作用域查询
     * 根据指定的案件类型进行筛选
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $type 案件类型
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * 按合同筛选的作用域查询
     * 根据指定的合同ID进行筛选
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $contractId 合同ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }
}
