<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * 获取角色列表
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
     * 获取角色详情
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
     * 创建角色
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
     * 更新角色
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
     * 删除角色
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
     * 获取角色权限
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
     * 分配权限给角色
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
     * 获取所有角色（用于下拉选择）
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
     * 构建权限树（用于权限分配）
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
