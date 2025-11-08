<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文件描述模型
 * 用于管理系统中不同类型文件的详细描述信息，包括适用场景、权限配置等
 */
class FileDescriptions extends Model
{
    protected $table = 'file_descriptions';

    protected $fillable = [
        'case_type',            // 案件类型
        'country',              // 国家
        'file_category_major',  // 文件主分类
        'file_category_minor',  // 文件子分类
        'file_name',            // 文件名称
        'file_name_en',         // 文件英文名称
        'file_code',            // 文件编码
        'internal_code',        // 内部编码
        'sort_order',           // 排序顺序
        'file_description',     // 文件描述
        'authorized_client',    // 授权客户
        'authorized_role',      // 授权角色
        'is_valid',             // 是否有效
        'updated_by',           // 更新人ID
        'created_by'            // 创建人ID
    ];

    protected $casts = [
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 格式化更新时间
     * 将更新时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化创建时间
     * 将创建时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 获取状态文本
     * 将 is_valid 字段值转换为中文文本
     * @return string 状态文本（"是"或"否"）
     */
    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    /**
     * 作用域：有效状态
     * 用于查询 is_valid=true 的有效文件描述
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * 作用域：按排序
     * 按照 sort_order 和 id 字段进行排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 转换角色ID为角色名称
     * 兼容存储ID或名称的情况，自动检测并返回格式化的角色名称列表
     * @param mixed $roleData 角色数据（字符串、数组或单个值）
     * @return string 格式化后的角色名称字符串（逗号分隔）
     */
    private function convertRoleIdsToNames($roleData)
    {
        if (empty($roleData)) {
            return '';
        }

        // 如果是逗号分隔的字符串，转换为数组
        if (is_string($roleData)) {
            $roleArray = array_filter(array_map('trim', explode(',', $roleData)));
        } else {
            $roleArray = is_array($roleData) ? $roleData : [$roleData];
        }

        if (empty($roleArray)) {
            return '';
        }

        // 检查第一个元素是否为数字ID
        $firstItem = $roleArray[0];
        if (is_numeric($firstItem)) {
            // 如果是数字，按ID查询角色名称
            $roleIds = array_map('intval', $roleArray);
            $roles = Role::whereIn('id', $roleIds)->pluck('role_name')->toArray();
            return implode(', ', $roles);
        } else {
            // 如果不是数字，假设存储的就是角色名称，直接返回
            // 但仍需验证这些角色名称是否存在
            $validRoles = Role::whereIn('role_name', $roleArray)->pluck('role_name')->toArray();
            return implode(', ', $validRoles);
        }
    }

    /**
     * 处理API响应格式
     * 自定义模型数据转换为数组的格式，包含驼峰命名的键、关联用户信息和角色名称
     * @return array 格式化后的数组数据
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'caseType' => $this->case_type,
            'country' => $this->country,
            'fileCategoryMajor' => $this->file_category_major,
            'fileCategoryMinor' => $this->file_category_minor,
            'fileName' => $this->file_name,
            'fileNameEn' => $this->file_name_en,
            'fileCode' => $this->file_code,
            'internalCode' => $this->internal_code,
            'sortOrder' => $this->sort_order,
            'fileDescription' => $this->file_description,
            'authorizedClient' => $this->authorized_client,
            'authorizedRole' => $this->authorized_role,
            'authorizedRoleNames' => $this->convertRoleIdsToNames($this->authorized_role),
            'isValid' => $this->is_valid,
            'updatedBy' => $this->updater->real_name ?? '',
            'updatedAt' => $this->updated_at,
            'createdBy' => $this->creator->real_name ?? '',
            'createdAt' => $this->created_at
        ];
    }
}
