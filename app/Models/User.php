<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 用户模型
 * 代表系统中的用户，包含用户基础信息、权限角色、部门关联等
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',        // 用户名
        'password',        // 密码
        'real_name',       // 真实姓名
        'email',           // 邮箱
        'phone',           // 手机号码
        'avatar_url',      // 头像URL
        'department_id',   // 部门ID
        'position',        // 职位
        'employee_no',     // 员工编号
        'status',          // 状态（1:启用, 0:禁用）
        'last_login_time', // 最后登录时间
        'last_login_ip',   // 最后登录IP
        'created_by',      // 创建人ID
        'updated_by',      // 更新人ID
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'department_id' => 'integer',
        'status' => 'integer',
        'last_login_time' => 'datetime',
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
     * 获取用户名称
     * 用于兼容性处理，返回用户的真实姓名
     * @return string 用户真实姓名
     */
    public function getNameAttribute()
    {
        return $this->real_name;
    }

    /**
     * 获取分配给用户的处理事项
     * 一对多关联 CaseProcess 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedProcesses()
    {
        return $this->hasMany(CaseProcess::class, 'assigned_to');
    }

    /**
     * 获取用户关联的角色
     * 多对多关联 Role 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * 获取用户所属部门
     * 通过 department_id 字段关联 Department 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * 获取创建人
     * 通过 created_by 字段自关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     * 通过 updated_by 字段自关联 User 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取用户管理的部门
     * 一对多关联 Department 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * 获取用户负责的客户
     * 一对多关联 Customer 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'business_person_id');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 检查用户是否有指定权限
     * 通过用户的角色检查是否拥有指定权限
     * @param string $permissionCode 权限编码
     * @return boolean 是否拥有该权限
     */
    public function hasPermission($permissionCode)
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionCode) {
            $query->where('permission_code', $permissionCode);
        })->exists();
    }

    /**
     * 检查用户是否有指定角色
     * 根据角色编码判断用户是否拥有该角色
     * @param string $roleCode 角色编码
     * @return boolean 是否拥有该角色
     */
    public function hasRole($roleCode)
    {
        return $this->roles()->where('role_code', $roleCode)->exists();
    }
    
}
