<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceApplicationHistory extends Model
{
    protected $table = 'invoice_application_history';

    protected $fillable = [
        'invoice_application_id',
        'title',
        'handler',
        'action',
        'comment',
        'type',
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
     * 格式化时间戳
     */
    public function getTimestampAttribute()
    {
        return $this->created_at->format('Y-m-d H:i');
    }
}

