<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractCaseRecord extends Model
{
    use SoftDeletes;

    protected $table = 'contract_case_records';

    protected $fillable = [
        'case_code',
        'case_name',
        'customer_id',
        'contract_id',
        'case_id',
        'case_type',
        'case_subtype',
        'application_type',
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
        'service_fees',
        'official_fees',
        'attachments',
        'is_filed',
        'filed_at',
        'filed_by',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'contract_id' => 'integer',
        'case_id' => 'integer',
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
        'service_fees' => 'json',
        'official_fees' => 'json',
        'attachments' => 'json',
        'is_filed' => 'boolean',
        'filed_at' => 'datetime',
        'filed_by' => 'integer',
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
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取关联的案件
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
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
     * 获取立项人
     */
    public function filer()
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    /**
     * 获取技术主导
     */
    public function techLeader()
    {
        return $this->belongsTo(User::class, 'tech_leader');
    }

    /**
     * 获取售前支持
     */
    public function presaleSupport()
    {
        return $this->belongsTo(User::class, 'presale_support');
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
     * 作用域：待立项的记录
     */
    public function scopeToBeField($query)
    {
        return $query->where('case_status', self::STATUS_TO_BE_FILED)
                    ->where('is_filed', false);
    }

    /**
     * 作用域：已立项的记录
     */
    public function scopeFiled($query)
    {
        return $query->where('is_filed', true);
    }

    /**
     * 作用域：按项目类型筛选
     */
    public function scopeByType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * 作用域：按合同筛选
     */
    public function scopeByContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }
}
