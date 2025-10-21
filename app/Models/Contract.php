<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contract extends Model
{
    use SoftDeletes;

    protected $table = 'contracts';

    protected $fillable = [
        'contract_no',
        'contract_code',
        'contract_name',
        'customer_id',
        'contract_type',
        'service_type',
        'status',
        'summary',
        'business_person_id',
        'technical_director_id',
        'technical_department',
        'paper_status',
        'party_a_contact_id',
        'party_a_phone',
        'party_a_email',
        'party_a_address',
        'party_b_signer_id',
        'party_b_phone',
        'party_b_company_id',
        'party_b_address',
        'service_fee',
        'official_fee',
        'channel_fee',
        'total_service_fee',
        'total_amount',
        'case_count',
        'opportunity_no',
        'opportunity_name',
        'currency',
        'signing_date',
        'validity_start_date',
        'validity_end_date',
        'additional_terms',
        'remark',
        'last_process_time',
        'process_remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'business_person_id' => 'integer',
        'technical_director_id' => 'integer',
        'party_a_contact_id' => 'integer',
        'party_b_signer_id' => 'integer',
        'party_b_company_id' => 'integer',
        'service_type' => 'json',
        'paper_status' => 'string',
        'service_fee' => 'decimal:2',
        'official_fee' => 'decimal:2',
        'channel_fee' => 'decimal:2',
        'total_service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'case_count' => 'integer',
        'signing_date' => 'date',
        'validity_start_date' => 'date',
        'validity_end_date' => 'date',
        'last_process_time' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 合同类型常量
     */
    const TYPE_STANDARD = 'standard';
    const TYPE_NON_STANDARD = 'non-standard';

    /**
     * 合同状态常量
     */
    const STATUS_DRAFT = '草稿';
    const STATUS_PENDING = '待处理';
    const STATUS_CONFIRMING = '确认中';
    const STATUS_CONFIRMED = '已确认';
    const STATUS_TERMINATED = '已终止';

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function getLastProcessTimeAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
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
    public function technicalDirector()
    {
        return $this->belongsTo(User::class, 'technical_director_id');
    }

    /**
     * 获取甲方联系人
     */
    public function partyAContact()
    {
        return $this->belongsTo(CustomerContact::class, 'party_a_contact_id');
    }

    /**
     * 获取乙方签约人
     */
    public function partyBSigner()
    {
        return $this->belongsTo(User::class, 'party_b_signer_id');
    }

    /**
     * 获取乙方签约公司
     */
    public function partyBCompany()
    {
        return $this->belongsTo(OurCompanies::class, 'party_b_company_id');
    }

    /**
     * 获取合同服务项目
     */
    public function services()
    {
        return $this->hasMany(ContractService::class, 'contract_id');
    }

    /**
     * 获取合同附件
     */
    public function attachments()
    {
        return $this->hasMany(ContractAttachment::class, 'contract_id');
    }

    /**
     * 获取项目
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'contract_id');
    }

    /**
     * 获取合同项目记录
     */
    public function contractCaseRecords()
    {
        return $this->hasMany(\App\Models\ContractCaseRecord::class, 'contract_id');
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
     * 生成合同编号
     */
    public static function generateContractNo()
    {
        $prefix = 'HT' . date('Ym');
        $latest = self::where('contract_no', 'like', $prefix . '%')
                     ->orderBy('contract_no', 'desc')
                     ->first();

        if ($latest) {
            $number = intval(substr($latest->contract_no, -4)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 检查合同是否已过期
     */
    public function getIsExpiredAttribute()
    {
        return $this->validity_end_date && $this->validity_end_date < now();
    }

    /**
     * 获取合同有效期范围
     */
    public function getValidityRangeAttribute()
    {
        if ($this->validity_start_date && $this->validity_end_date) {
            return [$this->validity_start_date, $this->validity_end_date];
        }
        return null;
    }

    /**
     * 设置合同有效期范围
     */
    public function setValidityRangeAttribute($value)
    {
        if (is_array($value) && count($value) == 2) {
            $this->validity_start_date = $value[0];
            $this->validity_end_date = $value[1];
        }
    }

    /**
     * 获取服务类型显示文本
     */
    public function getServiceTypeTextAttribute()
    {
        if (empty($this->service_type)) {
            return '';
        }

        if (is_array($this->service_type)) {
            return implode('、', $this->service_type);
        }

        return $this->service_type;
    }

    /**
     * 检查是否为标准合同
     */
    public function isStandardContract()
    {
        return $this->contract_type === self::TYPE_STANDARD;
    }

    /**
     * 检查是否为非标合同
     */
    public function isNonStandardContract()
    {
        return $this->contract_type === self::TYPE_NON_STANDARD;
    }

    /**
     * 验证服务类型格式是否与合同类型匹配
     */
    public function validateServiceTypeFormat()
    {
        if ($this->isStandardContract() && is_array($this->service_type)) {
            return false; // 标准合同不应该是数组
        }

        if ($this->isNonStandardContract() && !is_array($this->service_type)) {
            return false; // 非标合同应该是数组
        }

        return true;
    }

    /**
     * 关联工作流实例
     */
    public function workflowInstance(): HasOne
    {
        return $this->hasOne(WorkflowInstance::class, 'business_id')
            ->where('business_type', WorkflowInstance::BUSINESS_TYPE_CONTRACT)
            ->latest();
    }

    /**
     * 检查是否有进行中的工作流
     */
    public function hasPendingWorkflow(): bool
    {
        $instance = $this->workflowInstance()
            ->where('status', WorkflowInstance::STATUS_PENDING)
            ->first();
            
        if (!$instance) {
            return false;
        }
        
        // 如果工作流在第0个节点（创建节点），且没有待处理任务，则视为可以重新发起
        if ($instance->current_node_index === 0) {
            $hasPendingTask = $instance->processes()
                ->where('node_index', 0)
                ->where('action', 'pending')
                ->exists();
            return $hasPendingTask;
        }
        
        return true;
    }

    /**
     * 获取当前工作流状态详情
     */
    public function getWorkflowStatus()
    {
        // 查询最新的工作流实例（包括已完成的）
        $instance = $this->workflowInstance()
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$instance) {
            return null;
        }

        return [
            'instance_id' => $instance->id,
            'current_node_index' => $instance->current_node_index,
            'current_node_name' => $instance->getCurrentNodeName(),
            'status' => $instance->status,
            'created_at' => $instance->created_at
        ];
    }

    /**
     * 根据工作流状态更新合同状态
     */
    public function updateStatusByWorkflow($workflowStatus)
    {
        $statusMap = [
            WorkflowInstance::STATUS_PENDING => '审批中',
            WorkflowInstance::STATUS_COMPLETED => '已完成',
            WorkflowInstance::STATUS_REJECTED => '已驳回',
            WorkflowInstance::STATUS_CANCELLED => '已取消'
        ];

        if (isset($statusMap[$workflowStatus])) {
            $this->update(['status' => $statusMap[$workflowStatus]]);
            
            // 当合同审批完成时，自动推送项目到待立项
            if ($workflowStatus === \App\Models\WorkflowInstance::STATUS_COMPLETED) {
                $this->pushProjectsToFiling();
            }
        }
    }

    /**
     * 将合同项目推送到待立项状态
     */
    private function pushProjectsToFiling()
    {
        try {
            // 更新该合同下所有未立项的项目记录为待立项状态
            $updatedCount = $this->contractCaseRecords()
                ->where('case_status', '!=', \App\Models\ContractCaseRecord::STATUS_TO_BE_FILED)
                ->where('is_filed', false)
                ->update([
                    'case_status' => \App\Models\ContractCaseRecord::STATUS_TO_BE_FILED,
                    'updated_at' => now(),
                    'updated_by' => auth()->id() ?? 1
                ]);
                
            \Illuminate\Support\Facades\Log::info('合同项目已推送到待立项', [
                'contract_id' => $this->id,
                'contract_code' => $this->contract_code,
                'updated_projects' => $updatedCount
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('推送合同项目到待立项失败', [
                'contract_id' => $this->id,
                'contract_code' => $this->contract_code,
                'error' => $e->getMessage()
            ]);
        }
    }
}
