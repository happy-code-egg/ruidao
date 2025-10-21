<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * 获取用户列表
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['department', 'roles']);

            // 搜索条件
            if ($request->has('keyword') && !empty(trim($request->keyword))) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('username', 'like', "%{$keyword}%")
                      ->orWhere('real_name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            }

            if ($request->has('username') && $request->username !== '' && $request->username !== null) {
                $query->where('username', 'like', "%{$request->username}%");
            }

            if ($request->has('real_name') && $request->real_name !== '' && $request->real_name !== null) {
                $query->where('real_name', 'like', "%{$request->real_name}%");
            }

            // 部门筛选
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('department_id', $request->department_id);
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 先获取总数
            $total = $query->count();

            // 再获取分页数据 - 使用稳定排序避免重复数据
            $users = $query->orderBy('created_at', 'desc')
                          ->orderBy('id', 'desc')  // 添加主键作为第二排序条件确保排序稳定
                          ->offset(($page - 1) * $limit)
                          ->limit($limit)
                          ->get();

            $data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'real_name' => $user->real_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->department_name,
                    ] : null,
                    'position' => $user->position,
                    'employee_no' => $user->employee_no,
                    'status' => $user->status,
                    'status_text' => $user->status_text,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->role_name,
                            'code' => $role->role_code,
                        ];
                    }),
                    'last_login_time' => $user->last_login_time,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return json_page($data, $total, '获取成功');
        } catch (\Exception $e) {
            return json_fail('获取用户列表失败');
        }
    }

    /**
     * 获取用户详情
     */
    public function show($id)
    {
        try {
            $user = User::with(['department', 'roles'])->find($id);

            if (!$user) {
                return json_fail('用户不存在');
            }

            return json_success('获取成功', [
                'id' => $user->id,
                'username' => $user->username,
                'real_name' => $user->real_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'department' => $user->department ? [
                    'id' => $user->department->id,
                    'name' => $user->department->department_name,
                ] : null,
                'position' => $user->position,
                'employee_no' => $user->employee_no,
                'status' => $user->status,
                'status_text' => $user->status_text,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->role_name,
                        'code' => $role->role_code,
                    ];
                }),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        } catch (\Exception $e) {
            return json_fail('获取用户详情失败：' . $e->getMessage());
        }
    }

    /**
     * 创建用户
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'real_name' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:50',
            'employee_no' => 'nullable|string|max:50|unique:users,employee_no',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ], [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码长度不能少于6位',
            'real_name.required' => '真实姓名不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'employee_no.unique' => '员工编号已存在',
            'role_ids.required' => '请选择用户角色',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            // 创建用户
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'real_name' => $request->real_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'position' => $request->position,
                'employee_no' => $request->employee_no,
                'status' => 1,
                'created_by' => $request->user()->id,
            ]);

            // 分配角色
            $user->roles()->sync($request->role_ids);

            DB::commit();

            // 记录创建成功日志
            $this->log(1, "创建用户：{$user->real_name}({$user->username})", [
                'title' => '用户管理',
                'result' => json_encode(['user_id' => $user->id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('用户创建成功');
        } catch (\Exception $e) {
            DB::rollBack();

            // 记录创建失败日志
            $this->log(8, "创建用户失败：{$e->getMessage()}", [
                'title' => '用户管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);

            return json_fail('用户创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新用户
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return json_fail('用户不存在');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'real_name' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:50',
            'employee_no' => 'nullable|string|max:50|unique:users,employee_no,' . $id,
            'status' => 'required|in:0,1',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ], [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'real_name.required' => '真实姓名不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'employee_no.unique' => '员工编号已存在',
            'status.required' => '请选择用户状态',
            'role_ids.required' => '请选择用户角色',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            // 更新用户信息
            $user->update([
                'username' => $request->username,
                'real_name' => $request->real_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
                'position' => $request->position,
                'employee_no' => $request->employee_no,
                'status' => $request->status,
                'updated_by' => $request->user()->id,
            ]);

            // 更新角色
            $user->roles()->sync($request->role_ids);

            DB::commit();

            return json_success('用户更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return json_fail('用户更新失败');
        }
    }

    /**
     * 删除用户
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            // 记录用户不存在日志
            $this->log(8, "删除用户失败：用户不存在 (ID: $id)", [
                'title' => '用户管理',
                'error' => '用户不存在',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('用户不存在');
        }

        // 不能删除自己
        if ($user->id == $request->user()->id) {
            // 记录删除失败日志
            $this->log(8, "删除用户失败：不能删除自己", [
                'title' => '用户管理',
                'error' => '不能删除自己',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('不能删除自己');
        }

        try {
            $userName = $user->real_name ?: $user->username;
            $user->delete();

            // 记录删除成功日志
            $this->log(3, "删除用户：{$userName}", [
                'title' => '用户管理',
                'result' => json_encode(['deleted_user_id' => $id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('用户删除成功');
        } catch (\Exception $e) {
            // 记录删除失败日志
            $this->log(8, "删除用户失败：{$e->getMessage()}", [
                'title' => '用户管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('用户删除失败');
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return json_fail('用户不存在');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ], [
            'password.required' => '密码不能为空',
            'password.min' => '密码长度不能少于6位',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'updated_by' => $request->user()->id,
            ]);

            return json_success('密码重置成功');
        } catch (\Exception $e) {
            return json_fail('密码重置失败');
        }
    }

    /**
     * 获取部门列表（用于下拉选择）
     */
    public function getDepartments()
    {
        try {
            $departments = Department::where('status', 1)
                ->orderBy('sort_order')
                ->get(['id', 'department_name as name']);

            return json_success('获取成功', $departments);
        } catch (\Exception $e) {
            return json_fail('获取部门列表失败');
        }
    }

    /**
     * 获取角色列表（用于下拉选择）
     */
    public function getRoles()
    {
        try {
            $roles = Role::where('status', 1)
                ->orderBy('id')
                ->get(['id', 'role_name as name', 'role_code as code']);

            return json_success('获取成功', $roles);
        } catch (\Exception $e) {
            return json_fail('获取角色列表失败');
        }
    }

    /**
     * 获取用户角色
     */
    public function getUserRoles($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return json_fail('用户不存在');
            }

            $userRoles = $user->roles()->get(['id', 'role_name', 'role_code']);
            $allRoles = Role::where('status', 1)->get(['id', 'role_name', 'role_code']);

            return json_success('获取成功', [
                'user_roles' => $userRoles,
                'all_roles' => $allRoles,
                'user_role_ids' => $userRoles->pluck('id')->toArray(),
            ]);
        } catch (\Exception $e) {
            return json_fail('获取用户角色失败');
        }
    }

    /**
     * 分配角色给用户
     */
    public function assignRoles(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return json_fail('用户不存在');
        }

        $validator = Validator::make($request->all(), [
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ], [
            'role_ids.required' => '请选择角色',
            'role_ids.array' => '角色参数格式错误',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $user->roles()->sync($request->role_ids);
            return json_success('角色分配成功');
        } catch (\Exception $e) {
            return json_fail('角色分配失败');
        }
    }

    /**
     * 批量删除用户
     */
    public function batchDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ], [
            'ids.required' => '请选择要删除的用户',
            'ids.array' => '参数格式错误',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        // 检查是否包含当前用户
        if (in_array($request->user()->id, $request->ids)) {
            return json_fail('不能删除自己');
        }

        try {
            User::whereIn('id', $request->ids)->delete();
            return json_success('批量删除成功');
        } catch (\Exception $e) {
            return json_fail('批量删除失败');
        }
    }

    /**
     * 启用/禁用用户
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return json_fail('用户不存在');
        }

        // 不能禁用自己
        if ($user->id == $request->user()->id) {
            return json_fail('不能禁用自己');
        }

        try {
            $newStatus = $user->status == 1 ? 0 : 1;
            $user->update([
                'status' => $newStatus,
                'updated_by' => $request->user()->id,
            ]);

            $statusText = $newStatus == 1 ? '启用' : '禁用';
            return json_success("用户{$statusText}成功");
        } catch (\Exception $e) {
            return json_fail('状态更新失败');
        }
    }
}
