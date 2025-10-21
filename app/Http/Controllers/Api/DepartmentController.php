<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * 获取部门列表（树形结构）
     */
    public function index(Request $request)
    {
        try {
            $query = Department::with(['manager', 'creator', 'updater']);

            // 搜索条件
            if ($request->has('keyword') && !empty(trim($request->keyword))) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('department_name', 'like', "%{$keyword}%")
                      ->orWhere('department_code', 'like', "%{$keyword}%");
                });
            }



            $departments = $query->orderBy('sort_order')->get();

            // 构建树形结构
            $tree = $this->buildTree($departments);

            // 记录查询日志
            $this->log(0, '查看部门列表', [
                'title' => '部门管理',
                'result' => json_encode(['count' => count($tree)]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('获取成功', $tree);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, '查看部门列表失败：' . $e->getMessage(), [
                'title' => '部门管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取部门列表失败');
        }
    }

    /**
     * 获取部门详情
     */
    public function show($id)
    {
        try {
            $department = Department::with(['manager', 'parent', 'children', 'users'])
                ->find($id);

            if (!$department) {
                // 记录查询失败日志
                $this->log(8, "查看部门详情失败：部门不存在 (ID: $id)", [
                    'title' => '部门管理',
                    'error' => '部门不存在',
                    'status' => \App\Models\Logs::STATUS_FAILED
                ]);
                return json_fail('部门不存在');
            }

            // 记录查询日志
            $this->log(0, "查看部门详情：{$department->department_name}", [
                'title' => '部门管理',
                'result' => json_encode(['department_id' => $id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('获取成功', [
                'id' => $department->id,
                'department_code' => $department->department_code,
                'department_name' => $department->department_name,
                'parent_id' => $department->parent_id,
                'level_path' => $department->level_path,
                'manager_id' => $department->manager_id,
                'manager' => $department->manager ? [
                    'id' => $department->manager->id,
                    'real_name' => $department->manager->real_name,
                ] : null,
                'description' => $department->description,
                'sort_order' => $department->sort_order,
                'user_count' => $department->users->where('status', 1)->count(),
                'children_count' => $department->children->count(),
                'created_at' => $department->created_at,
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            $this->log(8, "查看部门详情失败：{$e->getMessage()}", [
                'title' => '部门管理',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取部门详情失败');
        }
    }

    /**
     * 创建部门
     */
    public function store(Request $request)
    {
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

        if ($validator->fails()) {
            // 记录参数验证失败日志
            $this->log(8, "创建部门失败：参数错误 - {$validator->errors()->first()}", [
                'title' => '部门管理',
                'error' => $validator->errors()->first(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('参数错误', $validator->errors()->first());
        }

        // 验证parent_id
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
            DB::beginTransaction();

            // 生成层级路径
            $levelPath = $this->generateLevelPath($request->parent_id);

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

            DB::commit();

            // 记录创建成功日志
            $this->log(1, "创建部门：{$department->department_name}", [
                'title' => '部门管理',
                'result' => json_encode(['department_id' => $department->id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

            return json_success('部门创建成功', $department);
        } catch (\Exception $e) {
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
     * 更新部门
     */
    public function update(Request $request, $id)
    {
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

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        // 验证parent_id
        if ($request->parent_id && $request->parent_id > 0) {
            $parentExists = Department::where('id', $request->parent_id)->exists();
            if (!$parentExists) {
                return json_fail('上级部门不存在');
            }
        }

        // 检查是否设置自己为上级部门
        if ($request->parent_id == $id) {
            return json_fail('不能设置自己为上级部门');
        }

        // 检查是否设置子部门为上级部门
        if ($request->parent_id && $this->isChildDepartment($id, $request->parent_id)) {
            return json_fail('不能设置子部门为上级部门');
        }

        try {
            DB::beginTransaction();

            // 如果修改了上级部门，需要重新生成层级路径
            $levelPath = $department->level_path;
            if ($request->parent_id != $department->parent_id) {
                $levelPath = $this->generateLevelPath($request->parent_id, $id);
            }

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

            DB::commit();

            return json_success('部门更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return json_fail('部门更新失败');
        }
    }

    /**
     * 删除部门
     */
    public function destroy(Request $request, $id)
    {
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

        // 检查是否有子部门
        if ($department->children()->count() > 0) {
            // 记录删除失败日志
            $this->log(8, "删除部门失败：{$department->department_name} 下还有子部门", [
                'title' => '部门管理',
                'error' => '该部门下还有子部门，无法删除',
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('该部门下还有子部门，无法删除');
        }

        // 检查是否有用户
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
            $departmentName = $department->department_name;
            $department->delete();

            // 记录删除成功日志
            $this->log(3, "删除部门：{$departmentName}", [
                'title' => '部门管理',
                'result' => json_encode(['deleted_department_id' => $id]),
                'status' => \App\Models\Logs::STATUS_SUCCESS
            ]);

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
     * 获取可选的部门负责人列表
     */
    public function getManagers()
    {
        try {
            $users = User::select('id', 'real_name', 'username', 'position')
                ->orderBy('real_name')
                ->get();

            return json_success('获取成功', $users);
        } catch (\Exception $e) {
            return json_fail('获取负责人列表失败');
        }
    }

    /**
     * 构建树形结构
     */
    private function buildTree($departments, $parentId = 0)
    {
        $tree = [];
        
        foreach ($departments as $department) {
            if ($department->parent_id == $parentId) {
                $node = [
                    'id' => $department->id,
                    'department_code' => $department->department_code,
                    'department_name' => $department->department_name,
                    'parent_id' => $department->parent_id,
                    'manager' => $department->manager ? [
                        'id' => $department->manager->id,
                        'real_name' => $department->manager->real_name,
                    ] : null,
                    'description' => $department->description,
                    'sort_order' => $department->sort_order,
                    'user_count' => $department->users()->where('status', 1)->count(),
                    'created_at' => $department->created_at,
                    'children' => $this->buildTree($departments, $department->id),
                ];
                
                $tree[] = $node;
            }
        }
        
        return $tree;
    }

    /**
     * 生成层级路径
     */
    private function generateLevelPath($parentId, $currentId = null)
    {
        if (!$parentId) {
            return $currentId ?: '';
        }

        $parent = Department::find($parentId);
        if (!$parent) {
            return $currentId ?: '';
        }

        $path = $parent->level_path;
        if ($currentId) {
            $path = $path ? $path . ',' . $currentId : $currentId;
        }

        return $path;
    }

    /**
     * 检查是否为子部门
     */
    private function isChildDepartment($parentId, $childId)
    {
        $child = Department::find($childId);
        if (!$child || !$child->level_path) {
            return false;
        }

        $levelIds = explode(',', $child->level_path);
        return in_array($parentId, $levelIds);
    }

    /**
     * 更新子部门的层级路径
     */
    private function updateChildrenLevelPath($departmentId)
    {
        $department = Department::find($departmentId);
        if (!$department) {
            return;
        }

        $children = Department::where('parent_id', $departmentId)->get();
        foreach ($children as $child) {
            $newLevelPath = $department->level_path ? $department->level_path . ',' . $child->id : $child->id;
            $child->update(['level_path' => $newLevelPath]);
            
            // 递归更新子部门的子部门
            $this->updateChildrenLevelPath($child->id);
        }
    }
}
