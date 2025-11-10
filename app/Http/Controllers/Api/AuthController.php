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

/**
 * 用户认证控制器
 * 负责处理用户登录、退出登录、获取个人信息、修改个人信息及密码等认证相关功能
 */
class AuthController extends Controller
{
    /**
    用户登录接口
    验证用户账号密码有效性，生成认证 Token，返回用户基础信息、权限编码及菜单树，同步更新登录记录与操作日志
    请求参数：
    username（用户名）：必填，字符串，最大长度 50 字符，用于用户身份标识
    password（密码）：必填，字符串，最小长度 6 位，用于用户身份校验
    自定义验证提示：
    username.required：用户名不能为空
    username.max：用户名长度不能超过 50 个字符
    password.required：密码不能为空
    password.min：密码长度不能少于 6 位
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功返回业务成功码，失败返回对应错误码
    message：字符串，操作结果描述（如 "登录成功"、"参数错误" 等）
    data：对象，登录成功时返回完整数据，包含：
    token：字符串，用户认证 Token（标识为 ema_auth_token），用于后续接口权限校验
    user：对象，用户基础信息
    id：整数，用户唯一 ID
    username：字符串，登录用户名
    real_name：字符串，用户真实姓名
    email：字符串，用户绑定邮箱
    phone：字符串，用户绑定手机号
    avatar_url：字符串，用户头像访问链接
    department：对象 /null，所属部门信息（含部门 ID 和部门名称）
    position：字符串，用户职位
    employee_no：字符串，员工编号
    permissions：数组，用户权限编码集合（提取 permission_code 字段）
    menus：数组，结构化权限菜单树（通过 buildMenuTree 方法构建，适配前端渲染）
    核心逻辑说明：
    前置校验：仅状态为启用（status=1）的用户可登录，禁用用户直接返回失败
    密码校验：通过 Hash::check 方法验证密码正确性，保障加密存储的安全性
    状态更新：登录成功后自动更新用户最后登录时间（last_login_time）和登录 IP（last_login_ip）
    日志记录：参数错误、用户不存在、密码错误、系统异常等场景均记录详细日志，便于问题排查
    权限处理：关联查询用户所属部门、角色及权限，自动构建前端所需的菜单结构
    @param Request $request 请求对象，包含登录所需参数
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含登录结果与相关数据*/
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
     * 用户退出登录接口
     * 清除当前用户的登录状态和认证信息
     * @return \Illuminate\Http\JsonResponse
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
    获取当前登录用户信息接口
    基于登录时生成的认证 Token，查询并返回当前登录用户的基础信息、所属部门及角色列表
    请求参数：无（依赖请求头中的认证 Token，用于身份校验）
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（获取成功 / 获取用户信息失败）
    data：对象，获取成功时返回用户详细信息：
    id：整数，用户唯一 ID
    username：字符串，登录用户名
    real_name：字符串，用户真实姓名
    email：字符串，用户绑定邮箱
    phone：字符串，用户绑定手机号
    avatar_url：字符串，用户头像访问链接
    department：对象 /null，所属部门信息（含部门 ID 和部门名称）
    position：字符串，用户职位
    employee_no：字符串，员工编号
    roles：数组，用户所属角色列表，每个角色包含：
    id：整数，角色 ID
    name：字符串，角色名称（role_name）
    code：字符串，角色编码（role_code）
    说明：
    接口依赖认证 Token，需在请求头中携带，未授权或 Token 失效时会返回对应权限错误
    关联查询用户所属部门（department）和角色（roles）数据，无需前端二次请求
    角色信息已格式化为简洁结构，包含角色 ID、名称和编码，适配权限控制场景
    @param Request $request 请求对象，自动携带当前登录用户信息（通过 Token 解析）
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含用户信息或错误提示
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
    更新个人信息接口
    允许当前登录用户修改自身基础信息（真实姓名、邮箱、手机号、头像链接），同步记录操作日志
    请求参数：
    real_name（真实姓名）：必填，字符串，最大 50 字符
    email（邮箱）：可选，字符串，最大 100 字符，需符合邮箱格式
    phone（手机号）：可选，字符串，最大 20 字符
    avatar_url（头像链接）：可选，字符串，最大 255 字符
    自定义验证提示：
    real_name.required：真实姓名不能为空
    real_name.max：真实姓名长度不能超过 50 个字符
    email.email：邮箱格式不正确
    phone.max：手机号长度不能超过 20 个字符
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（更新成功 / 参数错误 / 更新失败）
    说明：
    接口依赖认证 Token，仅当前登录用户可修改自身信息，无权限修改其他用户数据
    仅更新请求中传递的非空字段，未传递字段保持原有值不变
    支持部分字段更新（如仅修改手机号、仅更换头像等）
    更新成功后记录操作日志，包含操作人、操作类型及请求信息，便于追溯
    @param Request $request 请求对象，包含待更新的个人信息参数
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含更新结果提示*/
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
    修改密码接口
    允许当前登录用户修改登录密码，需验证原密码正确性，新密码需满足长度及一致性要求
    请求参数：
    old_password（原密码）：必填，字符串，用于校验用户身份
    new_password（新密码）：必填，字符串，最小 6 位
    new_password_confirmation（新密码确认）：必填，字符串，需与 new_password 完全一致
    自定义验证提示：
    old_password.required：原密码不能为空
    new_password.required：新密码不能为空
    new_password.min：新密码长度不能少于 6 位
    new_password.confirmed：两次输入的密码不一致
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（密码修改成功 / 参数错误 / 原密码错误 / 密码修改失败）
    说明：
    接口依赖认证 Token，仅当前登录用户可修改自身密码，无权限修改其他用户密码
    原密码校验：通过 Hash::check 方法验证原密码正确性，防止密码被恶意修改
    密码加密：新密码通过 Hash::make 方法加密存储，保障密码安全性
    操作日志：修改成功后记录操作日志，包含操作人、操作类型及请求信息，便于追溯
    @param Request $request 请求对象，包含原密码、新密码及确认密码参数
    @return \Illuminate\Http\JsonResponse JSON 格式响应，包含修改结果提示*/

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
    获取用户的所有权限
    整合用户所属角色的所有权限，去重后返回权限集合，用于权限校验和菜单生成
    逻辑说明：
    遍历用户关联的所有角色（$user->roles）
    合并每个角色拥有的权限（$role->permissions）到集合中
    通过 unique ('id') 对权限去重，避免同一权限因多角色关联导致重复
    @param \App\Models\User $user 用户模型实例，需关联 roles.permissions 关系
    @return \Illuminate\Support\Collection 去重后的权限集合，包含权限模型的所有字段*/
    private function getUserPermissions($user)
    {
        $permissions = collect();

        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    /**
    构建用户权限菜单树
    从用户权限集合中筛选出菜单类型权限，按父子关系层级结构构建树形菜单，适配前端导航菜单渲染需求
    逻辑说明：
    筛选权限类型为菜单（permission_type=1）的权限，并按排序号（sort_order）升序排列
    提取顶级菜单（parent_id=0）作为树的根节点
    递归调用 buildMenuChildren 方法为每个顶级菜单添加子菜单，形成完整树形结构
    菜单节点结构：
    id：整数，权限 ID
    code：字符串，权限编码（permission_code）
    name：字符串，菜单名称（permission_name）
    path：字符串，菜单访问路径（resource_url）
    icon：字符串，菜单图标标识
    children：数组，子菜单列表（递归构建的下级菜单节点）
    @param \Illuminate\Support\Collection $permissions 用户权限集合（需包含菜单类型权限）
    @return array 结构化的菜单树数组，包含顶级菜单及嵌套的子菜单*/
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
    递归构建子菜单
    作为 buildMenuTree 方法的辅助方法，通过父菜单 ID 递归查找并构建下级子菜单，形成多级嵌套的菜单结构
    逻辑说明：
    根据传入的父菜单 ID（
    ），从权限集合中筛选出对应的子菜单（parentId）
    为每个子菜单构建统一格式的节点数据，包含 ID、编码、名称、路径、图标等信息
    递归调用自身为当前子菜单添加下一级子菜单（children 字段），直至无下级菜单
    子菜单节点结构：
    id：整数，权限 ID
    code：字符串，权限编码（permission_code）
    name：字符串，菜单名称（permission_name）
    path：字符串，菜单访问路径（resource_url）
    icon：字符串，菜单图标标识
    children：数组，下一级子菜单列表（递归构建的结果，无下级则为空数组）
    @param int $parentId 父菜单 ID，用于筛选当前层级的子菜单
    @param \Illuminate\Support\Collection $permissions 菜单类型权限集合（已筛选并排序）
    @return array 当前父菜单下的子菜单数组，包含嵌套的下级菜单结构*/
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
    记录用户操作日志
    统一记录用户关键操作（登录、退出、修改信息等）的日志，包含操作类型、结果及状态，支持异常处理
    逻辑说明：
    根据操作类型（$type）映射对应的日志类型编码（如 LOGIN→4、LOGOUT→5 等）
    调用基础日志记录方法（$this->log）记录操作详情，包含操作名称、用户 ID 等信息
    若记录日志过程中发生异常，会自动记录错误日志，确保日志系统自身问题可追溯
    参数说明：
    $user：用户模型实例，用于获取操作用户 ID
    $type：字符串，操作类型标识（支持 LOGIN/LOGOUT/UPDATE 等）
    $name：字符串，操作名称描述（如 "用户登录"、"修改密码" 等）
    $request：请求对象，用于获取操作上下文（隐含在基础 log 方法中）
    日志类型映射：
    LOGIN → 4（登录操作）
    LOGOUT → 5（退出操作）
    UPDATE → 2（修改操作）
    其他类型 → 0（默认类型）
    @param \App\Models\User $user 操作用户模型实例
    @param string $type 操作类型标识
    @param string $name 操作名称描述
    @param Request $request 请求对象
    @return void
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
