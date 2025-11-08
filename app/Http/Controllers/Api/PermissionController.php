<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 权限控制器
 * 
 * 功能描述：负责系统权限的增删改查和树形结构管理
 * 
 * 主要功能：
 * - 获取权限列表（树形结构）
 * - 获取权限详情
 * - 创建新权限
 * - 更新权限信息
 * - 删除权限
 * - 获取权限类型选项
 * - 获取上级权限选项
 * - 构建权限树形结构
 * - 构建权限选择树
 * - 检查权限层级关系
 * - 获取权限的所有子权限ID
 */
class PermissionController extends Controller
{
    /**
     * 获取权限列表（树形结构） index
     * 
     * 功能描述：获取权限列表，支持关键词搜索和权限类型筛选，返回树形结构
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - keyword (string, optional): 搜索关键词，支持权限名称和权限编码
     *   - permission_type (int, optional): 权限类型筛选
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (array): 权限树形结构数据
     *   - id (int): 权限ID
     *   - permission_code (string): 权限编码
     *   - permission_name (string): 权限名称
     *   - parent_id (int): 父级权限ID
     *   - permission_type (int): 权限类型
     *   - permission_type_text (string): 权限类型文本
     *   - resource_url (string): 资源URL
     *   - sort_order (int): 排序
     *   - children_count (int): 子权限数量
     *   - roles_count (int): 关联角色数量
     *   - created_at (string): 创建时间
     *   - children (array): 子权限列表
     */
    public function index(Request $request)
    {
        try {
            $query = Permission::query();

            // 搜索条件
            if ($request->has('keyword') && !empty(trim($request->keyword))) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('permission_name', 'like', "%{$keyword}%")
                      ->orWhere('permission_code', 'like', "%{$keyword}%");
                });
            }

            // 权限类型筛选
            if ($request->has('permission_type') && $request->permission_type !== '' && $request->permission_type !== null) {
                $query->where('permission_type', $request->permission_type);
            }



            $permissions = $query->orderBy('sort_order')->get();

            // 构建树形结构
            $tree = $this->buildTree($permissions);

            // 记录查询日志
            $this->log(0, '查看权限列表', [
                'title' => '权限管理',
                'result' => json_encode(['count' => count($tree)]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('获取成功', $tree);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '查看权限列表失败：' . $e->getMessage(), [
                'title' => '权限管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取权限列表失败');
        }
    }

    /**
     * 获取权限详情 show
     * 
     * 功能描述：根据ID获取权限的详细信息，包括关联的父级权限、子权限和角色
     * 
     * 传入参数：
     * - id (int): 权限ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 权限详情数据
     *   - id (int): 权限ID
     *   - permission_code (string): 权限编码
     *   - permission_name (string): 权限名称
     *   - parent_id (int): 父级权限ID
     *   - permission_type (int): 权限类型
     *   - permission_type_text (string): 权限类型文本
     *   - resource_url (string): 资源URL
     *   - sort_order (int): 排序
     *   - parent (object): 父级权限信息
     *     - id (int): 父级权限ID
     *     - permission_name (string): 父级权限名称
     *   - children_count (int): 子权限数量
     *   - roles_count (int): 关联角色数量
     *   - created_at (string): 创建时间
     */
    public function show($id)
    {
        try {
            $permission = Permission::with(['parent', 'children', 'roles'])
                ->find($id);

            if (!$permission) {
                // 记录查询失败日志
                $this->log(8, "查看权限详情失败：权限不存在 (ID: $id)", [
                    'title' => '权限管理',
                    'error' => '权限不存在',
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]);
                return json_fail('权限不存在');
            }

            // 记录查询日志
            $this->log(0, "查看权限详情：{$permission->permission_name}", [
                'title' => '权限管理',
                'result' => json_encode(['permission_id' => $id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('获取成功', [
                'id' => $permission->id,
                'permission_code' => $permission->permission_code,
                'permission_name' => $permission->permission_name,
                'parent_id' => $permission->parent_id,
                'permission_type' => $permission->permission_type,
                'permission_type_text' => $permission->type_text,
                'resource_url' => $permission->resource_url,
                'sort_order' => $permission->sort_order,
                'parent' => $permission->parent ? [
                    'id' => $permission->parent->id,
                    'permission_name' => $permission->parent->permission_name,
                ] : null,
                'children_count' => $permission->children->count(),
                'roles_count' => $permission->roles->count(),
                'created_at' => $permission->created_at,
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, "查看权限详情失败：{$e->getMessage()}", [
                'title' => '权限管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取权限详情失败');
        }
    }

    /**
     * 创建权限 store
     * 
     * 功能描述：创建新的权限记录
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - permission_code (string): 权限编码，必填，最大100字符，必须唯一
     *   - permission_name (string): 权限名称，必填，最大200字符
     *   - parent_id (int, optional): 上级权限ID，可为空，最小值0
     *   - permission_type (int): 权限类型，必填，值为1-4
     *   - resource_url (string, optional): 资源URL，最大500字符
     *   - sort_order (int, optional): 排序，最小值0
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (object): 创建的权限对象
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission_code' => 'required|string|max:100|unique:permissions,permission_code',
            'permission_name' => 'required|string|max:200',
            'parent_id' => 'nullable|integer|min:0',
            'permission_type' => 'required|in:1,2,3,4',
            'resource_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'permission_code.required' => '权限编码不能为空',
            'permission_code.unique' => '权限编码已存在',
            'permission_name.required' => '权限名称不能为空',
            'parent_id.exists' => '上级权限不存在',
            'permission_type.required' => '请选择权限类型',
            'permission_type.in' => '权限类型无效',
        ]);

        if ($validator->fails()) {
            // 记录参数验证失败日志
            $this->log(8, "创建权限失败：参数错误 - {$validator->errors()->first()}", [
                'title' => '权限管理',
                'error' => $validator->errors()->first(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('参数错误', $validator->errors()->first());
        }

        // 验证parent_id
        if ($request->parent_id && $request->parent_id > 0) {
            $parentExists = Permission::where('id', $request->parent_id)->exists();
            if (!$parentExists) {
                // 记录验证失败日志
                $this->log(8, "创建权限失败：上级权限不存在 (parent_id: {$request->parent_id})", [
                    'title' => '权限管理',
                    'error' => '上级权限不存在',
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]);
                return json_fail('上级权限不存在');
            }
        }

        try {
            $permission = Permission::create([
                'permission_code' => $request->permission_code,
                'permission_name' => $request->permission_name,
                'parent_id' => $request->parent_id ?: 0,
                'permission_type' => $request->permission_type,
                'resource_url' => $request->resource_url,
                'sort_order' => $request->sort_order ?: 0,
            ]);

            // 记录创建成功日志
            $this->log(1, "创建权限：{$permission->permission_name}", [
                'title' => '权限管理',
                'result' => json_encode(['permission_id' => $permission->id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('权限创建成功', $permission);
        } catch (\Exception $e) {
            // 记录创建失败日志
            $this->log(8, "创建权限失败：{$e->getMessage()}", [
                'title' => '权限管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('权限创建失败');
        }
    }

    /**
     * 更新权限 update
     * 
     * 功能描述：更新指定ID的权限信息
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     *   - permission_code (string): 权限编码，必填，最大100字符，必须唯一
     *   - permission_name (string): 权限名称，必填，最大200字符
     *   - parent_id (int, optional): 上级权限ID，可为空，最小值0
     *   - permission_type (int): 权限类型，必填，值为1-4
     *   - resource_url (string, optional): 资源URL，最大500字符
     *   - sort_order (int, optional): 排序，最小值0
     * - id (int): 权限ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            // 记录权限不存在日志
            $this->log(8, "更新权限失败：权限不存在 (ID: $id)", [
                'title' => '权限管理',
                'error' => '权限不存在',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('权限不存在');
        }

        $validator = Validator::make($request->all(), [
            'permission_code' => 'required|string|max:100|unique:permissions,permission_code,' . $id,
            'permission_name' => 'required|string|max:200',
            'parent_id' => 'nullable|integer|min:0',
            'permission_type' => 'required|in:1,2,3,4',
            'resource_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0'
        ], [
            'permission_code.required' => '权限编码不能为空',
            'permission_code.unique' => '权限编码已存在',
            'permission_name.required' => '权限名称不能为空',
            'parent_id.exists' => '上级权限不存在',
            'permission_type.required' => '请选择权限类型',
            'permission_type.in' => '权限类型无效'
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        // 验证parent_id
        if ($request->parent_id && $request->parent_id > 0) {
            $parentExists = Permission::where('id', $request->parent_id)->exists();
            if (!$parentExists) {
                return json_fail('上级权限不存在');
            }
        }

        // 检查是否设置自己为上级权限
        if ($request->parent_id == $id) {
            return json_fail('不能设置自己为上级权限');
        }

        // 检查是否设置子权限为上级权限
        if ($request->parent_id && $this->isChildPermission($id, $request->parent_id)) {
            return json_fail('不能设置子权限为上级权限');
        }

        try {
            $oldData = $permission->toArray();

            $permission->update([
                'permission_code' => $request->permission_code,
                'permission_name' => $request->permission_name,
                'parent_id' => $request->parent_id ?: 0,
                'permission_type' => $request->permission_type,
                'resource_url' => $request->resource_url,
                'sort_order' => $request->sort_order ?: 0,
            ]);

            // 记录更新成功日志
            $this->log(2, "更新权限：{$permission->permission_name}", [
                'title' => '权限管理',
                'result' => json_encode([
                    'permission_id' => $permission->id,
                    'changes' => array_diff_assoc($request->only([
                        'permission_code', 'permission_name', 'parent_id',
                        'permission_type', 'resource_url', 'sort_order'
                    ]), $oldData)
                ]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('权限更新成功');
        } catch (\Exception $e) {
            // 记录更新失败日志
            $this->log(8, "更新权限失败：{$e->getMessage()}", [
                'title' => '权限管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('权限更新失败');
        }
    }

    /**
     * 删除权限 destroy
     * 
     * 功能描述：删除指定ID的权限，不允许删除有子权限或被角色使用的权限
     * 
     * 传入参数：
     * - request (Request): HTTP请求对象
     * - id (int): 权限ID
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     */
    public function destroy(Request $request, $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            // 记录权限不存在日志
            $this->log(8, "删除权限失败：权限不存在 (ID: $id)", [
                'title' => '权限管理',
                'error' => '权限不存在',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('权限不存在');
        }

        // 检查是否有子权限
        if ($permission->children()->count() > 0) {
            // 记录删除失败日志
            $this->log(8, "删除权限失败：{$permission->permission_name} 下还有子权限", [
                'title' => '权限管理',
                'error' => '该权限下还有子权限，无法删除',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('该权限下还有子权限，无法删除');
        }

        // 检查是否有角色在使用
        if ($permission->roles()->count() > 0) {
            // 记录删除失败日志
            $this->log(8, "删除权限失败：{$permission->permission_name} 正在被角色使用", [
                'title' => '权限管理',
                'error' => '该权限正在被角色使用，无法删除',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('该权限正在被角色使用，无法删除');
        }

        try {
            $permissionName = $permission->permission_name;
            $permission->delete();

            // 记录删除成功日志
            $this->log(3, "删除权限：{$permissionName}", [
                'title' => '权限管理',
                'result' => json_encode(['deleted_permission_id' => $id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('权限删除成功');
        } catch (\Exception $e) {
            // 记录删除失败日志
            $this->log(8, "删除权限失败：{$e->getMessage()}", [
                'title' => '权限管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('权限删除失败');
        }
    }

    /**
     * 获取权限类型选项 getTypes
     * 
     * 功能描述：获取系统支持的权限类型选项
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (array): 权限类型选项列表
     *   - value (int): 权限类型值
     *   - label (string): 权限类型名称
     */
    public function getTypes()
    {
        $types = [
            ['value' => 1, 'label' => '菜单'],
            ['value' => 2, 'label' => '页面'],
            ['value' => 3, 'label' => '按钮'],
            ['value' => 4, 'label' => '接口'],
        ];

        return json_success('获取成功', $types);
    }

    /**
     * 获取上级权限选项（返回菜单和页面类型的权限） getParentOptions
     * 
     * 功能描述：获取可用作上级权限的选项，仅返回菜单和页面类型的权限
     * 
     * 输出参数：
     * - code (int): 状态码，0表示成功
     * - msg (string): 操作结果消息
     * - data (array): 上级权限选项树形结构
     *   - value (int): 权限ID
     *   - label (string): 权限名称（带层级缩进）
     *   - children (array): 子权限选项列表
     */
    public function getParentOptions()
    {
        try {
            $permissions = Permission::whereIn('permission_type', [1, 2])
                ->orderBy('sort_order')
                ->get(['id', 'permission_name', 'parent_id']);

            $tree = $this->buildSelectTree($permissions);

            return json_success('获取成功', $tree);
        } catch (\Exception $e) {
            return json_fail('获取上级权限选项失败');
        }
    }

    /**
     * 构建树形结构 buildTree
     * 
     * 功能描述：将权限集合构建为树形结构
     * 
     * 传入参数：
     * - permissions (Collection): 权限集合
     * - parentId (int): 父级ID，默认为0
     * 
     * 输出参数：
     * - array: 构建好的权限树形结构数组
     */
    private function buildTree($permissions, $parentId = 0)
    {
        $tree = [];
        
        foreach ($permissions as $permission) {
            if ($permission->parent_id == $parentId) {
                $node = [
                    'id' => $permission->id,
                    'permission_code' => $permission->permission_code,
                    'permission_name' => $permission->permission_name,
                    'parent_id' => $permission->parent_id,
                    'permission_type' => $permission->permission_type,
                    'permission_type_text' => $permission->type_text,
                    'resource_url' => $permission->resource_url,
                    'sort_order' => $permission->sort_order,
                    'children_count' => $permission->children()->count(),
                    'roles_count' => $permission->roles()->count(),
                    'created_at' => $permission->created_at,
                    'children' => $this->buildTree($permissions, $permission->id),
                ];
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    /**
     * 构建选择树（用于上级权限选择） buildSelectTree
     * 
     * 功能描述：将权限集合构建为带层级缩进的选择树结构
     * 
     * 传入参数：
     * - permissions (Collection): 权限集合
     * - parentId (int): 父级ID，默认为0
     * - level (int): 层级，默认为0
     * 
     * 输出参数：
     * - array: 构建好的选择树结构数组
     */
    private function buildSelectTree($permissions, $parentId = 0, $level = 0)
    {
        $tree = [];
        
        foreach ($permissions as $permission) {
            if ($permission->parent_id == $parentId) {
                $node = [
                    'value' => $permission->id,
                    'label' => str_repeat('　', $level) . $permission->permission_name,
                    'children' => $this->buildSelectTree($permissions, $permission->id, $level + 1),
                ];
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    /**
     * 检查是否为子权限 isChildPermission
     * 
     * 功能描述：检查指定权限是否为另一权限的子权限
     * 
     * 传入参数：
     * - parentId (int): 父权限ID
     * - childId (int): 子权限ID
     * 
     * 输出参数：
     * - bool: 是否为子权限
     */
    private function isChildPermission($parentId, $childId)
    {
        $child = Permission::find($childId);
        if (!$child) {
            return false;
        }

        // 递归检查所有子权限
        $childIds = $this->getAllChildrenIds($parentId);
        return in_array($childId, $childIds);
    }

    /**
     * 获取所有子权限ID getAllChildrenIds
     * 
     * 功能描述：获取指定权限的所有子权限ID
     * 
     * 传入参数：
     * - parentId (int): 父权限ID
     * 
     * 输出参数：
     * - array: 所有子权限ID数组
     */
    private function getAllChildrenIds($parentId)
    {
        $ids = [];
        $children = Permission::where('parent_id', $parentId)->get();
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildrenIds($child->id));
        }
        
        return $ids;
    }
}
