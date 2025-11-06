<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cases extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定对应的数据库表名
    protected $table = 'cases';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'case_code',                // 案例编码
        'case_name',                // 案例名称
        'customer_id',              // 客户ID
        'contract_id',              // 合同ID
        'case_type',                // 案例类型
        'case_subtype',             // 案例子类型
        'application_type',         // 申请类型
        'application_method',       // 申请方式
        'case_direction',           // 案例方向
        'proposal_no',              // 提案号
        'company',                  // 公司
        'contract_number',          // 合同编号
        'initial_stage',            // 初始阶段
        'annual_fee_stage',         // 年费阶段
        'prosecution_review',       // 实审请求
        'preliminary_case',         // 初步案例
        'early_publication',        // 提前公开
        'confidential_application', // 保密申请
        'substantive_examination',  // 实质审查
        'fast_track_case',          // 快速通道案例
        'priority_examination',     // 优先审查
        'acceptance_number',        // 受理号
        'has_materials',            // 是否有材料
        'acceptance_date',          // 受理日期
        'location',                 // 地点
        'software_abbr',            // 软件简称
        'version_number',           // 版本号
        'development_complete_date',// 开发完成日期
        'publish_status',           // 发布状态
        'source_code_amount',       // 源代码量
        'hardware_env',             // 硬件环境
        'software_env',             // 软件环境
        'programming_language',     // 编程语言
        'software_description',     // 软件描述
        'main_features',            // 主要特性
        'author',                   // 作者
        'project_year',             // 项目年份
        'apply_batch',              // 申请批次
        'tech_service_level',       // 技术服务级别
        'supervisory_location',     // 监管地点
        'supervisory_department',   // 监管部门
        'government_reward',        // 政府奖励
        'cost_ratio',               // 成本比例
        'technical_contact',        // 技术联系人
        'is_urgent',                // 是否紧急
        'case_handler',             // 案例处理人
        'estimated_start_date',     // 预计开始日期
        'internal_deadline',        // 内部截止日期
        'receipt_date',             // 收文日期
        'estimated_completion_date',// 预计完成日期
        'payment_method',           // 付款方式
        'estimated_final_payment',  // 预计最终付款
        'contract_requirements',    // 合同要求
        'project_requirements',     // 项目要求
        'special_progress',         // 特殊进度
        'case_status',              // 案例状态
        'case_phase',               // 案例阶段
        'priority_level',           // 优先级
        'application_no',           // 申请号
        'application_date',         // 申请日期
        'registration_no',          // 注册号
        'registration_date',        // 注册日期
        'acceptance_no',            // 受理号
        'country_code',             // 国家代码
        'presale_support',          // 售前支持
        'tech_leader',              // 技术主导
        'tech_contact',             // 技术联系人
        'is_authorized',            // 是否授权
        'project_no',               // 项目编号
        'tech_service_name',        // 技术服务名称
        'trademark_category',       // 商标类别
        'trademark_image',          // 商标图像
        'sound_trademark',          // 声音商标
        'specified_color',          // 指定颜色
        'is_3d_mark',               // 是否三维标志
        'color_format',             // 颜色格式
        'preliminary_announcement_date', // 初步公告日期
        'preliminary_announcement_period', // 初步公告期
        'renewal_date',             // 续展日期
        'end_date',                 // 结束日期
        'customer_file_number',     // 客户档案号
        'trademark_description',    // 商标描述
        'registration_no',          // 注册号
        'publication_date',         // 公告日期
        'product_id',               // 产品ID
        'entity_type',              // 实体类型
        'applicant_info',           // 申请人信息(JSON)
        'inventor_info',            // 发明人信息(JSON)
        'business_person_id',       // 业务人员ID
        'agent_id',                 // 代理师ID
        'assistant_id',             // 助理ID
        'agency_id',                // 代理机构ID
        'deadline_date',            // 截止日期
        'annual_fee_due_date',      // 年费到期日
        'estimated_cost',           // 预估成本
        'actual_cost',              // 实际成本
        'service_fee',              // 服务费
        'official_fee',             // 官方费
        'is_priority',              // 是否优先权
        'priority_info',            // 优先权信息(JSON)
        'classification_info',      // 分类信息(JSON)
        'case_description',         // 案例描述
        'technical_field',          // 技术领域
        'innovation_points',        // 创新点
        'remarks',                  // 备注
        'cooperative_personnel',    // 合作人员(JSON)
        'has_risk_clause',          // 是否有风险条款
        'created_by',               // 创建人ID
        'updated_by',               // 更新人ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'customer_id' => 'integer',         // 客户ID - 整数类型
        'contract_id' => 'integer',         // 合同ID - 整数类型
        'case_type' => 'integer',           // 案例类型 - 整数类型
        'case_status' => 'integer',         // 案例状态 - 整数类型
        'priority_level' => 'integer',      // 优先级 - 整数类型
        'application_date' => 'date',       // 申请日期 - 日期类型
        'registration_date' => 'date',      // 注册日期 - 日期类型
        'entity_type' => 'integer',         // 实体类型 - 整数类型
        'presale_support' => 'integer',     // 售前支持 - 整数类型
        'tech_leader' => 'integer',         // 技术主导 - 整数类型
        'tech_contact' => 'integer',        // 技术联系人 - 整数类型
        'is_authorized' => 'integer',       // 是否授权 - 整数类型
        'product_id' => 'integer',          // 产品ID - 整数类型
        'business_person_id' => 'integer',  // 业务人员ID - 整数类型
        'agent_id' => 'integer',            // 代理师ID - 整数类型
        'assistant_id' => 'integer',        // 助理ID - 整数类型
        'agency_id' => 'integer',           // 代理机构ID - 整数类型
        'deadline_date' => 'date',          // 截止日期 - 日期类型
        'annual_fee_due_date' => 'date',    // 年费到期日 - 日期类型
        'estimated_cost' => 'decimal:2',    // 预估成本 - 精确到小数点后2位的十进制数
        'actual_cost' => 'decimal:2',       // 实际成本 - 精确到小数点后2位的十进制数
        'service_fee' => 'decimal:2',       // 服务费 - 精确到小数点后2位的十进制数
        'official_fee' => 'decimal:2',      // 官方费 - 精确到小数点后2位的十进制数
        'is_priority' => 'integer',         // 是否优先权 - 整数类型
        'applicant_info' => 'json',         // 申请人信息 - JSON格式
        'inventor_info' => 'json',          // 发明人信息 - JSON格式
        'priority_info' => 'json',          // 优先权信息 - JSON格式
        'classification_info' => 'json',    // 分类信息 - JSON格式
        'cooperative_personnel' => 'json',  // 合作人员 - JSON格式
        'has_risk_clause' => 'integer',     // 是否有风险条款 - 整数类型
        'created_by' => 'integer',          // 创建人ID - 整数类型
        'updated_by' => 'integer',          // 更新人ID - 整数类型
        'created_at' => 'datetime',         // 创建时间 - 日期时间类型
        'updated_at' => 'datetime',         // 更新时间 - 日期时间类型
        'deleted_at' => 'datetime',         // 删除时间 - 日期时间类型
    ];

    /**
     * 案例类型常量
     */
    const TYPE_PATENT = 1;        // 专利
    const TYPE_TRADEMARK = 2;     // 商标
    const TYPE_COPYRIGHT = 3;     // 版权
    const TYPE_TECH_SERVICE = 4;  // 科技服务

    /**
     * 案例状态常量
     */
    const STATUS_DRAFT = 1;           // 草稿
    const STATUS_TO_BE_FILED = 2;     // 待立项
    const STATUS_SUBMITTED = 3;       // 已提交
    const STATUS_PROCESSING = 4;      // 处理中
    const STATUS_AUTHORIZED = 5;      // 已授权
    const STATUS_REJECTED = 6;        // 已驳回
    const STATUS_COMPLETED = 7;       // 已完成
    const STATUS_ARCHIVED = 8;        // 已归档

    /**
     * 优先级常量
     */
    const PRIORITY_HIGH = 1;    // 高优先级
    const PRIORITY_MEDIUM = 2;  // 中优先级
    const PRIORITY_LOW = 3;     // 低优先级

    /**
     * 获取客户
     * 建立与 Customer 模型的一对多反向关联
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取合同
     * 建立与 CustomerContract 模型的一对多反向关联
     */
    public function contract()
    {
        return $this->belongsTo(CustomerContract::class, 'contract_id');
    }

    /**
     * 获取产品信息
     * 建立与 Product 模型的一对多反向关联
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
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
     * 获取技术主导
     * 建立与 User 模型的一对多反向关联，关联技术主导人员
     */
    public function techLeader()
    {
        return $this->belongsTo(User::class, 'tech_leader');
    }

    /**
     * 获取代理师
     * 建立与 User 模型的一对多反向关联，关联代理师
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * 获取助理
     * 建立与 User 模型的一对多反向关联，关联助理
     */
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
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
     * 获取指派用户（案例处理人）
     * case_handler是varchar类型，存储用户名或ID，暂时不用作关系查询
     */
    public function getAssignedUserAttribute()
    {
        if (empty($this->case_handler)) {
            return null;
        }

        // 如果是数字，按ID查找
        if (is_numeric($this->case_handler)) {
            return User::find($this->case_handler);
        } else {
            // 如果是字符串，按用户名查找
            return User::where('name', $this->case_handler)->first();
        }
    }

    /**
     * 获取案例费用
     * 建立与 CaseFee 模型的一对多关联
     */
    public function fees()
    {
        return $this->hasMany(CaseFee::class, 'case_id');
    }

    /**
     * 获取服务费
     * 建立与 CaseFee 模型的一对多关联，只获取服务费类型
     */
    public function serviceFees()
    {
        return $this->hasMany(CaseFee::class, 'case_id')->where('fee_type', 'service');
    }

    /**
     * 获取官费
     * 建立与 CaseFee 模型的一对多关联，只获取官方费类型
     */
    public function officialFees()
    {
        return $this->hasMany(CaseFee::class, 'case_id')->where('fee_type', 'official');
    }

    /**
     * 获取案例附件
     * 建立与 CaseAttachment 模型的一对多关联
     */
    public function attachments()
    {
        return $this->hasMany(CaseAttachment::class, 'case_id');
    }

    /**
     * 获取处理事项
     * 建立与 CaseProcess 模型的一对多关联
     */
    public function processes()
    {
        return $this->hasMany(CaseProcess::class, 'case_id');
    }

    /**
     * 案例类型文本
     * 根据 case_type 字段值返回对应的中文案例类型描述
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
     * 案例状态文本
     * 根据 case_status 字段值返回对应的中文案例状态描述
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
     * 优先级文本
     * 根据 priority_level 字段值返回对应的中文优先级描述
     */
    public function getPriorityTextAttribute()
    {
        $priorities = [
            self::PRIORITY_HIGH => '高',
            self::PRIORITY_MEDIUM => '中',
            self::PRIORITY_LOW => '低',
        ];

        return $priorities[$this->priority_level] ?? '未知';
    }

    /**
     * 获取立项工作流实例（最新的）
     * 建立与 WorkflowInstance 模型的一对一关联，获取最新创建的工作流实例
     */
    public function workflowInstance()
    {
        return $this->hasOne(WorkflowInstance::class, 'business_id')
                    ->where('business_type', 'case')
                    ->latest('created_at');
    }

    /**
     * 获取所有工作流实例
     * 建立与 WorkflowInstance 模型的一对多关联，获取所有工作流实例并按创建时间倒序排列
     */
    public function workflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'business_id')
                    ->where('business_type', 'case')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * 获取当前用户在此案例中的待办任务
     * 根据用户ID获取其在当前案例工作流中的待处理任务
     */
    public function getPendingTasksForUser($userId)
    {
        return $this->workflowInstance()
                    ->with(['processes' => function($query) use ($userId) {
                        $query->where('assignee_id', $userId)
                              ->where('action', 'pending');
                    }])
                    ->first();
    }
}
