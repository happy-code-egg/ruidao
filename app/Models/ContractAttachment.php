<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'contract_attachments';

    protected $fillable = [
        'contract_id',
        'file_type',
        'file_sub_type',
        'file_name',
        'file_path',
        'file_size',
        'file_extension',
        'mime_type',
        'uploader_id',
        'upload_time',
    ];

    protected $casts = [
        'contract_id' => 'integer',
        'file_size' => 'integer',
        'uploader_id' => 'integer',
        'upload_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取所属合同
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取上传人员
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * 获取文件大小（格式化）
     */
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * 获取文件下载URL
     */
    public function getDownloadUrlAttribute()
    {
        if ($this->file_path) {
            return url('storage/' . $this->file_path);
        }
        return null;
    }
}
