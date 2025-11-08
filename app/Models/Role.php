<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 角色模型
 * 定义系统中的角色，包含角色名称、代码和描述等信息
 */
class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'role_code',   // 角色编码
        'role_name',   // 角色名称
        'description', // 角色描述
        'created_by',  // 创建人ID
        'updated_by',  // 更新人ID
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

        /**
     * 格式化创建时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化更新时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化删除时间
     * 将时间戳转换为 Y-m-d H:i:s 格式，如果值为null则返回null
     * @param mixed $value 原始时间值
     * @return string|null 格式化的时间字符串或null
     */
    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取角色关联的用户
     * 多对多关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    /**
     * 获取角色关联的权限
     * 多对多关联 Permission 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * 获取创建人
     * 通过 created_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     * 通过 updated_by 字段关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }



    /**
     * 检查角色是否有指定权限
     * 根据权限编码判断角色是否拥有该权限
     * @param string $permissionCode 权限编码
     * @return boolean 是否拥有该权限
     */
    public function hasPermission($permissionCode)
    {
        return $this->permissions()->where('permission_code', $permissionCode)->exists();
    }

    /**
     * 分配权限给角色
     * 将指定权限添加到角色中，但不删除已有的权限
     * @param integer $permissionId 权限ID
     * @return array 受影响的权限ID数组
     */
    public function assignPermission($permissionId)
    {
        return $this->permissions()->syncWithoutDetaching([$permissionId]);
    }

    /**
     * 移除角色权限
     * 从角色中移除指定的权限
     * @param integer $permissionId 权限ID
     * @return integer 受影响的记录数
     */
    public function removePermission($permissionId)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * 同步角色权限
     * 更新角色的权限，添加新权限并移除不在数组中的现有权限
     * @param array $permissionIds 权限ID数组
     * @return array 同步结果数组
     */
    public function syncPermissions($permissionIds)
    {
        return $this->permissions()->sync($permissionIds);
    }
}
