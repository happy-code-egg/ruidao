<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 功能: 角色管理控制器，提供角色的增删改查、权限获取与分配、
 *       以及角色下拉选项接口。仅补充注释，不修改业务逻辑或日志。
 * 路由前缀: /api
 * 权限限制: 大部分接口受 middleware: permission:system.role 控制；
 *           其中 GET /api/roles/all 为公开测试路由。
 */
class RoleController extends Controller
{
    /**
     * 功能: 获取角色列表（支持关键词搜索与分页）。
     * 接口: GET /api/roles (route: api.roles.index)
     * 请求参数:
     * - keyword string 关键词（按角色名称/编码模糊匹配）
     * - page int 当前页，默认 1
     * - limit int 每页条数，默认 15，最大 100
     * 返回参数:
     * - JSON: {code, message, data}
     * - data.object: 分页数据（通过 json_page 返回）
     * 内部说明:
     * - 关联 creator/updater；统计启用用户数与权限数
     * - 先 count 再 offset/limit 获取分页数据
     */
    public function index(Request $request)
    {
        try {
            $query = Role::with(['creator', 'updater']);

            // 搜索条件
            if ($request->has('keyword') && !empty(trim($request->keyword))) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('role_name', 'like', "%{$keyword}%")
                      ->orWhere('role_code', 'like', "%{$keyword}%");
                });
            }



            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 先获取总数
            $total = $query->count();

            // 再获取分页数据
            $roles = $query->orderBy('created_at', 'desc')
                          ->offset(($page - 1) * $limit)
                          ->limit($limit)
                          ->get();

            $data = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'role_code' => $role->role_code,
                    'role_name' => $role->role_name,
                    'description' => $role->description,
                    'user_count' => $role->users()->where('status', 1)->count(),
                    'permission_count' => $role->permissions()->count(),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ];
            });

            return json_page($data, $total, '获取成功');
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '查看角色列表失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取角色列表失败');
        }
    }

    /**
     * 功能: 获取角色详情，包含权限与关联用户。
     * 接口: GET /api/roles/{id} (route: api.roles.show)
     * 请求参数:
     * - id int 路径参数，角色ID
     * 返回参数:
     * - JSON: {code, message, data}
     * - data.object: 角色详情（含 permissions 与 users），未找到返回失败提示
     */
    public function show($id)
    {
        try {
            $role = Role::with(['permissions', 'users'])->find($id);

            if (!$role) {
                return json_fail('角色不存在');
            }

            return json_success('获取成功', [
                'id' => $role->id,
                'role_code' => $role->role_code,
                'role_name' => $role->role_name,
                'description' => $role->description,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'permission_code' => $permission->permission_code,
                        'permission_name' => $permission->permission_name,
                        'permission_type' => $permission->permission_type,
                        'permission_type_text' => $permission->type_text,
                    ];
                }),
                'users' => $role->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'real_name' => $user->real_name,
                    ];
                }),
                'created_at' => $role->created_at,
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '查看角色详情失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取角色详情失败');
        }
    }

    /**
     * 功能: 创建角色并可选分配权限。
     * 接口: POST /api/roles (route: api.roles.store)
     * 请求参数:
     * - role_code string 必填，唯一，最长 50
     * - role_name string 必填，最长 100
     * - description string 可选，最长 500
     * - permission_ids array<int> 可选，权限ID列表
     * 返回参数:
     * - JSON: {code, message, data}
     * - data.object: 创建后的角色简要信息
     * 内部说明:
     * - 使用事务创建角色，并同步权限关联
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_code' => 'required|string|max:50|unique:roles,role_code',
            'role_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'role_code.required' => '角色编码不能为空',
            'role_code.unique' => '角色编码已存在',
            'role_name.required' => '角色名称不能为空',
            'permission_ids.array' => '权限参数格式错误',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $role = Role::create([
                'role_code' => $request->role_code,
                'role_name' => $request->role_name,
                'description' => $request->description,
                'created_by' => $request->user()->id,
            ]);

            // 分配权限
            if ($request->permission_ids) {
                $role->permissions()->sync($request->permission_ids);
            }

            DB::commit();

            return json_success('角色创建成功', $role);
        } catch (\Exception $e) {
            DB::rollBack();
            // 记录错误日志
            $this->log(8, '角色创建失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('角色创建失败');
        }
    }

    /**
     * 功能: 更新角色基本信息与权限分配。
     * 接口: PUT /api/roles/{id} (route: api.roles.update)
     * 请求参数:
     * - id int 路径参数，角色ID
     * - role_code string 必填，唯一（排除当前ID），最长 50
     * - role_name string 必填，最长 100
     * - description string 可选，最长 500
     * - permission_ids array<int> 可选，权限ID列表（不传表示不变，传空数组表示清空）
     * 返回参数:
     * - JSON: {code, message}
     * 内部说明:
     * - 使用事务更新角色，conditionally 同步权限
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return json_fail('角色不存在');
        }

        $validator = Validator::make($request->all(), [
            'role_code' => 'required|string|max:50|unique:roles,role_code,' . $id,
            'role_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'role_code.required' => '角色编码不能为空',
            'role_code.unique' => '角色编码已存在',
            'role_name.required' => '角色名称不能为空',
            'permission_ids.array' => '权限参数格式错误',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $role->update([
                'role_code' => $request->role_code,
                'role_name' => $request->role_name,
                'description' => $request->description,
                'updated_by' => $request->user()->id,
            ]);

            // 更新权限
            if ($request->has('permission_ids')) {
                $role->permissions()->sync($request->permission_ids ?: []);
            }

            DB::commit();

            return json_success('角色更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            // 记录错误日志
            $this->log(8, '角色更新失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('角色更新失败');
        }
    }

    /**
     * 功能: 删除角色（若有用户使用则禁止删除）。
     * 接口: DELETE /api/roles/{id} (route: api.roles.destroy)
     * 请求参数:
     * - id int 路径参数，角色ID
     * 返回参数:
     * - JSON: {code, message}
     * 内部说明:
     * - 事务中先清理权限关联，再删除角色
     */
    public function destroy(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return json_fail('角色不存在');
        }

        // 检查是否有用户在使用该角色
        if ($role->users()->count() > 0) {
            return json_fail('该角色正在被用户使用，无法删除');
        }

        try {
            DB::beginTransaction();

            // 删除角色权限关联
            $role->permissions()->detach();
            
            // 删除角色
            $role->delete();

            DB::commit();

            return json_success('角色删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            // 记录错误日志
            $this->log(8, '角色删除失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('角色删除失败');
        }
    }

    /**
     * 功能: 获取指定角色的权限树与已勾选权限ID。
     * 接口: GET /api/roles/{id}/permissions (route: api.roles.permissions)
     * 请求参数:
     * - id int 路径参数，角色ID
     * 返回参数:
     * - JSON: {code, message, data}
     * - data.object: {permissions: 权限树, checked_ids: 已选权限ID数组}
     * 内部说明:
     * - 权限树通过 buildPermissionTree 构建（按 parent_id 递归）
     */
    public function getPermissions($id)
    {
        try {
            $role = Role::find($id);
            if (!$role) {
                return json_fail('角色不存在');
            }

            // 获取所有权限（树形结构）
            $allPermissions = Permission::orderBy('sort_order')
                ->get();

            // 获取角色已有的权限ID
            $rolePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

            // 构建权限树
            $permissionTree = $this->buildPermissionTree($allPermissions, $rolePermissionIds);

            return json_success('获取成功', [
                'permissions' => $permissionTree,
                'checked_ids' => $rolePermissionIds,
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '获取角色权限失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取角色权限失败');
        }
    }

    /**
     * 功能: 为角色分配权限（覆盖式同步）。
     * 接口: POST /api/roles/{id}/permissions (route: api.roles.assign.permissions)
     * 请求参数:
     * - id int 路径参数，角色ID
     * - permission_ids array<int> 必填，权限ID列表
     * 返回参数:
     * - JSON: {code, message}
     * 内部说明:
     * - 使用 sync 同步角色权限（替换现有权限集）
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return json_fail('角色不存在');
        }

        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'permission_ids.required' => '请选择权限',
            'permission_ids.array' => '权限参数格式错误',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $role->permissions()->sync($request->permission_ids);
            return json_success('权限分配成功');
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '权限分配失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('权限分配失败');
        }
    }

    /**
     * 功能: 获取所有角色（下拉选项）。
     * 接口: GET /api/roles/all (route: api.roles.all)
     * 请求参数: 无
     * 返回参数:
     * - JSON: {code, message, data}
     * - data.array<object>: [{id, name, code}]
     */
    public function getAllRoles()
    {
        try {
            $roles = Role::orderBy('id')
                ->get(['id', 'role_name as name', 'role_code as code']);

            return json_success('获取成功', $roles);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '获取角色列表失败：' . $e->getMessage(), [
                'title' => '角色管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED,
                'trace' => $e->getTraceAsString(),
            ]);
            return json_fail('获取角色列表失败');
        }
    }

    /**
     * 功能: 构建权限树（递归，根据 parent_id 组织层级）。
     * 调用方: getPermissions
     * 参数说明:
     * - permissions 集合 所有权限记录
     * - checkedIds array<int> 已选中的权限ID
     * - parentId int 当前遍历的父节点ID，默认 0
     * 返回: array 树形结构节点数组
     */
    private function buildPermissionTree($permissions, $checkedIds = [], $parentId = 0)
    {
        $tree = [];
        
        foreach ($permissions as $permission) {
            if ($permission->parent_id == $parentId) {
                $node = [
                    'id' => $permission->id,
                    'label' => $permission->permission_name,
                    'permission_code' => $permission->permission_code,
                    'permission_type' => $permission->permission_type,
                    'permission_type_text' => $permission->type_text,
                    'checked' => in_array($permission->id, $checkedIds),
                    'children' => $this->buildPermissionTree($permissions, $checkedIds, $permission->id),
                ];
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }
}
