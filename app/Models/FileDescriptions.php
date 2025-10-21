<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileDescriptions extends Model
{
    protected $table = 'file_descriptions';

    protected $fillable = [
        'case_type',
        'country',
        'file_category_major',
        'file_category_minor',
        'file_name',
        'file_name_en',
        'file_code',
        'internal_code',
        'sort_order',
        'file_description',
        'authorized_client',
        'authorized_role',
        'is_valid',
        'updated_by',
        'created_by'
    ];

    protected $casts = [
        'is_valid' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getStatusTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 转换角色ID为角色名称（兼容存储ID或名称的情况）
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
