<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cases extends Model
{
    use SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_code',
        'case_name',
        'customer_id',
        'contract_id',
        'case_type',
        'case_subtype',
        'application_type',
        'application_method',
        'case_direction',
        'proposal_no',
        'company',
        'contract_number',
        'initial_stage',
        'annual_fee_stage',
        'prosecution_review',
        'preliminary_case',
        'early_publication',
        'confidential_application',
        'substantive_examination',
        'fast_track_case',
        'priority_examination',
        'acceptance_number',
        'has_materials',
        'acceptance_date',
        'location',
        'software_abbr',
        'version_number',
        'development_complete_date',
        'publish_status',
        'source_code_amount',
        'hardware_env',
        'software_env',
        'programming_language',
        'software_description',
        'main_features',
        'author',
        'project_year',
        'apply_batch',
        'tech_service_level',
        'supervisory_location',
        'supervisory_department',
        'government_reward',
        'cost_ratio',
        'technical_contact',
        'is_urgent',
        'case_handler',
        'estimated_start_date',
        'internal_deadline',
        'receipt_date',
        'estimated_completion_date',
        'payment_method',
        'estimated_final_payment',
        'contract_requirements',
        'project_requirements',
        'special_progress',
        'case_status',
        'case_phase',
        'priority_level',
        'application_no',
        'application_date',
        'registration_no',
        'registration_date',
        'acceptance_no',
        'country_code',
        'presale_support',
        'tech_leader',
        'tech_contact',
        'is_authorized',
        'project_no',
        'tech_service_name',
        'trademark_category',
        'trademark_image',
        'sound_trademark',
        'specified_color',
        'is_3d_mark',
        'color_format',
        'preliminary_announcement_date',
        'preliminary_announcement_period',
        'renewal_date',
        'end_date',
        'customer_file_number',
        'trademark_description',
        'registration_no',
        'publication_date',
        'product_id',
        'entity_type',
        'applicant_info',
        'inventor_info',
        'business_person_id',
        'agent_id',
        'assistant_id',
        'agency_id',
        'deadline_date',
        'annual_fee_due_date',
        'estimated_cost',
        'actual_cost',
        'service_fee',
        'official_fee',
        'is_priority',
        'priority_info',
        'classification_info',
        'case_description',
        'technical_field',
        'innovation_points',
        'remarks',
        'cooperative_personnel',
        'has_risk_clause',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'contract_id' => 'integer',
        'case_type' => 'integer',
        'case_status' => 'integer',
        'priority_level' => 'integer',
        'application_date' => 'date',
        'registration_date' => 'date',
        'entity_type' => 'integer',
        'presale_support' => 'integer',
        'tech_leader' => 'integer',
        'tech_contact' => 'integer',
        'is_authorized' => 'integer',
        'product_id' => 'integer',
        'business_person_id' => 'integer',
        'agent_id' => 'integer',
        'assistant_id' => 'integer',
        'agency_id' => 'integer',
        'deadline_date' => 'date',
        'annual_fee_due_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'official_fee' => 'decimal:2',
        'is_priority' => 'integer',
        'applicant_info' => 'json',
        'inventor_info' => 'json',
        'priority_info' => 'json',
        'classification_info' => 'json',
        'cooperative_personnel' => 'json',
        'has_risk_clause' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 案例类型常量
     */
    const TYPE_PATENT = 1;
    const TYPE_TRADEMARK = 2;
    const TYPE_COPYRIGHT = 3;
    const TYPE_TECH_SERVICE = 4;

    /**
     * 案例状态常量
     */
    const STATUS_DRAFT = 1;
    const STATUS_TO_BE_FILED = 2;  // 待立项
    const STATUS_SUBMITTED = 3;
    const STATUS_PROCESSING = 4;
    const STATUS_AUTHORIZED = 5;
    const STATUS_REJECTED = 6;
    const STATUS_COMPLETED = 7;
    const STATUS_ARCHIVED = 8;     // 已归档

    /**
     * 优先级常量
     */
    const PRIORITY_HIGH = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_LOW = 3;

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取合同
     */
    public function contract()
    {
        return $this->belongsTo(CustomerContract::class, 'contract_id');
    }

    /**
     * 获取产品信息
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * 获取业务人员
     */
    public function businessPerson()
    {
        return $this->belongsTo(User::class, 'business_person_id');
    }

    /**
     * 获取技术主导
     */
    public function techLeader()
    {
        return $this->belongsTo(User::class, 'tech_leader');
    }

    /**
     * 获取代理师
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * 获取助理
     */
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
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
     */
    public function fees()
    {
        return $this->hasMany(CaseFee::class, 'case_id');
    }

    /**
     * 获取服务费
     */
    public function serviceFees()
    {
        return $this->hasMany(CaseFee::class, 'case_id')->where('fee_type', 'service');
    }

    /**
     * 获取官费
     */
    public function officialFees()
    {
        return $this->hasMany(CaseFee::class, 'case_id')->where('fee_type', 'official');
    }

    /**
     * 获取案例附件
     */
    public function attachments()
    {
        return $this->hasMany(CaseAttachment::class, 'case_id');
    }
    
    /**
     * 获取处理事项
     */
    public function processes()
    {
        return $this->hasMany(CaseProcess::class, 'case_id');
    }

    /**
     * 案例类型文本
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
     */
    public function workflowInstance()
    {
        return $this->hasOne(WorkflowInstance::class, 'business_id')
                    ->where('business_type', 'case')
                    ->latest('created_at');
    }

    /**
     * 获取所有工作流实例
     */
    public function workflowInstances()
    {
        return $this->hasMany(WorkflowInstance::class, 'business_id')
                    ->where('business_type', 'case')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * 获取当前用户在此案例中的待办任务
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