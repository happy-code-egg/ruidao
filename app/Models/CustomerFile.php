<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CustomerFile extends Model
{
    use SoftDeletes;

    protected $table = 'customer_files';

    protected $fillable = [
        'customer_id',
        'file_name',
        'file_original_name',
        'file_path',
        'file_type',
        'file_category',
        'file_size',
        'mime_type',
        'file_description',
        'is_private',
        'permission_type',
        'allowed_departments',
        'allowed_users',
        'uploaded_by',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'file_size' => 'integer',
        'is_private' => 'boolean',
        'allowed_departments' => 'array',
        'allowed_users' => 'array',
        'uploaded_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 文件分类常量
     */
    const CATEGORY_CONTRACT = '合同';
    const CATEGORY_CERTIFICATE = '证件';
    const CATEGORY_TECH_DOC = '技术资料';
    const CATEGORY_BUSINESS_DOC = '商务资料';
    const CATEGORY_FINANCE_DOC = '财务资料';
    const CATEGORY_OTHER = '其他';

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取上传者
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取文件大小格式化文本
     */
    public function getFileSizeTextAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * 获取文件下载URL
     */
    public function getDownloadUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * 获取文件分类选项
     */
    public static function getFileCategories()
    {
        return [
            self::CATEGORY_CONTRACT => '合同',
            self::CATEGORY_CERTIFICATE => '证件',
            self::CATEGORY_TECH_DOC => '技术资料',
            self::CATEGORY_BUSINESS_DOC => '商务资料',
            self::CATEGORY_FINANCE_DOC => '财务资料',
            self::CATEGORY_OTHER => '其他',
        ];
    }

    /**
     * 获取文件图标
     */
    public function getFileIconAttribute()
    {
        $extension = strtolower(pathinfo($this->file_original_name, PATHINFO_EXTENSION));
        
        $iconMap = [
            'pdf' => 'el-icon-document',
            'doc' => 'el-icon-document',
            'docx' => 'el-icon-document',
            'xls' => 'el-icon-document',
            'xlsx' => 'el-icon-document',
            'ppt' => 'el-icon-document',
            'pptx' => 'el-icon-document',
            'txt' => 'el-icon-document',
            'jpg' => 'el-icon-picture',
            'jpeg' => 'el-icon-picture',
            'png' => 'el-icon-picture',
            'gif' => 'el-icon-picture',
            'zip' => 'el-icon-folder-opened',
            'rar' => 'el-icon-folder-opened',
            '7z' => 'el-icon-folder-opened',
        ];

        return $iconMap[$extension] ?? 'el-icon-document';
    }

    /**
     * 获取格式化文件大小
     */
    public function getFileSizeFormatted()
    {
        return $this->getFileSizeTextAttribute();
    }

    /**
     * 获取下载URL
     */
    public function getDownloadUrl()
    {
        return $this->getDownloadUrlAttribute();
    }

    /**
     * 检查是否可以下载
     */
    public function canDownload()
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // 管理员可以下载所有文件
        if ($user->role === 'admin') {
            return true;
        }

        // 上传者可以下载
        if ($user->id === $this->uploaded_by) {
            return true;
        }

        // 客户的商务人员可以下载
        if ($this->customer && $this->customer->business_person_id === $user->id) {
            return true;
        }

        // 根据权限类型检查
        switch ($this->permission_type) {
            case 'public':
                return true;

            case 'department':
                // 检查用户是否在允许的部门列表中
                if ($this->allowed_departments && is_array($this->allowed_departments)) {
                    $userDepartment = $user->department ?? '';
                    return in_array($userDepartment, $this->allowed_departments);
                }
                return false;

            case 'private':
                // 检查用户是否在允许的用户列表中
                if ($this->allowed_users && is_array($this->allowed_users)) {
                    return in_array($user->name, $this->allowed_users) || in_array($user->id, $this->allowed_users);
                }
                return false;

            default:
                // 兼容旧的 is_private 字段
                if ($this->is_private) {
                    return false;
                }
                return true;
        }
    }
}
