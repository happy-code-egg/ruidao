<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseAttachment extends Model
{
    protected $table = 'case_attachments';

    protected $fillable = [
        'case_id',
        'file_type',
        'file_sub_type',
        'file_desc',
        'file_name',
        'file_path',
        'file_size',
        'original_name',
        'document_type',
        'upload_date'
    ];

    protected $dates = [
        'upload_date',
        'created_at',
        'updated_at'
    ];

    /**
     * 获取关联的案例
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 获取文件大小的可读格式
     */
    public function getReadableFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}


