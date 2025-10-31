<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDownloadRecord extends Model
{
    protected $table = 'invoice_download_records';

    protected $fillable = [
        'invoice_application_id',
        'downloader',
        'download_ip',
        'download_type',
        'remark',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联发票申请
     */
    public function invoiceApplication()
    {
        return $this->belongsTo(InvoiceApplication::class, 'invoice_application_id');
    }

    /**
     * 格式化下载时间
     */
    public function getDownloadTimeAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}

