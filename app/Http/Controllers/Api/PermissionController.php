<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * 获取权限列表（树形结构）
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
     * 获取权限详情
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
     * 创建权限
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
     * 更新权限
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
     * 删除权限
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
     * 获取权限类型选项
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
     * 获取上级权限选项（返回菜单和页面类型的权限）
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
     * 构建树形结构
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
     * 构建选择树（用于上级权限选择）
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
     * 检查是否为子权限
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
     * 获取所有子权限ID
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
