<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 部门模型
 * 用于管理部门组织架构信息
 */
class Department extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定数据库表名
    protected $table = 'departments';

    // 定义可批量赋值的字段
    protected $fillable = [
        'department_code',  // 部门编码
        'department_name',  // 部门名称
        'parent_id',        // 父部门ID
        'level_path',       // 层级路径
        'manager_id',       // 部门负责人ID
        'description',      // 部门描述
        'sort_order',       // 排序顺序
        'created_by',       // 创建人ID
        'updated_by',       // 更新人ID
    ];

    // 定义字段类型转换
    protected $casts = [
        'parent_id' => 'integer',      // 父部门ID转为整数类型
        'manager_id' => 'integer',     // 部门负责人ID转为整数类型
        'sort_order' => 'integer',     // 排序顺序转为整数类型
        'created_by' => 'integer',     // 创建人ID转为整数类型
        'updated_by' => 'integer',     // 更新人ID转为整数类型
        'created_at' => 'datetime',    // 创建时间转为日期时间类型
        'updated_at' => 'datetime',    // 更新时间转为日期时间类型
        'deleted_at' => 'datetime',    // 删除时间转为日期时间类型
    ];

    /**
     * 格式化 created_at 时间
     * 将创建时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     * 将更新时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 deleted_at 时间
     * 将删除时间格式化为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string|null 格式化后的时间字符串或null
     */
    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d H:i:s') : null;
    }

    /**
     * 关联部门负责人信息
     * 通过 `manager_id` 字段关联 `User` 模型
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * 关联父部门信息
     * 通过 `parent_id` 字段关联 `Department` 模型
     */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * 关联子部门信息
     * 通过 `parent_id` 字段关联 `Department` 模型
     */
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * 关联部门用户信息
     * 通过 `department_id` 字段关联 `User` 模型
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * 关联创建人信息
     * 通过 `created_by` 字段关联 `User` 模型
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人信息
     * 通过 `updated_by` 字段关联 `User` 模型
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 递归获取所有子部门ID
     * 包括当前部门及其所有层级的子部门ID
     * @return array 所有子部门ID数组
     */
    public function getAllChildrenIds()
    {
        $ids = [$this->id];           // 将当前部门ID加入数组
        $children = $this->children;  // 获取直接子部门

        // 递归获取所有子部门的ID
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }
}
