<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * 部门控制器
 * 负责部门的增删改查和树形结构管理
 */
class DepartmentController extends Controller
{
  /**
 * 获取部门列表（树形结构）index
 *
 * 功能描述：获取系统中所有部门信息，并以树形结构形式返回，支持关键词搜索功能
 *
 * 传入参数：
 * - keyword (string, optional): 搜索关键词，用于模糊匹配部门名称或部门编码
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 部门树形结构数据
 *   - id (int): 部门ID
 *   - department_code (string): 部门编码
 *   - department_name (string): 部门名称
 *   - parent_id (int): 上级部门ID
 *   - manager (object|null): 部门负责人信息
 *     - id (int): 负责人ID
 *     - real_name (string): 负责人姓名
 *   - description (string): 部门描述
 *   - sort_order (int): 排序值
 *   - user_count (int): 部门有效用户数
 *   - created_at (string): 创建时间
 *   - children (array): 子部门列表，递归结构
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器，预加载关联关系
        $query = Department::with(['manager', 'creator', 'updater']);

        // 搜索条件：如果提供了关键词，则按部门名称或部门编码进行模糊搜索
        if ($request->has('keyword') && !empty(trim($request->keyword))) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('department_name', 'like', "%{$keyword}%")     // 按部门名称模糊搜索
                  ->orWhere('department_code', 'like', "%{$keyword}%");  // 按部门编码模糊搜索
            });
        }

        // 执行查询并按排序字段升序排列
        $departments = $query->orderBy('sort_order')->get();

        // 将扁平的部门数据构建成树形结构
        $tree = $this->buildTree($departments);

        // 记录查询成功的日志信息
        $this->log(0, '查看部门列表', [
            'title' => '部门管理',
            'result' => json_encode(['count' => count($tree)]),
            'status' => \App\Models\Logs::STATUS_SUCCESS
        ]);

        // 返回成功响应，包含树形结构的部门数据
        return json_success('获取成功', $tree);

    } catch (\Exception $e) {
        // 异常处理：记录错误日志并返回失败响应
        $this->log(8, '查看部门列表失败：' . $e->getMessage(), [
            'title' => '部门管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('获取部门列表失败');
    }
}


  /**
 * 获取部门详情 show
 *
 * 功能描述：根据部门ID获取指定部门的详细信息，包括关联的负责人、子部门等信息
 *
 * 传入参数：
 * - id (int): 部门ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 部门详细信息
 *   - id (int): 部门ID
 *   - department_code (string): 部门编码
 *   - department_name (string): 部门名称
 *   - parent_id (int): 上级部门ID
 *   - level_path (string): 层级路径
 *   - manager_id (int): 部门负责人ID
 *   - manager (object|null): 部门负责人信息
 *     - id (int): 负责人ID
 *     - real_name (string): 负责人姓名
 *   - description (string): 部门描述
 *   - sort_order (int): 排序值
 *   - user_count (int): 部门有效用户数
 *   - children_count (int): 子部门数量
 *   - created_at (string): 创建时间
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function show($id)
{
    try {
        // 查询指定ID的部门信息，并预加载关联关系
        $department = Department::with(['manager', 'parent', 'children', 'users'])
            ->find($id);

        // 检查部门是否存在
        if (!$department) {
            // 记录查询失败日志
            $this->log(8, "查看部门详情失败：部门不存在 (ID: $id)", [
                'title' => '部门管理',
                'error' => '部门不存在',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('部门不存在');
        }

        // 记录查询成功的日志信息
        $this->log(0, "查看部门详情：{$department->department_name}", [
            'title' => '部门管理',
            'result' => json_encode(['department_id' => $id]),
            'status' => \App\Models\Logs::STATUS_SUCCESS
        ]);

        // 返回成功响应，包含部门详细信息
        return json_success('获取成功', [
            'id' => $department->id,                                // 部门ID
            'department_code' => $department->department_code,      // 部门编码
            'department_name' => $department->department_name,      // 部门名称
            'parent_id' => $department->parent_id,                  // 上级部门ID
            'level_path' => $department->level_path,                // 层级路径
            'manager_id' => $department->manager_id,                // 部门负责人ID
            'manager' => $department->manager ? [                   // 部门负责人信息
                'id' => $department->manager->id,
                'real_name' => $department->manager->real_name,
            ] : null,
            'description' => $department->description,              // 部门描述
            'sort_order' => $department->sort_order,                // 排序值
            'user_count' => $department->users->where('status', 1)->count(),  // 部门有效用户数
            'children_count' => $department->children->count(),     // 子部门数量
            'created_at' => $department->created_at,                // 创建时间
        ]);

    } catch (\Exception $e) {
        // 异常处理：记录错误日志并返回失败响应
        $this->log(8, "查看部门详情失败：{$e->getMessage()}", [
            'title' => '部门管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('获取部门详情失败');
    }
}


  /**
 * 创建部门 store
 *
 * 功能描述：创建一个新的部门，包含部门编码、名称、上级部门、负责人等信息
 *
 * 传入参数：
 * - department_code (string): 部门编码，必填，唯一
 * - department_name (string): 部门名称，必填
 * - parent_id (int, optional): 上级部门ID
 * - manager_id (int, optional): 部门负责人ID
 * - description (string, optional): 部门描述
 * - sort_order (int, optional): 排序值
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建成功的部门信息
 *   - id (int): 部门ID
 *   - department_code (string): 部门编码
 *   - department_name (string): 部门名称
 *   - parent_id (int): 上级部门ID
 *   - level_path (string): 层级路径
 *   - manager_id (int): 部门负责人ID
 *   - description (string): 部门描述
 *   - sort_order (int): 排序值
 *   - created_by (int): 创建人ID
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function store(Request $request)
{
    // 验证输入参数
    $validator = Validator::make($request->all(), [
        'department_code' => 'required|string|max:50|unique:departments,department_code',
        'department_name' => 'required|string|max:100',
        'parent_id' => 'nullable|integer|min:0',
        'manager_id' => 'nullable|exists:users,id',
        'description' => 'nullable|string',
        'sort_order' => 'nullable|integer|min:0',
    ], [
        'department_code.required' => '部门编码不能为空',
        'department_code.unique' => '部门编码已存在',
        'department_name.required' => '部门名称不能为空',
        'parent_id.exists' => '上级部门不存在',
        'manager_id.exists' => '部门负责人不存在',
    ]);

    // 参数验证失败处理
    if ($validator->fails()) {
        // 记录参数验证失败日志
        $this->log(8, "创建部门失败：参数错误 - {$validator->errors()->first()}", [
            'title' => '部门管理',
            'error' => $validator->errors()->first(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('参数错误', $validator->errors()->first());
    }

    // 验证上级部门是否存在
    if ($request->parent_id && $request->parent_id > 0) {
        $parentExists = Department::where('id', $request->parent_id)->exists();
        if (!$parentExists) {
            // 记录验证失败日志
            $this->log(8, "创建部门失败：上级部门不存在 (parent_id: {$request->parent_id})", [
                'title' => '部门管理',
                'error' => '上级部门不存在',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('上级部门不存在');
        }
    }

    try {
        // 开启数据库事务
        DB::beginTransaction();

        // 生成层级路径
        $levelPath = $this->generateLevelPath($request->parent_id);

        // 创建部门记录
        $department = Department::create([
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'parent_id' => $request->parent_id ?: 0,
            'level_path' => $levelPath,
            'manager_id' => $request->manager_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'created_by' => $request->user()->id,
        ]);

        // 提交事务
        DB::commit();

        // 记录创建成功日志
        $this->log(1, "创建部门：{$department->department_name}", [
            'title' => '部门管理',
            'result' => json_encode(['department_id' => $department->id]),
            'status' => \App\Models\Logs::STATUS_SUCCESS
        ]);

        // 返回成功响应，包含创建的部门信息
        return json_success('部门创建成功', $department);

    } catch (\Exception $e) {
        // 回滚事务
        DB::rollBack();

        // 记录创建失败日志
        $this->log(8, "创建部门失败：{$e->getMessage()}", [
            'title' => '部门管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);

        return json_fail('部门创建失败');
    }
}

   /**
 * 更新部门 update
 *
 * 功能描述：根据部门ID更新指定部门的信息，包括部门编码、名称、上级部门、负责人等信息
 *
 * 传入参数：
 * - id (int): 部门ID
 * - department_code (string): 部门编码，必填，唯一（排除当前部门）
 * - department_name (string): 部门名称，必填
 * - parent_id (int, optional): 上级部门ID
 * - manager_id (int, optional): 部门负责人ID
 * - description (string, optional): 部门描述
 * - sort_order (int, optional): 排序值
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (null): 空数据
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function update(Request $request, $id)
{
    // 查找要更新的部门，如果不存在则返回错误
    $department = Department::find($id);
    if (!$department) {
        // 记录部门不存在日志
        $this->log(8, "更新部门失败：部门不存在 (ID: $id)", [
            'title' => '部门管理',
            'error' => '部门不存在',
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('部门不存在');
    }

    // 验证输入参数
    $validator = Validator::make($request->all(), [
        'department_code' => 'required|string|max:50|unique:departments,department_code,' . $id,
        'department_name' => 'required|string|max:100',
        'parent_id' => 'nullable|integer|min:0',
        'manager_id' => 'nullable|exists:users,id',
        'description' => 'nullable|string',
        'sort_order' => 'nullable|integer|min:0',
    ], [
        'department_code.required' => '部门编码不能为空',
        'department_code.unique' => '部门编码已存在',
        'department_name.required' => '部门名称不能为空',
        'parent_id.exists' => '上级部门不存在',
        'manager_id.exists' => '部门负责人不存在',
    ]);

    // 参数验证失败处理
    if ($validator->fails()) {
        return json_fail('参数错误', $validator->errors()->first());
    }

    // 验证上级部门是否存在
    if ($request->parent_id && $request->parent_id > 0) {
        $parentExists = Department::where('id', $request->parent_id)->exists();
        if (!$parentExists) {
            return json_fail('上级部门不存在');
        }
    }

    // 检查是否设置自己为上级部门（防止循环引用）
    if ($request->parent_id == $id) {
        return json_fail('不能设置自己为上级部门');
    }

    // 检查是否设置子部门为上级部门（防止循环引用）
    if ($request->parent_id && $this->isChildDepartment($id, $request->parent_id)) {
        return json_fail('不能设置子部门为上级部门');
    }

    try {
        // 开启数据库事务
        DB::beginTransaction();

        // 如果修改了上级部门，需要重新生成层级路径
        $levelPath = $department->level_path;
        if ($request->parent_id != $department->parent_id) {
            $levelPath = $this->generateLevelPath($request->parent_id, $id);
        }

        // 更新部门信息
        $department->update([
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'parent_id' => $request->parent_id ?: 0,
            'level_path' => $levelPath,
            'manager_id' => $request->manager_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?: 0,
            'updated_by' => $request->user()->id,
        ]);

        // 如果修改了上级部门，需要更新所有子部门的层级路径
        if ($request->parent_id != $department->parent_id) {
            $this->updateChildrenLevelPath($id);
        }

        // 提交事务
        DB::commit();

        // 返回成功响应
        return json_success('部门更新成功');

    } catch (\Exception $e) {
        // 回滚事务
        DB::rollBack();
        return json_fail('部门更新失败');
    }
}


  /**
 * 删除部门 destroy
 *
 * 功能描述：根据部门ID删除指定部门，删除前会检查是否存在子部门或用户关联
 *
 * 传入参数：
 * - id (int): 部门ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (null): 空数据
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function destroy(Request $request, $id)
{
    // 查找要删除的部门，如果不存在则返回错误
    $department = Department::find($id);
    if (!$department) {
        // 记录部门不存在日志
        $this->log(8, "删除部门失败：部门不存在 (ID: $id)", [
            'title' => '部门管理',
            'error' => '部门不存在',
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('部门不存在');
    }

    // 检查是否有子部门，如果有则不能删除
    if ($department->children()->count() > 0) {
        // 记录删除失败日志
        $this->log(8, "删除部门失败：{$department->department_name} 下还有子部门", [
            'title' => '部门管理',
            'error' => '该部门下还有子部门，无法删除',
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('该部门下还有子部门，无法删除');
    }

    // 检查是否有用户关联，如果有则不能删除
    if ($department->users()->count() > 0) {
        // 记录删除失败日志
        $this->log(8, "删除部门失败：{$department->department_name} 下还有用户", [
            'title' => '部门管理',
            'error' => '该部门下还有用户，无法删除',
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('该部门下还有用户，无法删除');
    }

    try {
        // 保存部门名称用于日志记录
        $departmentName = $department->department_name;
        // 执行删除操作
        $department->delete();

        // 记录删除成功日志
        $this->log(3, "删除部门：{$departmentName}", [
            'title' => '部门管理',
            'result' => json_encode(['deleted_department_id' => $id]),
            'status' => \App\Models\Logs::STATUS_SUCCESS
        ]);

        // 返回成功响应
        return json_success('部门删除成功');

    } catch (\Exception $e) {
        // 记录删除失败日志
        $this->log(8, "删除部门失败：{$e->getMessage()}", [
            'title' => '部门管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('部门删除失败');
    }
}


   /**
 * 获取可选的部门负责人列表 getManagers
 *
 * 功能描述：获取系统中所有用户的信息，用于部门负责人下拉选择框
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 用户列表
 *   - id (int): 用户ID
 *   - real_name (string): 用户真实姓名
 *   - username (string): 用户名
 *   - position (string): 用户职位
 *
 * 错误响应：
 * - code (int): 状态码，非0表示失败
 * - msg (string): 错误信息
 * - data (null): 空数据
 */
public function getManagers()
{
    try {
        // 查询所有用户的基本信息，并按姓名排序
        $users = User::select('id', 'real_name', 'username', 'position')
            ->orderBy('real_name')
            ->get();

        // 返回成功响应，包含用户列表数据
        return json_success('获取成功', $users);

    } catch (\Exception $e) {
        // 异常处理：返回失败响应
        return json_fail('获取负责人列表失败');
    }
}

   /**
 * 构建树形结构 buildTree
 *
 * 功能描述：将扁平的部门数据递归构建成树形结构，便于前端展示层级关系
 *
 * 传入参数：
 * - departments (Collection): 部门集合数据
 * - parentId (int): 父级部门ID，默认为0表示顶级部门
 *
 * 输出参数：
 * - array: 树形结构的部门数据
 *   - id (int): 部门ID
 *   - department_code (string): 部门编码
 *   - department_name (string): 部门名称
 *   - parent_id (int): 上级部门ID
 *   - manager (object|null): 部门负责人信息
 *     - id (int): 负责人ID
 *     - real_name (string): 负责人姓名
 *   - description (string): 部门描述
 *   - sort_order (int): 排序值
 *   - user_count (int): 部门有效用户数
 *   - created_at (string): 创建时间
 *   - children (array): 子部门列表，递归结构
 */
private function buildTree($departments, $parentId = 0)
{
    // 初始化树形结构数组
    $tree = [];

    // 遍历所有部门数据
    foreach ($departments as $department) {
        // 如果当前部门的父级ID等于指定的父级ID，则将其作为节点加入树中
        if ($department->parent_id == $parentId) {
            $node = [
                'id' => $department->id,                                    // 部门ID
                'department_code' => $department->department_code,          // 部门编码
                'department_name' => $department->department_name,          // 部门名称
                'parent_id' => $department->parent_id,                      // 上级部门ID
                'manager' => $department->manager ? [                       // 部门负责人信息
                    'id' => $department->manager->id,
                    'real_name' => $department->manager->real_name,
                ] : null,
                'description' => $department->description,                  // 部门描述
                'sort_order' => $department->sort_order,                    // 排序值
                'user_count' => $department->users()->where('status', 1)->count(),  // 部门有效用户数
                'created_at' => $department->created_at,                    // 创建时间
                'children' => $this->buildTree($departments, $department->id),  // 递归构建子部门树形结构
            ];

            // 将当前节点添加到树形结构中
            $tree[] = $node;
        }
    }

    // 返回构建好的树形结构
    return $tree;
}

   /**
 * 生成层级路径 generateLevelPath
 *
 * 功能描述：根据父部门ID和当前部门ID生成层级路径字符串，用于表示部门的层级关系
 *
 * 传入参数：
 * - parentId (int): 父部门ID
 * - currentId (int, optional): 当前部门ID
 *
 * 输出参数：
 * - string: 层级路径字符串，格式为"祖先ID,父ID,当前ID"或空字符串
 */
private function generateLevelPath($parentId, $currentId = null)
{
    // 如果没有父部门ID，直接返回当前部门ID或空字符串
    if (!$parentId) {
        return $currentId ?: '';
    }

    // 查找父部门信息
    $parent = Department::find($parentId);
    // 如果父部门不存在，返回当前部门ID或空字符串
    if (!$parent) {
        return $currentId ?: '';
    }

    // 获取父部门的层级路径
    $path = $parent->level_path;
    // 如果有当前部门ID，将其添加到路径中
    if ($currentId) {
        $path = $path ? $path . ',' . $currentId : $currentId;
    }

    // 返回生成的层级路径
    return $path;
}

  /**
 * 检查是否为子部门 isChildDepartment
 *
 * 功能描述：检查指定的部门是否为目标部门的子部门，用于防止设置子部门为上级部门的循环引用问题
 *
 * 传入参数：
 * - parentId (int): 父部门ID
 * - childId (int): 子部门ID
 *
 * 输出参数：
 * - bool: true表示childId是parentId的子部门，false表示不是
 */
private function isChildDepartment($parentId, $childId)
{
    // 查找子部门信息
    $child = Department::find($childId);
    // 如果子部门不存在或没有层级路径，返回false
    if (!$child || !$child->level_path) {
        return false;
    }

    // 将子部门的层级路径拆分为ID数组
    $levelIds = explode(',', $child->level_path);
    // 检查父部门ID是否在子部门的层级路径中
    return in_array($parentId, $levelIds);
}


   /**
 * 更新子部门的层级路径 updateChildrenLevelPath
 *
 * 功能描述：递归更新指定部门的所有子部门的层级路径，当部门的上级部门发生变更时调用
 *
 * 传入参数：
 * - departmentId (int): 部门ID
 *
 * 输出参数：无
 */
private function updateChildrenLevelPath($departmentId)
{
    // 查找指定ID的部门信息
    $department = Department::find($departmentId);
    // 如果部门不存在，直接返回
    if (!$department) {
        return;
    }

    // 获取该部门的所有直接子部门
    $children = Department::where('parent_id', $departmentId)->get();
    // 遍历所有子部门
    foreach ($children as $child) {
        // 生成新的层级路径：父部门路径 + 子部门ID
        $newLevelPath = $department->level_path ? $department->level_path . ',' . $child->id : $child->id;
        // 更新子部门的层级路径
        $child->update(['level_path' => $newLevelPath]);

        // 递归更新子部门的子部门
        $this->updateChildrenLevelPath($child->id);
    }
}

}
