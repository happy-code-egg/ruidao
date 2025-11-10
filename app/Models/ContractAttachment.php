<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合同附件模型
 * 用于管理合同相关的附件文件信息
 */
class ContractAttachment extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'contract_attachments';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'contract_id',      // 合同ID
        'file_type',        // 文件类型
        'file_sub_type',    // 文件子类型
        'file_name',        // 文件名
        'file_path',        // 文件路径
        'file_size',        // 文件大小(字节)
        'file_extension',   // 文件扩展名
        'mime_type',        // MIME类型
        'uploader_id',      // 上传人ID
        'upload_time',      // 上传时间
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'contract_id' => 'integer',     // 合同ID转换为整数
        'file_size' => 'integer',       // 文件大小转换为整数
        'uploader_id' => 'integer',     // 上传人ID转换为整数
        'upload_time' => 'datetime',    // 上传时间转换为日期时间
        'created_at' => 'datetime',     // 创建时间转换为日期时间
        'updated_at' => 'datetime',     // 更新时间转换为日期时间
        'deleted_at' => 'datetime',     // 删除时间转换为日期时间
    ];

    /**
     * 获取所属合同关联关系
     * 建立与 `Contract` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取上传人员关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * 获取文件大小（格式化）的访问器
     * 将字节大小转换为人类可读的格式(GB/MB/KB/bytes)
     * @return string 格式化后的文件大小
     */
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';  // 大于等于1GB时显示GB
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';     // 大于等于1MB时显示MB
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';        // 大于等于1KB时显示KB
        } else {
            return $bytes . ' bytes';                              // 小于1KB时显示bytes
        }
    }

    /**
     * 获取文件下载URL的访问器
     * 根据文件路径生成可访问的下载链接
     * @return string|null 文件下载URL或null
     */
    public function getDownloadUrlAttribute()
    {
        if ($this->file_path) {
            return url('storage/' . $this->file_path);  // 拼接storage路径生成完整URL
        }
        return null;
    }
}
