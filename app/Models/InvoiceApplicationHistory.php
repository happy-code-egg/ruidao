<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 发票申请历史记录模型
 * 用于记录发票申请流程中的各环节操作历史
 */
class InvoiceApplicationHistory extends Model
{
    protected $table = 'invoice_application_history';

    protected $fillable = [
        'invoice_application_id', // 发票申请ID
        'title',                  // 操作标题
        'handler',                // 处理人
        'action',                 // 操作类型
        'comment',                // 操作说明
        'type',                   // 记录类型
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联发票申请
     * 通过 `invoice_application_id` 字段关联 `InvoiceApplication` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceApplication()
    {
        return $this->belongsTo(InvoiceApplication::class, 'invoice_application_id');
    }

    /**
     * 格式化时间戳
     * 将 created_at 转换为 Y-m-d H:i 格式的时间字符串
     * @return string 格式化的时间字符串
     */
    public function getTimestampAttribute()
    {
        return $this->created_at->format('Y-m-d H:i');
    }
}

