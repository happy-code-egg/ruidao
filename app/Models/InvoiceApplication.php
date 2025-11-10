<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 发票申请模型
 * 用于管理发票申请的相关信息，包括申请流程、发票信息和审批记录等
 */
class InvoiceApplication extends Model
{
    use SoftDeletes;

    protected $table = 'invoice_applications';

    protected $fillable = [
        'application_no',      // 申请单号
        'application_date',    // 申请日期
        'applicant',           // 申请人
        'department',          // 部门
        'customer_id',         // 客户ID
        'customer_name',       // 客户名称
        'customer_no',         // 客户编号
        'contract_id',         // 合同ID
        'contract_name',       // 合同名称
        'contract_no',         // 合同编号
        'buyer_name',          // 购买方名称
        'buyer_tax_id',        // 购买方税号
        'buyer_address',       // 购买方地址
        'buyer_bank_account',  // 购买方银行账户
        'invoice_type',        // 发票类型
        'invoice_amount',      // 发票金额
        'items',               // 明细项目（数组）
        'flow_status',         // 流程状态
        'current_handler',     // 当前处理人
        'priority',            // 优先级
        'invoice_number',      // 发票号码
        'invoice_date',        // 发票日期
        'invoice_files',       // 发票文件（数组）
        'upload_remark',       // 上传备注
        'approval_comment',    // 审批意见
        'approved_at',         // 审批时间
        'approved_by',         // 审批人
        'remark',              // 备注
        'created_by',          // 创建人ID
        'updated_by',          // 更新人ID
    ];

    protected $casts = [
        'application_date' => 'date',
        'invoice_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'items' => 'array',
        'invoice_files' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 关联客户
     * 通过 `customer_id` 字段关联 `Customer` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联合同
     * 通过 `contract_id` 字段关联 `Contract` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 关联创建人
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 关联审批人
     * 通过 `approved_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 关联历史记录
     * 通过 `invoice_application_id` 字段关联 `InvoiceApplicationHistory` 模型，一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history()
    {
        return $this->hasMany(InvoiceApplicationHistory::class, 'invoice_application_id');
    }

    /**
     * 关联下载记录
     * 通过 `invoice_application_id` 字段关联 `InvoiceDownloadRecord` 模型，一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloadRecords()
    {
        return $this->hasMany(InvoiceDownloadRecord::class, 'invoice_application_id');
    }

    /**
     * 生成申请单号
     * 生成格式为 FPSP + 年月日 + 4位序号 的申请单号
     * @return string 生成的申请单号
     */
    public static function generateApplicationNo()
    {
        $prefix = 'FPSP';
        $date = date('Ymd');
        $lastRecord = self::where('application_no', 'like', $prefix . $date . '%')
            ->orderBy('application_no', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNo = intval(substr($lastRecord->application_no, -4));
            $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }

        return $prefix . $date . $newNo;
    }

    /**
     * 获取流程状态文本
     * 将 flow_status 字段值转换为对应的中文状态文本
     * @return string 状态文本（草稿、审核中、已通过等）
     */
    public function getFlowStatusTextAttribute()
    {
        $statusMap = [
            'draft' => '草稿',
            'reviewing' => '审核中',
            'approved' => '已通过',
            'rejected' => '已退回',
            'completed' => '已完成',
        ];

        return $statusMap[$this->flow_status] ?? $this->flow_status;
    }

    /**
     * 获取发票类型文本
     * 将 invoice_type 字段值转换为对应的中文类型文本
     * @return string 发票类型文本（增值税专用发票、增值税普通发票、电子发票等）
     */
    public function getInvoiceTypeTextAttribute()
    {
        $typeMap = [
            'special' => '增值税专用发票',
            'normal' => '增值税普通发票',
            'electronic' => '电子发票',
        ];

        return $typeMap[$this->invoice_type] ?? $this->invoice_type;
    }

    /**
     * 获取优先级文本
     * 将 priority 字段值转换为对应的中文优先级文本
     * @return string 优先级文本（紧急、普通、低等）
     */
    public function getPriorityTextAttribute()
    {
        $priorityMap = [
            'urgent' => '紧急',
            'normal' => '普通',
            'low' => '低',
        ];

        return $priorityMap[$this->priority] ?? $this->priority;
    }
}

