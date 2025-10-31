<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceApplication extends Model
{
    use SoftDeletes;

    protected $table = 'invoice_applications';

    protected $fillable = [
        'application_no',
        'application_date',
        'applicant',
        'department',
        'customer_id',
        'customer_name',
        'customer_no',
        'contract_id',
        'contract_name',
        'contract_no',
        'buyer_name',
        'buyer_tax_id',
        'buyer_address',
        'buyer_bank_account',
        'invoice_type',
        'invoice_amount',
        'items',
        'flow_status',
        'current_handler',
        'priority',
        'invoice_number',
        'invoice_date',
        'invoice_files',
        'upload_remark',
        'approval_comment',
        'approved_at',
        'approved_by',
        'remark',
        'created_by',
        'updated_by',
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
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联合同
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 关联创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 关联审批人
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 关联历史记录
     */
    public function history()
    {
        return $this->hasMany(InvoiceApplicationHistory::class, 'invoice_application_id');
    }

    /**
     * 关联下载记录
     */
    public function downloadRecords()
    {
        return $this->hasMany(InvoiceDownloadRecord::class, 'invoice_application_id');
    }

    /**
     * 生成申请单号
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
     * 获取状态文本
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

