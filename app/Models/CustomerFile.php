<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * 客户文件模型
 * 用于管理客户相关的文件信息和权限控制
 */
class CustomerFile extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'customer_files';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'customer_id',          // 客户ID
        'file_name',            // 文件名
        'file_original_name',   // 文件原始名称
        'file_path',            // 文件存储路径
        'file_type',            // 文件类型
        'file_category',        // 文件分类
        'file_size',            // 文件大小(字节)
        'mime_type',            // MIME类型
        'file_description',     // 文件描述
        'is_private',           // 是否私有
        'permission_type',      // 权限类型
        'allowed_departments',   // 允许访问的部门列表
        'allowed_users',        // 允许访问的用户列表
        'uploaded_by',          // 上传人ID
        'remark',               // 备注
        'created_by',           // 创建人ID
        'updated_by',           // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',             // 客户ID转换为整数
        'file_size' => 'integer',               // 文件大小转换为整数
        'is_private' => 'boolean',              // 是否私有转换为布尔值
        'allowed_departments' => 'array',       // 允许访问的部门列表转换为数组
        'allowed_users' => 'array',             // 允许访问的用户列表转换为数组
        'uploaded_by' => 'integer',             // 上传人ID转换为整数
        'created_by' => 'integer',              // 创建人ID转换为整数
        'updated_by' => 'integer',              // 更新人ID转换为整数
        'created_at' => 'datetime',             // 创建时间转换为日期时间
        'updated_at' => 'datetime',             // 更新时间转换为日期时间
        'deleted_at' => 'datetime',             // 删除时间转换为日期时间
    ];

    /**
     * 文件分类常量
     */
    const CATEGORY_CONTRACT = '合同';        // 合同文件
    const CATEGORY_CERTIFICATE = '证件';     // 证件文件
    const CATEGORY_TECH_DOC = '技术资料';     // 技术资料文件
    const CATEGORY_BUSINESS_DOC = '商务资料';  // 商务资料文件
    const CATEGORY_FINANCE_DOC = '财务资料';   // 财务资料文件
    const CATEGORY_OTHER = '其他';           // 其他文件

    /**
     * 获取客户关联关系
     * 建立与 `Customer` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取上传者关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * 获取创建人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人关联关系
     * 建立与 `User` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取文件大小格式化文本的访问器
     * 将字节大小转换为人类可读的格式(GB/MB/KB/bytes)
     * @return string 格式化后的文件大小文本
     */
    public function getFileSizeTextAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';  // 大于等于1GB时显示GB
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';     // 大于等于1MB时显示MB
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';        // 大于等于1KB时显示KB
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';                              // 大于1字节时显示bytes
        } elseif ($bytes == 1) {
            return $bytes . ' byte';                               // 等于1字节时显示byte
        } else {
            return '0 bytes';                                       // 0字节时显示0 bytes
        }
    }

    /**
     * 获取文件下载URL的访问器
     * 通过 `Storage` 门面生成文件的可访问URL
     * @return string 文件下载URL
     */
    public function getDownloadUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * 获取文件分类选项
     * 返回所有支持的文件分类选项
     * @return array 文件分类选项数组
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
     * 获取文件图标的访问器
     * 根据文件扩展名返回对应的图标类名
     * @return string 图标类名
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

        return $iconMap[$extension] ?? 'el-icon-document';  // 默认返回文档图标
    }

    /**
     * 获取格式化文件大小
     * 调用 `getFileSizeTextAttribute` 访问器获取格式化后的文件大小
     * @return string 格式化后的文件大小文本
     */
    public function getFileSizeFormatted()
    {
        return $this->getFileSizeTextAttribute();
    }

    /**
     * 获取下载URL
     * 调用 `getDownloadUrlAttribute` 访问器获取文件下载URL
     * @return string 文件下载URL
     */
    public function getDownloadUrl()
    {
        return $this->getDownloadUrlAttribute();
    }

    /**
     * 检查是否可以下载文件
     * 根据用户权限和文件权限设置判断用户是否有权下载该文件
     * @return bool 是否可以下载
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
                return true;  // 公共文件所有人都可以下载

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
