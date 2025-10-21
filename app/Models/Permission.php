<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{


    protected $table = 'permissions';

    public $timestamps = true;

    protected $fillable = [
        'permission_code',
        'permission_name',
        'parent_id',
        'permission_type',
        'resource_url',
        'sort_order',
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
     * 获取权限角色
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }

    /**
     * 获取父权限
     */
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * 获取子权限
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }



    /**
     * 权限类型文本
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
