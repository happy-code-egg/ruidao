<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 合同模型
 * 用于管理合同相关信息及业务逻辑
 */
class Contract extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'contracts';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'contract_no',              // 合同编号
        'contract_code',            // 合同代码
        'contract_name',            // 合同名称
        'customer_id',              // 客户ID
        'contract_type',            // 合同类型
        'service_type',             // 服务类型
        'status',                   // 合同状态
        'summary',                  // 合同摘要
        'business_person_id',       // 业务人员ID
        'technical_director_id',    // 技术主导ID
        'technical_department',     // 技术部门
        'paper_status',             // 纸质状态
        'party_a_contact_id',       // 甲方联系人ID
        'party_a_phone',            // 甲方电话
        'party_a_email',            // 甲方邮箱
        'party_a_address',          // 甲方地址
        'party_b_signer_id',        // 乙方签约人ID
        'party_b_phone',            // 乙方电话
        'party_b_company_id',       // 乙方公司ID
        'party_b_address',          // 乙方地址
        'service_fee',              // 服务费
        'official_fee',             // 官方费用
        'channel_fee',              // 渠道费用
        'total_service_fee',        // 总服务费
        'total_amount',             // 总金额
        'case_count',               // 案件数量
        'opportunity_no',           // 商机编号
        'opportunity_name',         // 商机名称
        'currency',                 // 货币类型
        'signing_date',             // 签约日期
        'validity_start_date',      // 有效期开始日期
        'validity_end_date',        // 有效期结束日期
        'additional_terms',         // 附加条款
        'remark',                   // 备注
        'last_process_time',        // 最后处理时间
        'process_remark',           // 处理备注
        'created_by',               // 创建人ID
        'updated_by',               // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',                 // 客户ID转换为整数
        'business_person_id' => 'integer',          // 业务人员ID转换为整数
        'technical_director_id' => 'integer',       // 技术主导ID转换为整数
        'party_a_contact_id' => 'integer',          // 甲方联系人ID转换为整数
        'party_b_signer_id' => 'integer',           // 乙方签约人ID转换为整数
        'party_b_company_id' => 'integer',          // 乙方公司ID转换为整数
        'service_type' => 'json',                   // 服务类型转换为JSON
        'paper_status' => 'string',                 // 纸质状态转换为字符串
        'service_fee' => 'decimal:2',               // 服务费转换为保留2位小数的浮点数
        'official_fee' => 'decimal:2',              // 官方费用转换为保留2位小数的浮点数
        'channel_fee' => 'decimal:2',               // 渠道费用转换为保留2位小数的浮点数
        'total_service_fee' => 'decimal:2',         // 总服务费转换为保留2位小数的浮点数
        'total_amount' => 'decimal:2',              // 总金额转换为保留2位小数的浮点数
        'case_count' => 'integer',                  // 案件数量转换为整数
        'signing_date' => 'date',                   // 签约日期转换为日期
        'validity_start_date' => 'date',            // 有效期开始日期转换为日期
        'validity_end_date' => 'date',              // 有效期结束日期转换为日期
        'last_process_time' => 'datetime',          // 最后处理时间转换为日期时间
        'created_by' => 'integer',                  // 创建人ID转换为整数
        'updated_by' => 'integer',                  // 更新人ID转换为整数
        'created_at' => 'datetime',                 // 创建时间转换为日期时间
        'updated_at' => 'datetime',                 // 更新时间转换为日期时间
        'deleted_at' => 'datetime',                 // 删除时间转换为日期时间
    ];

    /**
     * 合同类型常量
     */
    const TYPE_STANDARD = 'standard';       // 标准合同
    const TYPE_NON_STANDARD = 'non-standard'; // 非标合同

    /**
     * 合同状态常量
     */
    const STATUS_DRAFT = '草稿';            // 草稿状态
    const STATUS_PENDING = '待处理';        // 待处理状态
    const STATUS_CONFIRMING = '确认中';     // 确认中状态
    const STATUS_CONFIRMED = '已确认';      // 已确认状态
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
     * 获取创建时间的访问器
     * 格式化创建时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始创建时间值
     * @return string|null 格式化后的创建时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    /**
     * 获取更新时间的访问器
     * 格式化更新时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始更新时间值
     * @return string|null 格式化后的更新时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    /**
     * 获取最后处理时间的访问器
     * 格式化最后处理时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始最后处理时间值
     * @return string|null 格式化后的最后处理时间
     */
    public function getLastProcessTimeAttribute($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
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
     * 获取技术主导关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function technicalDirector()
    {
        return $this->belongsTo(User::class, 'technical_director_id');
    }

    /**
     * 获取甲方联系人关联关系
     * 建立与 `CustomerContact` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partyAContact()
    {
        return $this->belongsTo(CustomerContact::class, 'party_a_contact_id');
    }

    /**
     * 获取乙方签约人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partyBSigner()
    {
        return $this->belongsTo(User::class, 'party_b_signer_id');
    }

    /**
     * 获取乙方签约公司关联关系
     * 建立与 `OurCompanies` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partyBCompany()
    {
        return $this->belongsTo(OurCompanies::class, 'party_b_company_id');
    }

    /**
     * 获取合同服务项目关联关系
     * 建立与 `ContractService` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(ContractService::class, 'contract_id');
    }

    /**
     * 获取合同附件关联关系
     * 建立与 `ContractAttachment` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany(ContractAttachment::class, 'contract_id');
    }

    /**
     * 获取项目关联关系
     * 建立与 `Cases` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'contract_id');
    }

    /**
     * 获取合同项目记录关联关系
     * 建立与 `ContractCaseRecord` 模型的一对多关联
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contractCaseRecords()
    {
        return $this->hasMany(\App\Models\ContractCaseRecord::class, 'contract_id');
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
     * 生成合同编号
     * 格式为 HT+年月+四位序号，如 HT2023120001
     * @return string 生成的合同编号
     */
    public static function generateContractNo()
    {
        $prefix = 'HT' . date('Ym');  // 前缀为 HT+当前年月
        $latest = self::where('contract_no', 'like', $prefix . '%')
                     ->orderBy('contract_no', 'desc')
                     ->first();

        if ($latest) {
            $number = intval(substr($latest->contract_no, -4)) + 1;  // 取最后4位数字并加1
        } else {
            $number = 1;  // 如果没有找到记录，则从1开始
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);  // 补齐4位数字并拼接前缀
    }

    /**
     * 获取合同是否已过期的访问器
     * 判断合同有效期结束日期是否小于当前时间
     * @return bool 合同是否已过期
     */
    public function getIsExpiredAttribute()
    {
        return $this->validity_end_date && $this->validity_end_date < now();
    }

    /**
     * 获取合同有效期范围的访问器
     * 返回包含开始日期和结束日期的数组
     * @return array|null 合同有效期范围数组或null
     */
    public function getValidityRangeAttribute()
    {
        if ($this->validity_start_date && $this->validity_end_date) {
            return [$this->validity_start_date, $this->validity_end_date];
        }
        return null;
    }

    /**
     * 设置合同有效期范围的修改器
     * 接收包含开始日期和结束日期的数组，并分别赋值给对应字段
     * @param array $value 包含开始日期和结束日期的数组
     */
    public function setValidityRangeAttribute($value)
    {
        if (is_array($value) && count($value) == 2) {
            $this->validity_start_date = $value[0];
            $this->validity_end_date = $value[1];
        }
    }

    /**
     * 获取服务类型显示文本的访问器
     * 将服务类型数组转换为用顿号连接的字符串
     * @return string 服务类型显示文本
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
     * @return bool 是否为标准合同
     */
    public function isStandardContract()
    {
        return $this->contract_type === self::TYPE_STANDARD;
    }

    /**
     * 检查是否为非标合同
     * @return bool 是否为非标合同
     */
    public function isNonStandardContract()
    {
        return $this->contract_type === self::TYPE_NON_STANDARD;
    }

    /**
     * 验证服务类型格式是否与合同类型匹配
     * 标准合同的服务类型不应为数组，非标合同的服务类型应为数组
     * @return bool 验证结果
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
     * 建立与 `WorkflowInstance` 模型的一对一关联，筛选合同类型的业务实例
     * @return HasOne
     */
    public function workflowInstance(): HasOne
    {
        return $this->hasOne(WorkflowInstance::class, 'business_id')
            ->where('business_type', WorkflowInstance::BUSINESS_TYPE_CONTRACT)
            ->latest();
    }

    /**
     * 检查是否有进行中的工作流
     * @return bool 是否有进行中的工作流
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
     * @return array|null 工作流状态详情数组或null
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
     * @param string $workflowStatus 工作流状态
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
     * 更新合同下所有未立项的项目记录为待立项状态
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
