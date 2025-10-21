<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'customer_name',
        'credit_code',
        'customer_type',
        'customer_level',
        'customer_scale',
        'industry',
        'registered_address',
        'office_address',
        'legal_person',
        'contact_phone',
        'contact_email',
        'website',
        'business_scope',
        'company_type',
        'business_person_id',
        'business_assistant_id',
        'business_partner_id',
        'company_manager_id',
        'source_channel',
        'park_id',
        'customer_status',
        'latest_contract_date',
        'contract_count',
        'total_amount',
        'remarks',
        'created_by',
        'updated_by',
        // 第一批新增字段
        'province',
        'city',
        'district',
        'case_count',
        'customer_no',
        'process_staff_id',
        'economic_category',
        'economic_door',
        'economic_big_class',
        'economic_mid_class',
        'economic_small_class',
        'sales_2021',
        'research_fee_2021',
        'loan_2021',
        'high_tech_enterprise',
        'province_enterprise',
        'city_enterprise',
        'province_tech_center',
        'ip_standard',
        'it_standard',
        'innovation_index',
        'price_index',
        // 资质认证字段
        'is_jinxin_verified',
        'jinxin_verify_date',
        'is_science_verified',
        'science_verify_date',
        'high_tech_date',
        'province_enterprise_date',
        'city_enterprise_date',
        'province_tech_center_date',
        'ip_standard_date',
        'info_standard_date',
        // 工商信息字段
        'industry_classification',
        'paid_capital',
        'business_term',
        'registration_authority',
        'approval_date',
        'registration_status',
        // 第二批新增字段（前端匹配）
        'name',
        'name_en',
        'customer_code_alias',
        'legal_representative',
        'company_manager',
        'level',
        'employee_count',
        'business_person',
        'business_assistant',
        'business_partner',
        'price_index_str',
        'innovation_index_str',
        'contract_count_str',
        'latest_contract_date_str',
        'created_by',
        'create_date',
        'create_time',
        'updated_by',
        'update_time',
        'remark',
        'contact_name',
        'email',
        'qq',
        'wechat',
        'country',
        'address',
        'address_en',
        'other_address',
        'industrial_park',
        'zip_code',
        'account_name',
        'bank_name',
        'bank_account',
        'invoice_address',
        'invoice_phone',
        'is_general_taxpayer',
        'billing_address',
        'invoice_credit_code',
        'founding_date',
        'main_products',
        'company_staff_count',
        'registered_capital',
        'research_staff_count',
        'doctor_count',
        'master_count',
        'bachelor_count',
        'overseas_returnee_count',
        'middle_engineer_count',
        'senior_engineer_count',
        'trademark_count',
        'patent_count',
        'invention_patent_count',
        'copyright_count',
        'has_additional_deduction',
        'has_school_cooperation',
        'cooperation_school',
        'is_jinxin_verified',
        'jinxin_verify_date',
        'is_science_verified',
        'science_verify_date',
        'high_tech_enterprise_str',
        'high_tech_date',
        'province_enterprise_str',
        'province_enterprise_date',
        'city_enterprise_str',
        'city_enterprise_date',
        'province_tech_center_str',
        'province_tech_center_date',
        'ip_standard_str',
        'ip_standard_date',
        'it_standard_str',
        'info_standard_date',
        'spare1',
        'spare2',
        'spare3',
        'spare4',
        'spare5',
        'original_salesperson',
        'public_sea_name',
        'sales_data',
        'rd_cost_data',
        'loan_data',
        'research_project_data',
        'project_amount_data',
        'rd_equipment_original_value_data',
        'has_audit_report_data',
        'asset_liability_ratio_data',
        'fixed_asset_investment_data',
        'equipment_investment_data',
        'smart_equipment_investment_data',
        'rd_equipment_investment_data',
        'it_investment_data',
        'has_imported_equipment_data',
        'has_investment_record_data',
        'record_amount_data',
        'record_period_data',
        'rating',
        'avatar',
        'tags',
        'important_events',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'customer_type' => 'integer',
        'customer_level' => 'integer',
        'customer_scale' => 'integer',
        'business_person_id' => 'integer',
        'business_assistant_id' => 'integer',
        'business_partner_id' => 'integer',
        'company_manager_id' => 'integer',
        'park_id' => 'integer',
        'customer_status' => 'integer',
        'latest_contract_date' => 'date',
        'contract_count' => 'integer',
        'total_amount' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        // 第一批新增字段的类型转换
        'case_count' => 'integer',
        'process_staff_id' => 'integer',
        'sales_2021' => 'decimal:2',
        'research_fee_2021' => 'decimal:2',
        'loan_2021' => 'decimal:2',
        'high_tech_enterprise' => 'string',
        'province_enterprise' => 'string',
        'city_enterprise' => 'string',
        'province_tech_center' => 'string',
        'ip_standard' => 'string',
        'it_standard' => 'string',
        'is_jinxin_verified' => 'string',
        'is_science_verified' => 'string',
        'innovation_index' => 'integer',
        'price_index' => 'integer',
        'level' => 'integer',
        'business_person' => 'integer',
        // 日期字段
        'jinxin_verify_date' => 'date',
        'science_verify_date' => 'date',
        'high_tech_date' => 'date',
        'province_enterprise_date' => 'date',
        'city_enterprise_date' => 'date',
        'province_tech_center_date' => 'date',
        'ip_standard_date' => 'date',
        'info_standard_date' => 'date',
        'approval_date' => 'date',
        // 数值字段
        'paid_capital' => 'decimal:2',
        // 第二批新增字段的类型转换
        'founding_date' => 'date',
        'is_general_taxpayer' => 'boolean',
        'trademark_count' => 'integer',
        'patent_count' => 'integer',
        'invention_patent_count' => 'integer',
        'copyright_count' => 'integer',
        'has_additional_deduction' => 'boolean',
        'has_school_cooperation' => 'boolean',
        'rating' => 'decimal:1',
        // JSON字段的类型转换
        'sales_data' => 'json',
        'rd_cost_data' => 'json',
        'loan_data' => 'json',
        'research_project_data' => 'json',
        'project_amount_data' => 'json',
        'rd_equipment_original_value_data' => 'json',
        'has_audit_report_data' => 'json',
        'asset_liability_ratio_data' => 'json',
        'fixed_asset_investment_data' => 'json',
        'equipment_investment_data' => 'json',
        'smart_equipment_investment_data' => 'json',
        'rd_equipment_investment_data' => 'json',
        'it_investment_data' => 'json',
        'has_imported_equipment_data' => 'json',
        'has_investment_record_data' => 'json',
        'record_amount_data' => 'json',
        'record_period_data' => 'json',
        'tags' => 'json',
        'important_events' => 'json',
    ];

    /**
     * 客户类型常量
     */
    const TYPE_ENTERPRISE = 1;
    const TYPE_INDIVIDUAL = 2;
    const TYPE_INSTITUTION = 3;

    /**
     * 客户等级常量
     */
    const LEVEL_IMPORTANT = 1;
    const LEVEL_GENERAL = 2;
    const LEVEL_POTENTIAL = 3;

    /**
     * 客户状态常量
     */
    const STATUS_NORMAL = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_TERMINATED = 3;

    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getLatestContractDateAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
    }

    public function getLatestContractDateStrAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d');
    }


    /**
     * 获取业务人员
     */
    public function businessPerson()
    {
        return $this->belongsTo(User::class, 'business_person_id');
    }

    /**
     * 获取业务助理
     */
    public function businessAssistant()
    {
        return $this->belongsTo(User::class, 'business_assistant_id');
    }

    /**
     * 获取业务协作人
     */
    public function businessPartner()
    {
        return $this->belongsTo(User::class, 'business_partner_id');
    }

    /**
     * 获取公司负责人
     */
    public function companyManager()
    {
        return $this->belongsTo(User::class, 'company_manager_id');
    }

    /**
     * 获取流程人员
     */
    public function processStaff()
    {
        return $this->belongsTo(User::class, 'process_staff_id');
    }

    /**
     * 获取技术主导
     */
    public function techLead()
    {
        return $this->belongsTo(User::class, 'tech_lead_id');
    }

    /**
     * 获取园区
     */
    public function park()
    {
        return $this->belongsTo(ParkConfig::class, 'park_id');
    }

    /**
     * 获取客户等级
     */
    public function customerLevel()
    {
        return $this->belongsTo(CustomerLevel::class, 'level', 'id');
    }

    /**
     * 获取客户规模
     */
    public function customerScale()
    {
        return $this->belongsTo(CustomerScale::class, 'customer_scale', 'id');
    }

    /**
     * 获取客户等级名称
     */
    public function getLevelNameAttribute()
    {
        return $this->customerLevel ? $this->customerLevel->level_name : null;
    }

    /**
     * 获取客户规模名称
     */
    public function getScaleNameAttribute()
    {
        return $this->customerScale ? $this->customerScale->scale_name : null;
    }

    /**
     * 获取客户联系人
     */
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    /**
     * 获取主要联系人
     */
    public function primaryContact()
    {
        return $this->hasOne(CustomerContact::class, 'customer_id')->where('is_primary', 1);
    }

    /**
     * 获取申请人
     */
    public function applicants()
    {
        return $this->hasMany(CustomerApplicant::class, 'customer_id');
    }

    /**
     * 获取发明人
     */
    public function inventors()
    {
        return $this->hasMany(CustomerInventor::class, 'customer_id');
    }

    /**
     * 获取商机
     */
    public function opportunities()
    {
        return $this->hasMany(BusinessOpportunity::class, 'customer_id');
    }

    /**
     * 获取客户的业务员列表
     */
    public function businessPersons()
    {
        return $this->hasMany(CustomerRelatedPerson::class, 'customer_id')
                    ->where('person_type', '业务员')
                    ->with('relatedBusinessPerson');
    }

    /**
     * 获取合同
     */
    public function contracts()
    {
        return $this->hasMany(CustomerContract::class, 'customer_id');
    }

    /**
     * 获取跟进记录
     */
    public function followupRecords()
    {
        return $this->hasMany(CustomerFollowupRecord::class, 'customer_id');
    }

    /**
     * 获取相关人员
     */
    public function relatedPersons()
    {
        return $this->hasMany(CustomerRelatedPerson::class, 'customer_id');
    }

    /**
     * 获取文件
     */
    public function files()
    {
        return $this->hasMany(CustomerFile::class, 'customer_id');
    }

    /**
     * 获取项目
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'customer_id');
    }
}
