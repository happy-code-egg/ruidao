<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WorkflowConfigController extends Controller
{
    /**
     * 功能: 获取流程配置列表，支持分页、名称/代码/项目类型/状态筛选
     * 请求参数:
     * - page(int, 可选): 页码，默认1
     * - limit(int, 可选): 每页数量，默认10，最大100
     * - name(string, 可选): 名称模糊搜索
     * - code(string, 可选): 代码模糊搜索
     * - caseType(string, 可选): 项目类型过滤
     * - isValid(bool|string|int, 可选): 状态筛选，支持 true/'true'/1
     * 返回参数:
     * - JSON: {code, msg, data, count, page, limit}
     * - data(array): 格式化后的流程配置列表
     * 接口: GET /workflow-config/list
     */
    public function getList(Request $request)
    {
        try {
            // 步骤说明：解析参数 -> 构建查询 -> 应用筛选 -> 统计总数 -> 分页查询 -> 格式化输出
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));
            $name = $request->get('name', '');
            $code = $request->get('code', '');
            $caseType = $request->get('caseType', '');
            $isValid = $request->get('isValid', '');

            $query = Workflow::query();

            // 名称筛选
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }

            // 代码筛选
            if (!empty($code)) {
                $query->where('code', 'like', "%{$code}%");
            }

            // 项目类型筛选
            if (!empty($caseType)) {
                $query->where('case_type', $caseType);
            }

            // 状态筛选
            if ($isValid !== '' && $isValid !== null) {
                $status = ($isValid === 'true' || $isValid === true || $isValid === 1 || $isValid === '1') ? 1 : 0;
                $query->where('status', $status);
            }

            // 获取总数
            $total = $query->count();

            // 分页获取数据
            $workflows = $query->orderBy('id', 'asc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            // 格式化数据
            $tableData = $workflows->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'code' => $workflow->code,
                    'description' => $workflow->description,
                    'caseType' => $this->formatCaseType($workflow->case_type),
                    'case_type' => $workflow->case_type, // 原始值
                    'isValid' => $workflow->status == 1,
                    'status' => $workflow->status,
                    'nodes' => $this->formatNodes($workflow->nodes),
                    'nodeCount' => is_array($workflow->nodes) ? count($workflow->nodes) : 0,
                    'updateUser' => $workflow->updater ? $workflow->updater->name : '系统',
                    'updateTime' => $workflow->updated_at ? $workflow->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $tableData,
                'count' => $total,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取流程配置列表失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 功能: 获取指定流程配置详情
     * 请求参数:
     * - id(int, 必填): 路径参数，流程配置ID
     * 返回参数:
     * - JSON: {code, msg, data}
     * - data(object|null): 流程配置详情
     * 接口: GET /workflow-config/{id}
     */
    public function getDetail($id)
    {
        try {
            // 步骤说明：查找配置 -> 格式化关键字段 -> 返回详情
            $workflow = Workflow::find($id);
            
            if (!$workflow) {
                return response()->json([
                    'code' => 1,
                    'msg' => '流程配置不存在',
                    'data' => null
                ]);
            }

            $data = [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'code' => $workflow->code,
                'description' => $workflow->description,
                'caseType' => $this->formatCaseType($workflow->case_type),
                'case_type' => $workflow->case_type,
                'isValid' => $workflow->status == 1,
                'status' => $workflow->status,
                'nodes' => $this->formatNodes($workflow->nodes),
                'updateUser' => $workflow->updater ? $workflow->updater->name : '系统',
                'updateTime' => $workflow->updated_at ? $workflow->updated_at->format('Y-m-d H:i:s') : '',
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取流程配置详情失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 功能: 更新流程配置（名称/代码/描述/类型/状态/节点）
     * 请求参数:
     * - id(int, 必填): 路径参数，流程配置ID
     * - name(string, 可选): 名称
     * - code(string, 可选): 代码
     * - description(string, 可选): 描述
     * - caseType(string, 可选): 项目类型显示值（内部将解析为代码）
     * - isValid(boolean, 可选): 是否启用
     * - nodes(array, 可选): 节点配置数组
     * 返回参数:
     * - JSON: {code, msg}
     * 接口: PUT /workflow-config/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // 步骤说明：查找配置 -> 参数校验 -> 事务更新基本信息与节点 -> 保存并提交
            $workflow = Workflow::find($id);
            
            if (!$workflow) {
                return response()->json([
                    'code' => 1,
                    'msg' => '流程配置不存在'
                ]);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:200',
                'code' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
                'caseType' => 'sometimes|required|string|max:50',
                'isValid' => 'sometimes|required|boolean',
                'nodes' => 'sometimes|required|array',
                'nodes.*.name' => 'nullable|string',
                'nodes.*.type' => 'nullable|string',
                'nodes.*.required' => 'sometimes|boolean',
                'nodes.*.assignee' => 'nullable|array',
                'nodes.*.timeLimit' => 'nullable|integer|min:1',
                'nodes.*.description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 1,
                    'msg' => '参数验证失败：' . $validator->errors()->first()
                ]);
            }

            DB::beginTransaction();

            // 更新基本信息
            if ($request->has('name')) {
                $workflow->name = $request->name;
            }
            if ($request->has('code')) {
                $workflow->code = $request->code;
            }
            if ($request->has('description')) {
                $workflow->description = $request->description;
            }
            if ($request->has('caseType')) {
                $workflow->case_type = $this->parseCaseType($request->caseType);
            }
            if ($request->has('isValid')) {
                $workflow->status = $request->isValid ? 1 : 0;
            }
            if ($request->has('nodes')) {
                $workflow->nodes = $this->processNodes($request->nodes);
            }

            $workflow->updated_by = Auth::id() ?: 1;
            $workflow->save();

            DB::commit();

            return response()->json([
                'code' => 0,
                'msg' => '更新成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 1,
                'msg' => '更新流程配置失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 功能: 获取可分配的用户列表（按部门分组）
     * 请求参数: 无
     * 返回参数:
     * - JSON: {code, msg, data}
     * - data(array): [{label: 部门名, options: [{label: 用户名(职位), value: 用户ID}]}]
     * 接口: GET /workflow-config/assignable-users
     */
    public function getAssignableUsers()
    {
        try {
            // 步骤说明：联表查询部门与启用用户 -> 构建分组下拉 -> 无分组时回退为“所有用户”组
            $departments = Department::with(['users' => function($query) {
                $query->where('status', 1)
                      ->select('id', 'real_name', 'department_id', 'position');
            }])
            ->orderBy('sort_order')
            ->get();

            $userGroups = [];
            
            foreach ($departments as $department) {
                if ($department->users->count() > 0) {
                    $options = $department->users->map(function ($user) {
                        return [
                            'label' => $user->real_name . ($user->position ? " ({$user->position})" : ''),
                            'value' => $user->id
                        ];
                    })->toArray();

                    $userGroups[] = [
                        'label' => $department->department_name,
                        'options' => $options
                    ];
                }
            }

            // 如果没有部门分组，添加一个默认分组
            if (empty($userGroups)) {
                $users = User::where('status', 1)
                    ->select('id', 'real_name', 'position')
                    ->orderBy('real_name')
                    ->get();

                if ($users->count() > 0) {
                    $userGroups[] = [
                        'label' => '所有用户',
                        'options' => $users->map(function ($user) {
                            return [
                                'label' => $user->real_name . ($user->position ? " ({$user->position})" : ''),
                                'value' => $user->id
                            ];
                        })->toArray()
                    ];
                }
            }

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $userGroups
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取用户列表失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 功能: 获取项目类型列表
     * 请求参数: 无
     * 返回参数:
     * - JSON: {code, msg, data}
     * - data(array): [{label, value}]
     * 接口: GET /workflow-config/case-types
     */
    public function getCaseTypes()
    {
        try {
            $caseTypes = [
                ['label' => '专利', 'value' => 'patent'],
                ['label' => '商标', 'value' => 'trademark'],
                ['label' => '版权', 'value' => 'copyright'],
                ['label' => '合同', 'value' => 'contract'],
                ['label' => '科服', 'value' => 'tech_service'],
                ['label' => '通用', 'value' => 'general'],
                ['label' => '财务', 'value' => 'finance'],
                ['label' => '提成', 'value' => 'commission'],
            ];

            return response()->json([
                'code' => 0,
                'msg' => '获取成功',
                'data' => $caseTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取项目类型失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 功能: 批量启用/禁用流程配置
     * 请求参数:
     * - ids(array<int>, 必填): 配置ID列表
     * - action(string, 必填): 操作类型，支持 enable/disable
     * 返回参数:
     * - JSON: {code, msg}
     * 接口: POST /workflow-config/batch-update
     */
    public function batchUpdate(Request $request)
    {
        try {
            // 步骤说明：校验参数 -> 计算目标状态 -> 批量更新 -> 返回更新统计
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:workflows,id',
                'action' => 'required|string|in:enable,disable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 1,
                    'msg' => '参数验证失败：' . $validator->errors()->first()
                ]);
            }

            $status = $request->action === 'enable' ? 1 : 0;
            $updated = Workflow::whereIn('id', $request->ids)
                ->update([
                    'status' => $status,
                    'updated_by' => Auth::id() ?: 1,
                    'updated_at' => now()
                ]);

            return response()->json([
                'code' => 0,
                'msg' => "批量{$request->action}成功，共更新{$updated}个流程配置"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '批量更新失败：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 功能: 验证流程配置节点的完整性与合理性
     * 请求参数:
     * - id(int, 必填): 路径参数，流程配置ID
     * 返回参数:
     * - JSON: {code, msg, data}
     * - data: {valid(bool), errors(array<string>)}
     * 接口: POST /workflow-config/{id}/validate
     */
    public function validateWorkflow($id)
    {
        try {
            // 步骤说明：查找配置 -> 校验节点数组存在 -> 遍历各节点检查必需处理人与时限 -> 汇总错误并返回
            $workflow = Workflow::find($id);
            
            if (!$workflow) {
                return response()->json([
                    'code' => 1,
                    'msg' => '流程配置不存在'
                ]);
            }

            $errors = [];
            $nodes = $workflow->nodes;

            if (!is_array($nodes) || empty($nodes)) {
                $errors[] = '流程节点配置为空';
            } else {
                foreach ($nodes as $index => $node) {
                    $nodeIndex = $index + 1;
                    $nodeName = $node['name'] ?? '';
                    
                    // 空节点跳过验证（自动通过节点）
                    if (empty($nodeName)) {
                        continue;
                    }
                    
                    // 检查必需节点的处理人配置
                    if (isset($node['required']) && $node['required'] && 
                        (!isset($node['assignee']) || empty($node['assignee']))) {
                        $errors[] = "节点{$nodeIndex}「{$nodeName}」：必需节点未配置处理人";
                    }
                    
                    // 检查处理时限
                    if (isset($node['timeLimit']) && $node['timeLimit'] <= 0) {
                        $errors[] = "节点{$nodeIndex}「{$nodeName}」：处理时限必须大于0";
                    }
                }
            }

            $isValid = empty($errors);

            return response()->json([
                'code' => 0,
                'msg' => $isValid ? '流程配置验证通过' : '流程配置验证失败',
                'data' => [
                    'valid' => $isValid,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '验证流程配置失败：' . $e->getMessage(),
                'data' => [
                    'valid' => false,
                    'errors' => ['系统异常：' . $e->getMessage()]
                ]
            ]);
        }
    }

    /**
     * 功能: 将内部类型代码映射为显示名称
     * 请求参数:
     * - caseType(string): 类型代码
     * 返回参数:
     * - string: 显示名称
     * 接口: 无接口（内部工具方法）
     */
    private function formatCaseType($caseType)
    {
        $typeMap = [
            'patent' => '专利',
            'trademark' => '商标',
            'copyright' => '版权',
            'contract' => '合同',
            'tech_service' => '科服',
            'general' => '通用',
            'finance' => '财务',
            'commission' => '提成',
            'assignment' => '配案',
            'review' => '核稿',
            'submission' => '递交',
            'update' => '更新',
            'payment' => '收付款',
        ];

        return $typeMap[$caseType] ?? $caseType;
    }

    /**
     * 功能: 将显示名称解析为内部类型代码
     * 请求参数:
     * - displayType(string): 显示名称
     * 返回参数:
     * - string: 类型代码
     * 接口: 无接口（内部工具方法）
     */
    private function parseCaseType($displayType)
    {
        $typeMap = [
            '专利' => 'patent',
            '商标' => 'trademark',
            '版权' => 'copyright',
            '合同' => 'contract',
            '科服' => 'tech_service',
            '通用' => 'general',
            '财务' => 'finance',
            '提成' => 'commission',
        ];

        return $typeMap[$displayType] ?? $displayType;
    }

    /**
     * 功能: 将数据库节点结构格式化为前端友好结构
     * 请求参数:
     * - nodes(array|mixed): 节点原始结构
     * 返回参数:
     * - array: 规范化节点数组 [{id,name,type,required,assignee,timeLimit,description,auto_pass,sort_order}]
     * 接口: 无接口（内部工具方法）
     */
    private function formatNodes($nodes)
    {
        if (!is_array($nodes)) {
            return [];
        }

        return array_map(function ($node) {
            return [
                'id' => $node['id'] ?? 0,
                'name' => $node['name'] ?? '',
                'type' => $node['type'] ?? 'manual',
                'required' => !($node['auto_pass'] ?? false), // auto_pass的反义
                'assignee' => $node['assignee_users'] ?? [],
                'timeLimit' => 24, // 默认24小时
                'description' => $node['name'] ?? '',
                'auto_pass' => $node['auto_pass'] ?? false,
                'sort_order' => $node['sort_order'] ?? 0,
            ];
        }, $nodes);
    }

    /**
     * 功能: 将前端提交的节点数据转换为数据库存储结构
     * 请求参数:
     * - nodes(array): 前端节点数组
     * 返回参数:
     * - array: 存储结构节点数组 [{id,name,type,auto_pass,assignee_users,sort_order}]
     * 接口: 无接口（内部工具方法）
     */
    private function processNodes($nodes)
    {
        return array_map(function ($node, $index) {
            return [
                'id' => $index + 1,
                'name' => $node['name'] ?? '',
                'type' => $node['type'] ?? 'manual',
                'auto_pass' => !($node['required'] ?? true), // required的反义
                'assignee_users' => $node['assignee'] ?? [],
                'sort_order' => $index + 1,
            ];
        }, $nodes, array_keys($nodes));
    }

    /**
     * 功能: 将流程配置重置为默认的8节点结构（首尾自动通过）
     * 请求参数:
     * - id(int, 必填): 路径参数，流程配置ID
     * 返回参数:
     * - JSON: {code, msg}
     * 接口: POST /workflow-config/{id}/reset
     */
    public function resetToDefault($id)
    {
        try {
            // 步骤说明：查找配置 -> 构建默认8节点 -> 写入并启用 -> 保存
            $workflow = Workflow::find($id);
            
            if (!$workflow) {
                return response()->json([
                    'code' => 1,
                    'msg' => '流程配置不存在'
                ]);
            }

            // 重置为默认的8节点结构
            $defaultNodes = [];
            for ($i = 0; $i < 8; $i++) {
                $defaultNodes[] = [
                    'id' => $i + 1,
                    'name' => $i === 0 ? '启动' : ($i === 7 ? '结束' : "节点" . ($i + 1)),
                    'type' => ($i === 0 || $i === 7) ? 'auto' : 'manual',
                    'auto_pass' => ($i === 0 || $i === 7),
                    'assignee_users' => [],
                    'sort_order' => $i + 1,
                ];
            }

            $workflow->nodes = $defaultNodes;
            $workflow->status = 1; // 启用
            $workflow->updated_by = Auth::id() ?: 1;
            $workflow->save();

            return response()->json([
                'code' => 0,
                'msg' => '重置成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '重置流程配置失败：' . $e->getMessage()
            ]);
        }
    }
}