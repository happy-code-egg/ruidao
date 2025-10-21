<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'role_code',
        'role_name',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

        /**
     * 格式化 created_at 时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 deleted_at 时间
     */
    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取角色用户
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    /**
     * 获取角色权限
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
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
     * 检查角色是否有指定权限
     */
    public function hasPermission($permissionCode)
    {
        return $this->permissions()->where('permission_code', $permissionCode)->exists();
    }

    /**
     * 分配权限给角色
     */
    public function assignPermission($permissionId)
    {
        return $this->permissions()->syncWithoutDetaching([$permissionId]);
    }

    /**
     * 移除角色权限
     */
    public function removePermission($permissionId)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * 同步角色权限
     */
    public function syncPermissions($permissionIds)
    {
        return $this->permissions()->sync($permissionIds);
    }
}
