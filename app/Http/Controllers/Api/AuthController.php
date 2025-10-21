<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => '用户名不能为空',
            'username.max' => '用户名长度不能超过50个字符',
            'password.required' => '密码不能为空',
            'password.min' => '密码长度不能少于6位',
        ]);

        if ($validator->fails()) {
            // 记录参数验证失败日志
            $this->log(8, "登录失败：参数错误 - {$validator->errors()->first()}", [
                'title' => '用户认证',
                'error' => $validator->errors()->first(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            // 查找用户
            $user = User::where('username', $request->username)
                ->where('status', 1)
                ->with(['department', 'roles.permissions'])
                ->first();

            if (!$user) {
                // 记录用户不存在日志
                $this->log(8, "登录失败：用户不存在 - {$request->username}", [
                    'title' => '用户认证',
                    'error' => '未找到相关用户',
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]);
                return json_fail('未找到相关用户！');
            }

            // 验证密码
            if (!Hash::check($request->password, $user->password)) {
                // 记录密码错误日志
                $this->log(8, "登录失败：密码错误 - {$request->username}", [
                    'title' => '用户认证',
                    'error' => '用户名或密码错误',
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]);
                return json_fail('用户名或密码错误');
            }

            // 创建token
            $token = $user->createToken('ema_auth_token')->plainTextToken;

            // 更新最后登录信息
            $user->update([
                'last_login_time' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // 获取用户权限菜单
            $permissions = $this->getUserPermissions($user);
            $menus = $this->buildMenuTree($permissions);

            // 记录登录日志
            $this->logOperation($user, 'LOGIN', '用户登录', $request);

            return json_success('登录成功', [
                'token' => $token,
                'user' => [
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
                ],
                'permissions' => $permissions->pluck('permission_code')->toArray(),
                'menus' => $menus,
            ]);

        } catch (\Exception $e) {
            Log::error('登录失败: ' . $e->getMessage(), [
                'username' => $request->username,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return json_fail('登录失败，请稍后重试');
        }
    }

    /**
     * 用户登出
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // 记录登出日志
            $this->logOperation($user, 'LOGOUT', '用户登出', $request);

            // 删除当前token
            $request->user()->currentAccessToken()->delete();

            return json_success('退出成功');
        } catch (\Exception $e) {
            Log::error('登出失败: ' . $e->getMessage());
            return json_fail('退出失败');
        }
    }

    /**
     * 获取当前用户信息
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user()->load(['department', 'roles']);

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
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->role_name,
                        'code' => $role->role_code,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return json_fail('获取用户信息失败');
        }
    }

    /**
     * 更新个人信息
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'real_name' => 'required|string|max:50',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'avatar_url' => 'nullable|string|max:255',
        ], [
            'real_name.required' => '真实姓名不能为空',
            'real_name.max' => '真实姓名长度不能超过50个字符',
            'email.email' => '邮箱格式不正确',
            'phone.max' => '手机号长度不能超过20个字符',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $user = $request->user();
            $user->update($request->only(['real_name', 'email', 'phone', 'avatar_url']));

            $this->logOperation($user, 'UPDATE', '更新个人信息', $request);

            return json_success('更新成功');
        } catch (\Exception $e) {
            return json_fail('更新失败');
        }
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'old_password.required' => '原密码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.min' => '新密码长度不能少于6位',
            'new_password.confirmed' => '两次输入的密码不一致',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $user = $request->user();

            // 验证原密码
            if (!Hash::check($request->old_password, $user->password)) {
                return json_fail('原密码错误');
            }

            // 更新密码
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            $this->logOperation($user, 'UPDATE', '修改密码', $request);

            return json_success('密码修改成功');
        } catch (\Exception $e) {
            return json_fail('密码修改失败');
        }
    }

    /**
     * 获取用户权限
     */
    private function getUserPermissions($user)
    {
        $permissions = collect();

        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    /**
     * 构建菜单树
     */
    private function buildMenuTree($permissions)
    {
        $menuPermissions = $permissions->where('permission_type', 1)->sortBy('sort_order');
        $tree = [];

        // 构建顶级菜单
        $topMenus = $menuPermissions->where('parent_id', 0);

        foreach ($topMenus as $menu) {
            $menuItem = [
                'id' => $menu->id,
                'code' => $menu->permission_code,
                'name' => $menu->permission_name,
                'path' => $menu->resource_url,
                'icon' => $menu->icon,
                'children' => $this->buildMenuChildren($menu->id, $menuPermissions),
            ];

            $tree[] = $menuItem;
        }

        return $tree;
    }

    /**
     * 构建子菜单
     */
    private function buildMenuChildren($parentId, $permissions)
    {
        $children = [];
        $childMenus = $permissions->where('parent_id', $parentId);

        foreach ($childMenus as $menu) {
            $menuItem = [
                'id' => $menu->id,
                'code' => $menu->permission_code,
                'name' => $menu->permission_name,
                'path' => $menu->resource_url,
                'icon' => $menu->icon,
                'children' => $this->buildMenuChildren($menu->id, $permissions),
            ];

            $children[] = $menuItem;
        }

        return $children;
    }

    /**
     * 记录操作日志
     */
    private function logOperation($user, $type, $name, $request)
    {
        try {
            // 使用统一的日志系统
            $logType = [
                'LOGIN' => 4,   // 登录操作
                'LOGOUT' => 5,  // 退出操作
                'UPDATE' => 2,  // 修改操作
            ][$type] ?? 0;

            $this->log($logType, $name, [
                'title' => '用户认证',
                'result' => json_encode(['user_id' => $user->id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);
        } catch (\Exception $e) {
            // 记录日志失败也要记录
            $this->log(8, "记录操作日志失败：{$e->getMessage()}", [
                'title' => '系统错误',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
        }
    }
}
