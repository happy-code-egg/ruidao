<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 发票下载记录模型
 * 用于记录发票文件的下载历史信息
 */
class InvoiceDownloadRecord extends Model
{
    protected $table = 'invoice_download_records';

    protected $fillable = [
        'invoice_application_id', // 发票申请ID
        'downloader',             // 下载人
        'download_ip',            // 下载IP地址
        'download_type',          // 下载类型
        'remark',                 // 备注
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
     * 格式化下载时间
     * 将 created_at 转换为 Y-m-d H:i:s 格式的时间字符串
     * @return string 格式化的下载时间字符串
     */
    public function getDownloadTimeAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}

