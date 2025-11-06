<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseAttachment extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_attachments';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'case_id',          // 案例ID
        'file_type',        // 文件类型
        'file_sub_type',    // 文件子类型
        'file_desc',        // 文件描述
        'file_name',        // 文件名
        'file_path',        // 文件路径
        'file_size',        // 文件大小(字节)
        'original_name',    // 原始文件名
        'document_type',    // 文档类型
        'upload_date'       // 上传日期
    ];

    // 需要被Carbon\Carbon实例化的日期属性
    protected $dates = [
        'upload_date',  // 上传日期
        'created_at',   // 创建时间
        'updated_at'    // 更新时间
    ];

    /**
     * 获取关联的案例
     * 建立与 Cases 模型的一对多反向关联
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * 获取文件大小的可读格式
     * 将字节大小转换为人类可读的格式(B, KB, MB, GB, TB)
     */
    public function getReadableFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        // 通过不断除以1024来确定合适的单位
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        // 返回格式化后的文件大小，保留2位小数
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
