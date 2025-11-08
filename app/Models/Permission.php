<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 权限模型
 * 定义系统中的权限点，包含菜单、页面、按钮和API等各类权限
 */
class Permission extends Model
{


    protected $table = 'permissions';

    public $timestamps = true;

    protected $fillable = [
        'permission_code',   // 权限编码
        'permission_name',   // 权限名称
        'parent_id',         // 父权限ID
        'permission_type',   // 权限类型（1:菜单, 2:页面, 3:按钮, 4:接口）
        'resource_url',      // 资源路径
        'sort_order',        // 排序顺序
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'permission_type' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 权限类型常量
     */
    const TYPE_MENU = 1;
    const TYPE_PAGE = 2;
    const TYPE_BUTTON = 3;
    const TYPE_API = 4;

    /**
     * 获取权限关联的角色
     * 多对多关联 Role 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }

    /**
     * 获取父权限
     * 通过 parent_id 字段自关联 Permission 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * 获取子权限
     * 通过 parent_id 字段一对多自关联 Permission 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }



    /**
     * 获取权限类型文本
     * 将 permission_type 字段值转换为对应的中文类型名称
     * @return string 权限类型文本
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_MENU => '菜单',
            self::TYPE_PAGE => '页面',
            self::TYPE_BUTTON => '按钮',
            self::TYPE_API => '接口',
        ];

        return $types[$this->permission_type] ?? '未知';
    }

    /**
     * 递归获取所有子权限ID
     * 递归收集当前权限及其所有子权限的ID
     * @return array 权限ID数组
     */
    public function getAllChildrenIds()
    {
        $ids = [$this->id];
        $children = $this->children;
        
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        
        return $ids;
    }

    /**
     * 获取权限树结构
     * 递归获取指定父权限下的完整权限树
     * @param integer $parentId 父权限ID，默认为0（根节点）
     * @return \Illuminate\Database\Eloquent\Collection 权限树集合
     */
    public static function getTree($parentId = 0)
    {
        $permissions = self::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        foreach ($permissions as $permission) {
            $permission->children = self::getTree($permission->id);
        }

        return $permissions;
    }
}
