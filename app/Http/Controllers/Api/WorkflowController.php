<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class WorkflowController extends Controller
{
    /**
     * 功能: 获取工作流列表，支持分页、搜索与筛选
     * 请求参数:
     * - page(int, 可选): 页码，默认1
     * - limit(int, 可选): 每页数量，默认15
     * - name(string, 可选): 按名称模糊搜索
     * - code(string, 可选): 按代码模糊搜索
     * - keyword(string, 可选): 名称/代码/描述综合关键字
     * - status(int, 可选): 状态筛选（1启用/0禁用）
     * - isValid(boolean|string|int, 可选): 有效性筛选，支持 true/'true'/1/'1'
     * - caseType(string|int, 可选): 项目类型筛选
     * 返回参数:
     * - 使用 `json_page(data, total, msg)` 标准分页结构
     * 接口: GET /workflows
     */
    public function index(Request $request)
    {
        try {
            // 步骤说明：确保数据存在 -> 构建查询 -> 应用搜索/筛选 -> 分页 -> 格式化输出
            // 确保工作流数据存在，如果不存在则调用 Seeder
            $this->ensureWorkflowsExist();
            
            $query = Workflow::with('creator')->orderBy('id', 'asc');
            
            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', "%{$request->name}%");
            }

            if ($request->filled('code')) {
                $query->where('code', 'like', "%{$request->code}%");
            }

            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }

            if ($request->filled('isValid')) {
                // 支持多种true值表示：true, 'true', 1, '1'
                $isValidValue = $request->isValid;
                if ($isValidValue === '' || $isValidValue === null) {
                    // 如果是空值，不添加筛选条件
                } else {
                    $isValid = $isValidValue === 'true' || $isValidValue === true || 
                              $isValidValue === 1 || $isValidValue === '1';
                    $query->where('status', $isValid ? 1 : 0);
                }
            }

            // 项目类型筛选
            if ($request->filled('caseType')) {
                $query->where('case_type', $request->caseType);
            }

            // 分页
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);
            
            $total = $query->count();

            $workflows = $query->offset(($page - 1) * $limit)
                             ->limit($limit)
                             ->get();

            // 格式化数据以匹配前端期望的格式
            $formattedWorkflows = $workflows->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'code' => $workflow->code,
                    'caseType' => $workflow->case_type,
                    'description' => $workflow->description,
                    'isValid' => $workflow->status == 1,
                    'nodeCount' => $workflow->node_count,
                    'updateUser' => $workflow->creator ? $workflow->creator->real_name ?? $workflow->creator->username : 'System',
                    'updateTime' => $workflow->updated_at->format('Y-m-d H:i:s'),
                    'nodes' => $workflow->nodes ?? []
                ];
            });

            return json_page($formattedWorkflows, $total, '获取成功');
        } catch (\Exception $e) {
            return json_fail('获取工作流列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取指定工作流详情
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * 返回参数:
     * - 使用 `json_success(msg, data)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: GET /workflows/{id}
     */
    public function show($id)
    {
        try {
            $workflow = Workflow::with('creator')->findOrFail($id);
            
            $data = [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'code' => $workflow->code,
                'caseType' => $workflow->case_type,
                'description' => $workflow->description,
                'isValid' => $workflow->status == 1,
                'nodeCount' => $workflow->node_count,
                'updateUser' => $workflow->creator ? $workflow->creator->real_name ?? $workflow->creator->username : 'System',
                'updateTime' => $workflow->updated_at->format('Y-m-d H:i:s'),
                'nodes' => $workflow->nodes ?? []
            ];

            return json_success('获取成功', $data);
        } catch (\Exception $e) {
            return json_fail('获取工作流详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 创建工作流（示例实现，写入逻辑待接入）
     * 请求参数:
     * - name(string, 必填): 工作流名称
     * - code(string, 必填): 工作流代码
     * - caseType(string, 必填): 项目类型
     * - description(string, 可选): 描述
     * - nodes(array, 必填): 节点配置，至少2个
     * 返回参数:
     * - 使用 `json_success(msg, data)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: POST /workflows
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'caseType' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'nodes' => 'required|array|min:2',
        ], [
            'name.required' => '工作流名称不能为空',
            'code.required' => '工作流代码不能为空',
            'caseType.required' => '项目类型不能为空',
            'nodes.required' => '工作流节点不能为空',
            'nodes.min' => '工作流至少需要2个节点',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            // 步骤说明：校验参数 -> 构造数据 -> 返回创建结果（当前为模拟数据）
            // 模拟创建工作流
            $workflow = [
                'id' => rand(1000, 9999),
                'name' => $request->name,
                'code' => $request->code,
                'caseType' => $request->caseType,
                'description' => $request->description,
                'isValid' => true,
                'nodes' => $request->nodes,
                'updateUser' => $request->user()->username ?? 'admin',
                'updateTime' => now()->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            return json_success('工作流创建成功', $workflow);
        } catch (\Exception $e) {
            return json_fail('工作流创建失败');
        }
    }

    /**
     * 功能: 更新工作流状态与节点配置
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * - isValid(boolean, 可选): 是否启用
     * - nodes(array, 可选): 节点配置
     * 返回参数:
     * - 使用 `json_success(msg)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: PUT /workflows/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'isValid' => 'nullable|boolean',
            'nodes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            $workflow = Workflow::findOrFail($id);
            
            // 步骤说明：查找工作流 -> 有选择地更新状态/节点 -> 设置更新人 -> 保存
            // 更新工作流状态
            if ($request->has('isValid')) {
                $workflow->status = $request->isValid ? 1 : 0;
            }
            
            // 更新节点配置
            if ($request->has('nodes')) {
                $workflow->nodes = $request->nodes;
            }
            
            // 设置更新人
            $workflow->updated_by = auth()->id();
            $workflow->save();
            
            return json_success('工作流配置更新成功');
        } catch (\Exception $e) {
            return json_fail('工作流配置更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 删除工作流（示例实现）
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * 返回参数:
     * - 使用 `json_success(msg)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: DELETE /workflows/{id}
     */
    public function destroy($id)
    {
        try {
            // 模拟删除工作流
            return json_success('工作流删除成功');
        } catch (\Exception $e) {
            return json_fail('工作流删除失败');
        }
    }

    /**
     * 功能: 启用/禁用工作流（示例实现）
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * 返回参数:
     * - 使用 `json_success(msg)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: PUT /workflows/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        try {
            // 模拟切换状态
            return json_success('工作流状态更新成功');
        } catch (\Exception $e) {
            return json_fail('工作流状态更新失败');
        }
    }

    /**
     * 功能: 获取指定工作流的节点配置
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * 返回参数:
     * - 使用 `json_success(msg, data)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: GET /workflows/{id}/nodes
     */
    public function getNodes($id)
    {
        try {
            $workflows = $this->getMockWorkflows();
            $workflow = collect($workflows)->firstWhere('id', (int)$id);

            if (!$workflow) {
                return json_fail('工作流不存在');
            }

            return json_success('获取成功', $workflow['nodes']);
        } catch (\Exception $e) {
            return json_fail('获取工作流节点失败');
        }
    }

    /**
     * 功能: 更新指定工作流的节点配置（示例实现）
     * 请求参数:
     * - id(int, 必填): 路径参数，工作流ID
     * - nodes(array, 必填): 节点配置，至少2个
     * 返回参数:
     * - 使用 `json_success(msg)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: PUT /workflows/{id}/nodes
     */
    public function updateNodes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nodes' => 'required|array|min:2',
        ]);

        if ($validator->fails()) {
            return json_fail('参数错误', $validator->errors()->first());
        }

        try {
            // 模拟更新节点配置
            return json_success('节点配置更新成功');
        } catch (\Exception $e) {
            return json_fail('节点配置更新失败');
        }
    }

    /**
     * 功能: 获取项目类型列表
     * 请求参数: 无
     * 返回参数:
     * - 使用 `json_success(msg, data)` 成功结构或 `json_fail(msg)` 失败结构
     * 接口: GET /workflows/case-types
     */
    public function getCaseTypes()
    {
        try {
            $caseTypes = [
                ['value' => 1, 'label' => '发明专利'],
                ['value' => 2, 'label' => '实用新型'],
                ['value' => 3, 'label' => '外观设计'],
                ['value' => 4, 'label' => '商标注册'],
                ['value' => 5, 'label' => '版权登记'],
                ['value' => 6, 'label' => '域名注册'],
            ];

            return json_success('获取成功', $caseTypes);
        } catch (\Exception $e) {
            return json_fail('获取项目类型失败');
        }
    }

    /**
     * 功能: 获取可分配用户列表（按部门分组与排序）
     * 请求参数: 无
     * 返回参数:
     * - 使用 `json_success(msg, data)` 成功结构或 `json_fail(msg)` 失败结构
     * - data(array): 分组列表，每项包含 `label` 与 `options(label, value)`
     * 接口: GET /workflows/assignable-users
     */
    public function getAssignableUsers()
    {
        try {
            // 步骤说明：检查用户表 -> 查询启用用户并按部门/姓名排序 -> 按部门分组 -> 组装下拉选项
            // 检查表是否存在
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return json_fail('用户表不存在，请先创建用户数据');
            }

            // 从数据库获取启用的用户，按部门分组
            $users = DB::table('users')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->select(
                    'users.id', 
                    'users.username', 
                    'users.real_name', 
                    'users.position',
                    'departments.department_name'
                )
                ->where('users.status', 1) // 只获取启用的用户
                ->whereNull('users.deleted_at') // 排除软删除的用户
                ->orderBy('departments.department_name')
                ->orderBy('users.real_name')
                ->get();

            if ($users->isEmpty()) {
                return json_fail('系统中暂无可分配的用户，请先添加用户数据');
            }

            // 按部门分组
            $groupedUsers = $users->groupBy('department_name');
            $userGroups = [];

            foreach ($groupedUsers as $departmentName => $departmentUsers) {
                $options = [];
                foreach ($departmentUsers as $user) {
                    $displayName = $user->real_name ?: $user->username;
                    if ($user->position) {
                        $displayName .= ' (' . $user->position . ')';
                    }
                    
                    $options[] = [
                        'label' => $displayName,
                        'value' => (int)$user->id  // 确保返回整数类型的ID
                    ];
                }
                
                if (!empty($options)) {
                    $userGroups[] = [
                        'label' => $departmentName ?: '未分组人员',
                        'options' => $options
                    ];
                }
            }

            return json_success('获取成功', $userGroups);
        } catch (\Exception $e) {
            \Log::error('获取可分配用户失败: ' . $e->getMessage());
            return json_fail('获取可分配用户失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 确保工作流数据存在（无则自动初始化）
     * 请求参数: 无
     * 返回参数: 无
     * 接口: 无接口
     */
    private function ensureWorkflowsExist()
    {
        // 步骤说明：统计工作流数量 -> 若为0则触发Seeder -> 异常记录不阻断流程
        $workflowCount = Workflow::count();
        if ($workflowCount === 0) {
            // 如果没有工作流数据，则运行 Seeder
            try {
                Artisan::call('db:seed', ['--class' => 'Database\Seeders\WorkflowSeeder']);
            } catch (\Exception $e) {
                // 如果 Seeder 失败，记录错误但不中断程序
                \Log::error('工作流Seeder执行失败: ' . $e->getMessage());
            }
        }
    }

    /**
     * 功能: 获取模拟工作流数据（已弃用，现改从数据库获取）
     * 请求参数: 无
     * 返回参数:
     * - data(array): 工作流基本字段数组
     * 接口: 无接口
     */
    private function getMockWorkflows()
    {
        // 步骤说明：改为从数据库获取启用状态的工作流，并映射为简化结构
        // 改为从数据库获取
        $workflows = Workflow::where('status', 1)->get();
        return $workflows->map(function ($workflow) {
            return [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'code' => $workflow->code,
                'caseType' => $workflow->case_type,
                'description' => $workflow->description,
                'isValid' => $workflow->status == 1,
                'nodes' => $workflow->nodes ?? []
            ];
        })->toArray();
    }
}
