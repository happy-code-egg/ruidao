<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 客户模型
 * 用于管理客户基本信息及相关业务数据
 */
class Customer extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'customers';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'customer_code',                // 客户编码
        'customer_name',                // 客户名称
        'credit_code',                  // 统一社会信用代码
        'customer_type',                // 客户类型
        'customer_level',               // 客户等级
        'customer_scale',               // 客户规模
        'industry',                     // 所属行业
        'registered_address',           // 注册地址
        'office_address',               // 办公地址
        'legal_person',                 // 法人代表
        'contact_phone',                // 联系电话
        'contact_email',                // 联系邮箱
        'website',                      // 公司网站
        'business_scope',               // 经营范围
        'company_type',                 // 公司类型
        'business_person_id',           // 业务人员ID
        'business_assistant_id',        // 业务助理ID
        'business_partner_id',          // 业务协作人ID
        'company_manager_id',           // 公司负责人ID
        'source_channel',               // 来源渠道
        'park_id',                      // 园区ID
        'customer_status',              // 客户状态
        'latest_contract_date',         // 最新合同日期
        'contract_count',               // 合同数量
        'total_amount',                 // 合同总金额
        'remarks',                      // 备注
        'created_by',                   // 创建人ID
        'updated_by',                   // 更新人ID
        // 第一批新增字段
        'province',                     // 省份
        'city',                         // 城市
        'district',                     // 区县
        'case_count',                   // 案件数量
        'customer_no',                  // 客户编号
        'process_staff_id',             // 流程人员ID
        'economic_category',            // 经济类别
        'economic_door',                // 国民经济门类
        'economic_big_class',           // 经济大类
        'economic_mid_class',           // 经济中类
        'economic_small_class',         // 经济小类
        'sales_2021',                   // 2021年销售额
        'research_fee_2021',            // 2021年研发费用
        'loan_2021',                    // 2021年贷款额
        'high_tech_enterprise',         // 高新技术企业认证
        'province_enterprise',          // 省级企业认证
        'city_enterprise',              // 市级企业认证
        'province_tech_center',         // 省级技术中心认证
        'ip_standard',                  // 知识产权管理规范认证
        'it_standard',                  // 信息化标准认证
        'innovation_index',             // 创新指数
        'price_index',                  // 价格指数
        // 资质认证字段
        'is_jinxin_verified',           // 是否金信认证
        'jinxin_verify_date',           // 金信认证日期
        'is_science_verified',          // 是否科技认证
        'science_verify_date',          // 科技认证日期
        'high_tech_date',               // 高新技术企业认证日期
        'province_enterprise_date',     // 省级企业认证日期
        'city_enterprise_date',         // 市级企业认证日期
        'province_tech_center_date',    // 省级技术中心认证日期
        'ip_standard_date',             // 知识产权管理规范认证日期
        'info_standard_date',           // 信息化标准认证日期
        // 工商信息字段
        'industry_classification',      // 行业分类
        'paid_capital',                 // 实缴资本
        'business_term',                // 营业期限
        'registration_authority',       // 登记机关
        'approval_date',                // 核准日期
        'registration_status',          // 登记状态
        // 第二批新增字段（前端匹配）
        'name',                         // 名称
        'name_en',                      // 英文名称
        'customer_code_alias',          // 客户编码别名
        'legal_representative',         // 法定代表人
        'company_manager',              // 公司经理
        'level',                        // 等级
        'employee_count',               // 员工数量
        'business_person',              // 业务人员
        'business_assistant',           // 业务助理
        'business_partner',             // 业务伙伴
        'price_index_str',              // 价格指数字符串
        'innovation_index_str',         // 创新指数字符串
        'contract_count_str',           // 合同数量字符串
        'latest_contract_date_str',     // 最新合同日期字符串
        'created_by',                   // 创建人
        'create_date',                  // 创建日期
        'create_time',                  // 创建时间
        'updated_by',                   // 更新人
        'update_time',                  // 更新时间
        'remark',                       // 备注
        'contact_name',                 // 联系人姓名
        'email',                        // 邮箱
        'qq',                           // QQ
        'wechat',                       // 微信
        'country',                      // 国家
        'address',                      // 地址
        'address_en',                   // 英文地址
        'other_address',                // 其他地址
        'industrial_park',              // 工业园区
        'zip_code',                     // 邮政编码
        'account_name',                 // 账户名称
        'bank_name',                    // 银行名称
        'bank_account',                 // 银行账户
        'invoice_address',              // 发票地址
        'invoice_phone',                // 发票电话
        'is_general_taxpayer',          // 是否一般纳税人
        'billing_address',              // 开票地址
        'invoice_credit_code',          // 发票信用代码
        'founding_date',                // 成立日期
        'main_products',                // 主要产品
        'company_staff_count',          // 公司员工数
        'registered_capital',           // 注册资本
        'research_staff_count',         // 研发人员数
        'doctor_count',                 // 博士人数
        'master_count',                 // 硕士人数
        'bachelor_count',               // 本科人数
        'overseas_returnee_count',      // 海归人数
        'middle_engineer_count',        // 中级工程师人数
        'senior_engineer_count',        // 高级工程师人数
        'trademark_count',              // 商标数量
        'patent_count',                 // 专利数量
        'invention_patent_count',       // 发明专利数量
        'copyright_count',              // 著作权数量
        'has_additional_deduction',     // 是否有加计扣除
        'has_school_cooperation',       // 是否有校企合作
        'cooperation_school',           // 合作院校
        'is_jinxin_verified',           // 是否金信认证
        'jinxin_verify_date',           // 金信认证日期
        'is_science_verified',          // 是否科技认证
        'science_verify_date',          // 科技认证日期
        'high_tech_enterprise_str',     // 高新技术企业认证字符串
        'high_tech_date',               // 高新技术企业认证日期
        'province_enterprise_str',      // 省级企业认证字符串
        'province_enterprise_date',     // 省级企业认证日期
        'city_enterprise_str',          // 市级企业认证字符串
        'city_enterprise_date',         // 市级企业认证日期
        'province_tech_center_str',     // 省级技术中心认证字符串
        'province_tech_center_date',    // 省级技术中心认证日期
        'ip_standard_str',              // 知识产权管理规范认证字符串
        'ip_standard_date',             // 知识产权管理规范认证日期
        'it_standard_str',              // 信息化标准认证字符串
        'info_standard_date',           // 信息化标准认证日期
        'spare1',                       // 备用字段1
        'spare2',                       // 备用字段2
        'spare3',                       // 备用字段3
        'spare4',                       // 备用字段4
        'spare5',                       // 备用字段5
        'original_salesperson',         // 原销售人员
        'public_sea_name',              // 公海名称
        'sales_data',                   // 销售数据
        'rd_cost_data',                 // 研发成本数据
        'loan_data',                    // 贷款数据
        'research_project_data',        // 研究项目数据
        'project_amount_data',          // 项目金额数据
        'rd_equipment_original_value_data', // 研发设备原值数据
        'has_audit_report_data',        // 是否有审计报告数据
        'asset_liability_ratio_data',   // 资产负债率数据
        'fixed_asset_investment_data',  // 固定资产投资数据
        'equipment_investment_data',    // 设备投资数据
        'smart_equipment_investment_data', // 智能设备投资数据
        'rd_equipment_investment_data', // 研发设备投资数据
        'it_investment_data',           // IT投资数据
        'has_imported_equipment_data',  // 是否有进口设备数据
        'has_investment_record_data',   // 是否有投资记录数据
        'record_amount_data',           // 记录金额数据
        'record_period_data',           // 记录期间数据
        'rating',                       // 评级
        'avatar',                       // 头像
        'tags',                         // 标签
        'important_events',             // 重要事件
        'created_at',                   // 创建时间
        'updated_at',                   // 更新时间
        'deleted_at',                   // 删除时间
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_type' => 'integer',                   // 客户类型转换为整数
        'customer_level' => 'integer',                  // 客户等级转换为整数
        'customer_scale' => 'integer',                  // 客户规模转换为整数
        'business_person_id' => 'integer',              // 业务人员ID转换为整数
        'business_assistant_id' => 'integer',           // 业务助理ID转换为整数
        'business_partner_id' => 'integer',             // 业务协作人ID转换为整数
        'company_manager_id' => 'integer',              // 公司负责人ID转换为整数
        'park_id' => 'integer',                         // 园区ID转换为整数
        'customer_status' => 'integer',                 // 客户状态转换为整数
        'latest_contract_date' => 'date',               // 最新合同日期转换为日期
        'contract_count' => 'integer',                  // 合同数量转换为整数
        'total_amount' => 'decimal:2',                  // 合同总金额转换为保留2位小数的浮点数
        'created_by' => 'integer',                      // 创建人ID转换为整数
        'updated_by' => 'integer',                      // 更新人ID转换为整数
        'created_at' => 'datetime',                     // 创建时间转换为日期时间
        'updated_at' => 'datetime',                     // 更新时间转换为日期时间
        'deleted_at' => 'datetime',                     // 删除时间转换为日期时间
        // 第一批新增字段的类型转换
        'case_count' => 'integer',                      // 案件数量转换为整数
        'process_staff_id' => 'integer',                // 流程人员ID转换为整数
        'sales_2021' => 'decimal:2',                    // 2021年销售额转换为保留2位小数的浮点数
        'research_fee_2021' => 'decimal:2',             // 2021年研发费用转换为保留2位小数的浮点数
        'loan_2021' => 'decimal:2',                     // 2021年贷款额转换为保留2位小数的浮点数
        'high_tech_enterprise' => 'string',             // 高新技术企业认证转换为字符串
        'province_enterprise' => 'string',              // 省级企业认证转换为字符串
        'city_enterprise' => 'string',                  // 市级企业认证转换为字符串
        'province_tech_center' => 'string',             // 省级技术中心认证转换为字符串
        'ip_standard' => 'string',                      // 知识产权管理规范认证转换为字符串
        'it_standard' => 'string',                      // 信息化标准认证转换为字符串
        'is_jinxin_verified' => 'string',               // 是否金信认证转换为字符串
        'is_science_verified' => 'string',              // 是否科技认证转换为字符串
        'innovation_index' => 'integer',                // 创新指数转换为整数
        'price_index' => 'integer',                     // 价格指数转换为整数
        'level' => 'integer',                           // 等级转换为整数
        'business_person' => 'integer',                 // 业务人员转换为整数
        // 日期字段
        'jinxin_verify_date' => 'date',                 // 金信认证日期转换为日期
        'science_verify_date' => 'date',                // 科技认证日期转换为日期
        'high_tech_date' => 'date',                     // 高新技术企业认证日期转换为日期
        'province_enterprise_date' => 'date',           // 省级企业认证日期转换为日期
        'city_enterprise_date' => 'date',               // 市级企业认证日期转换为日期
        'province_tech_center_date' => 'date',          // 省级技术中心认证日期转换为日期
        'ip_standard_date' => 'date',                   // 知识产权管理规范认证日期转换为日期
        'info_standard_date' => 'date',                 // 信息化标准认证日期转换为日期
        'approval_date' => 'date',                      // 核准日期转换为日期
        // 数值字段
        'paid_capital' => 'decimal:2',                  // 实缴资本转换为保留2位小数的浮点数
        // 第二批新增字段的类型转换
        'founding_date' => 'date',                      // 成立日期转换为日期
        'is_general_taxpayer' => 'boolean',             // 是否一般纳税人转换为布尔值
        'trademark_count' => 'integer',                 // 商标数量转换为整数
        'patent_count' => 'integer',                    // 专利数量转换为整数
        'invention_patent_count' => 'integer',          // 发明专利数量转换为整数
        'copyright_count' => 'integer',                 // 著作权数量转换为整数
        'has_additional_deduction' => 'boolean',        // 是否有加计扣除转换为布尔值
        'has_school_cooperation' => 'boolean',          // 是否有校企合作转换为布尔值
        'rating' => 'decimal:1',                        // 评级转换为保留1位小数的浮点数
        // JSON字段的类型转换
        'sales_data' => 'json',                         // 销售数据转换为JSON
        'rd_cost_data' => 'json',                       // 研发成本数据转换为JSON
        'loan_data' => 'json',                          // 贷款数据转换为JSON
        'research_project_data' => 'json',              // 研究项目数据转换为JSON
        'project_amount_data' => 'json',                // 项目金额数据转换为JSON
        'rd_equipment_original_value_data' => 'json',   // 研发设备原值数据转换为JSON
        'has_audit_report_data' => 'json',              // 是否有审计报告数据转换为JSON
        'asset_liability_ratio_data' => 'json',         // 资产负债率数据转换为JSON
        'fixed_asset_investment_data' => 'json',        // 固定资产投资数据转换为JSON
        'equipment_investment_data' => 'json',          // 设备投资数据转换为JSON
        'smart_equipment_investment_data' => 'json',    // 智能设备投资数据转换为JSON
        'rd_equipment_investment_data' => 'json',       // 研发设备投资数据转换为JSON
        'it_investment_data' => 'json',                 // IT投资数据转换为JSON
        'has_imported_equipment_data' => 'json',        // 是否有进口设备数据转换为JSON
        'has_investment_record_data' => 'json',         // 是否有投资记录数据转换为JSON
        'record_amount_data' => 'json',                 // 记录金额数据转换为JSON
        'record_period_data' => 'json',                 // 记录期间数据转换为JSON
        'tags' => 'json',                               // 标签转换为JSON
        'important_events' => 'json',                   // 重要事件转换为JSON
    ];

    /**
     * 客户类型常量
     */
    const TYPE_ENTERPRISE = 1;      // 企业客户
    const TYPE_INDIVIDUAL = 2;      // 个人客户
    const TYPE_INSTITUTION = 3;     // 机构客户

    /**
     * 客户等级常量
     */
    const LEVEL_IMPORTANT = 1;      // 重要客户
    const LEVEL_GENERAL = 2;        // 一般客户
    const LEVEL_POTENTIAL = 3;      // 潜在客户

    /**
     * 客户状态常量
     */
    const STATUS_NORMAL = 1;        // 正常
    const STATUS_SUSPENDED = 2;     // 暂停
    const STATUS_TERMINATED = 3;    // 终止

    /**
     * 获取创建时间的访问器
     * 格式化创建时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始创建时间值
     * @return string 格式化后的创建时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
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
     * 获取更新时间的访问器
     * 格式化更新时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始更新时间值
     * @return string 格式化后的更新时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
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
     * 获取最新合同日期的访问器
     * 格式化最新合同日期为 'Y-m-d' 格式
     * @param string $value 原始最新合同日期值
     * @return string 格式化后的最新合同日期
     */
    public function getLatestContractDateAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
    }

    /**
     * 获取最新合同日期字符串的访问器
     * 格式化最新合同日期字符串为 'Y-m-d' 格式
     * @param string $value 原始最新合同日期字符串值
     * @return string 格式化后的最新合同日期字符串
     */
    public function getLatestContractDateStrAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
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
     * 获取业务助理关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessAssistant()
    {
        return $this->belongsTo(User::class, 'business_assistant_id');
    }

    /**
     * 获取业务协作人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessPartner()
    {
        return $this->belongsTo(User::class, 'business_partner_id');
    }

    /**
     * 获取公司负责人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function companyManager()
    {
        return $this->belongsTo(User::class, 'company_manager_id');
    }

    /**
     * 获取流程人员关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processStaff()
    {
        return $this->belongsTo(User::class, 'process_staff_id');
    }

    /**
     * 获取技术主导关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function techLead()
    {
        return $this->belongsTo(User::class, 'tech_lead_id');
    }

    /**
     * 获取园区关联关系
     * 建立与 `ParkConfig` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function park()
    {
        return $this->belongsTo(ParkConfig::class, 'park_id');
    }

    /**
     * 获取客户等级关联关系
     * 建立与 `CustomerLevel` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerLevel()
    {
        return $this->belongsTo(CustomerLevel::class, 'level', 'id');
    }

    /**
     * 获取客户规模关联关系
     * 建立与 `CustomerScale` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerScale()
    {
        return $this->belongsTo(CustomerScale::class, 'customer_scale', 'id');
    }

    /**
     * 获取客户等级名称的访问器
     * 通过关联关系获取客户等级名称
     * @return string|null 客户等级名称
     */
    public function getLevelNameAttribute()
    {
        return $this->customerLevel ? $this->customerLevel->level_name : null;
    }

    /**
     * 获取客户规模名称的访问器
     * 通过关联关系获取客户规模名称
     * @return string|null 客户规模名称
     */
    public function getScaleNameAttribute()
    {
        return $this->customerScale ? $this->customerScale->scale_name : null;
    }

    /**
     * 获取客户联系人关联关系
     * 建立与 `CustomerContact` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    /**
     * 获取主要联系人关联关系
     * 建立与 `CustomerContact` 模型的一对一关联，筛选主要联系人
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primaryContact()
    {
        return $this->hasOne(CustomerContact::class, 'customer_id')->where('is_primary', 1);
    }

    /**
     * 获取申请人关联关系
     * 建立与 `CustomerApplicant` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applicants()
    {
        return $this->hasMany(CustomerApplicant::class, 'customer_id');
    }

    /**
     * 获取发明人关联关系
     * 建立与 `CustomerInventor` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventors()
    {
        return $this->hasMany(CustomerInventor::class, 'customer_id');
    }

    /**
     * 获取商机关联关系
     * 建立与 `BusinessOpportunity` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function opportunities()
    {
        return $this->hasMany(BusinessOpportunity::class, 'customer_id');
    }

    /**
     * 获取客户的业务员列表关联关系
     * 建立与 `CustomerRelatedPerson` 模型的一对多关联，筛选业务员类型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businessPersons()
    {
        return $this->hasMany(CustomerRelatedPerson::class, 'customer_id')
                    ->where('person_type', '业务员')
                    ->with('relatedBusinessPerson');
    }

    /**
     * 获取合同关联关系
     * 建立与 `CustomerContract` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {
        return $this->hasMany(CustomerContract::class, 'customer_id');
    }

    /**
     * 获取跟进记录关联关系
     * 建立与 `CustomerFollowupRecord` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followupRecords()
    {
        return $this->hasMany(CustomerFollowupRecord::class, 'customer_id');
    }

    /**
     * 获取相关人员关联关系
     * 建立与 `CustomerRelatedPerson` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relatedPersons()
    {
        return $this->hasMany(CustomerRelatedPerson::class, 'customer_id');
    }

    /**
     * 获取文件关联关系
     * 建立与 `CustomerFile` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(CustomerFile::class, 'customer_id');
    }

    /**
     * 获取项目关联关系
     * 建立与 `Cases` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'customer_id');
    }
}
