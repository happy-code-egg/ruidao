<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // 检查用户是否已登录
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'code' => 401,
                'msg' => '请先登录',
                'data' => null
            ], 401);
        }

        $user = Auth::guard('sanctum')->user();

        // 如果没有指定权限，只检查登录状态
        if (!$permission) {
            return $next($request);
        }

        // 检查用户是否有指定权限
        if (!$this->hasPermission($user, $permission)) {
            return response()->json([
                'code' => 403,
                'msg' => '无权限访问',
                'data' => null
            ], 403);
        }

        return $next($request);
    }

    /**
     * 检查用户是否有指定权限
     */
    private function hasPermission($user, $permissionCode)
    {
        // 管理员用户（type=1）拥有所有权限
        if ($user->type == 1) {
            return true;
        }

        // 加载用户角色和权限
        $user->load(['roles.permissions']);

        // 检查用户的所有角色是否有该权限
        foreach ($user->roles as $role) {
            if ($role->permissions->contains('permission_code', $permissionCode)) {
                return true;
            }
        }

        return false;
    }
}
