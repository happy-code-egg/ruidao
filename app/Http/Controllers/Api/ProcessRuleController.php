<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 流程规则控制器
 *
 * 功能:
 * - 管理与查询流程规则（条件、动作、优先级、启用状态等）
 * - 提供列表、详情、创建、更新、删除、状态切换以及各类选项获取接口
 * - 支持按处理事项、项目类型、国家、申请类型等维度筛选
 *
 * 路由说明:
 * - GET    /api/process-rules                          列表 index
 * - POST   /api/process-rules                          创建 store
 * - GET    /api/process-rules/{id}                     详情 show
 * - PUT    /api/process-rules/{id}                     更新 update
 * - DELETE /api/process-rules/{id}                     删除 destroy
 * - PUT    /api/process-rules/{id}/toggle-status       切换状态 toggleStatus
 * - GET    /api/process-rules/process-item-tree        处理事项树 getProcessItemTree
 * - GET    /api/process-rules/process-items            处理事项列表（同树）getProcessItemTree
 * - GET    /api/process-rules/process-item-detail/{id} 处理事项详情 getProcessItemDetail
 * - GET    /api/process-rules/process-item-rules       处理事项规则 getProcessItemRules
 * - GET    /api/process-rules/rule-types               规则类型 getRuleTypes
 * - GET    /api/process-rules/case-types               项目类型 getCaseTypes
 * - GET    /api/process-rules/business-types           业务类型 getBusinessTypes
 * - GET    /api/process-rules/application-types        申请类型 getApplicationTypes
 * - GET    /api/process-rules/countries                国家地区 getCountries
 * - GET    /api/process-rules/process-item-types       处理事项类型 getProcessItemTypes
 * - GET    /api/process-rules/process-statuses         处理事项状态 getProcessStatuses
 * - GET    /api/process-rules/users                    用户列表 getUsers
 *
 * 统一响应:
 * - 成功: json_success / json_page（或 response()->json({code,msg,data})）
 * - 失败: json_fail（或 response()->json 的错误码）
 *
 * 依赖:
 * - 模型 App\Models\ProcessRule
 * - Facades: Log, DB, Schema
 * - 鉴权: auth()（用于记录创建/更新人）
 */
class ProcessRuleController extends Controller
{
    /**
     * 功能: 获取流程规则列表，支持多条件筛选与分页
     * 路由说明:
     * - GET /api/process-rules
     * 请求参数:
     * - keyword string 可空，名称/描述模糊匹配
     * - ruleName string 可空，规则名称模糊匹配
     * - ruleType string 可空，映射到内部类型（自动生成/提醒通知/状态变更）
     * - caseType string 可空，映射到项目类型（商标/专利/版权）
     * - country string 可空，conditions.country JSON 包含匹配
     * - applicationType int 可空，application_type_id 精确匹配
     * - status int 可空，状态精确匹配
     * - isEffective boolean 可空，映射为 status 1/0
     * - page int 可空，默认1
     * - limit int 可空，默认15
     * 返回参数:
     * - json_page(list,total,message) 其中 list 为格式化后的字段
     * 异常处理:
     * - 捕获异常，返回 json_fail 并附带错误信息
     * 内部说明:
     * - 使用 with('creator') 预加载创建者信息
     */
    public function index(Request $request)
    {
        try {
            $query = ProcessRule::with('creator')->orderBy('priority', 'asc')->orderBy('created_at', 'desc');
            
            // 搜索条件
            if ($request->has('keyword') && $request->keyword) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }
            
            // 规则名称筛选
            if ($request->has('ruleName') && $request->ruleName) {
                $query->where('name', 'like', "%{$request->ruleName}%");
            }
            
            // 规则类型筛选
            if ($request->has('ruleType') && $request->ruleType) {
                $ruleTypeMap = [
                    '自动生成' => 1,
                    '自动分配' => 1,
                    '提醒通知' => 2,
                    '状态变更' => 3
                ];
                if (isset($ruleTypeMap[$request->ruleType])) {
                    $query->where('rule_type', $ruleTypeMap[$request->ruleType]);
                }
            }
            
            // 项目类型筛选
            if ($request->has('caseType') && $request->caseType) {
                $caseTypeMap = [
                    '商标' => 1,
                    '专利' => 2,
                    '版权' => 3
                ];
                if (isset($caseTypeMap[$request->caseType])) {
                    $query->where('case_type_id', $caseTypeMap[$request->caseType]);
                }
            }
            
            // 国家筛选
            if ($request->has('country') && $request->country) {
                $query->whereJsonContains('conditions->country', $request->country);
            }
            
            // 申请类型筛选
            if ($request->has('applicationType') && $request->applicationType) {
                $query->where('application_type_id', $request->applicationType);
            }
            
            // 状态筛选
            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('isEffective') && $request->isEffective !== '') {
                $isEffective = $request->isEffective === 'true' || $request->isEffective === true;
                $query->where('status', $isEffective ? 1 : 0);
            }
            
            // 分页
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);
            $total = $query->count();
            
            $rules = $query->offset(($page - 1) * $limit)
                          ->limit($limit)
                          ->get();
            
            // 格式化数据以匹配前端期望的格式
            $formattedRules = $rules->map(function ($rule, $index) use ($page, $limit) {
                $ruleTypeMap = [
                    'add_process' => '新增处理事项',
                    'update_process' => '更新处理事项',
                    'update_status' => '更新项目状态'
                ];

                $businessTypeMap = ['申请' => '申请', '维护' => '维护', '诉讼' => '诉讼'];
                $caseTypeMap = ['专利' => '专利', '商标' => '商标', '版权' => '版权'];
                
                return [
                    'id' => $rule->id,
                    'sequence' => ($page - 1) * $limit + $index + 1,
                    'processItem' => $rule->name,
                    'country' => $rule->country ?? '全球',
                    'applicationType' => $rule->application_type ?? '通用',
                    'generateOrComplete' => $rule->generate_or_complete ?? '生成',
                    'ruleName' => $rule->name,
                    'ruleType' => $ruleTypeMap[$rule->rule_type] ?? $rule->rule_type,
                    'ruleDescription' => $rule->description,
                    'isEffective' => $rule->is_effective,
                    'updater' => $rule->updated_by ?? ($rule->creator ? $rule->creator->real_name : '系统'),
                    'updateTime' => $rule->updated_at ? $rule->updated_at->format('Y-m-d H:i:s') : '',
                    'caseType' => $rule->case_type ?? '通用',
                    'businessType' => $rule->business_type ?? '通用',
                    'processItemType' => $rule->process_item_type ?? '内部处理',
                    'created_at' => $rule->created_at ? $rule->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $rule->updated_at ? $rule->updated_at->format('Y-m-d H:i:s') : '',
                    'priority' => $rule->priority,
                    'conditions' => $rule->conditions,
                    'actions' => $rule->actions
                ];
            });

            return json_page($formattedRules, $total, '获取成功');
        } catch (\Exception $e) {
            return json_fail('获取流程规则列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取流程规则详情
     * 路由说明:
     * - GET /api/process-rules/{id}
     * 请求参数:
     * - id path 必填
     * 返回参数:
     * - 单条规则详情（含 creator 关联）
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function show($id)
    {
        try {
            $rule = ProcessRule::with('creator')->findOrFail($id);
            
            return json_success('获取成功', $rule);
        } catch (\Exception $e) {
            return json_fail('获取流程规则详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 创建流程规则
     * 路由说明:
     * - POST /api/process-rules
     * 请求参数:
     * - name string 必填
     * - description string 可空
     * - rule_type string 可空
     * - process_item_id int 可空
     * - case_type/business_type/application_type/country/process_item_type string 可空
     * - conditions/actions array 可空
     * - generate_or_complete string 可空，枚举 generate|complete
     * - processor/fixed_personnel string 可空
     * - is_assign_case boolean 可空
     * - internal_deadline/customer_deadline/official_deadline/complete_date array 可空
     * - status int 可空，0/1
     * - priority/sort_order int 可空
     * - is_effective boolean 可空
     * 返回参数:
     * - 创建成功后的规则对象
     * 异常处理:
     * - 验证失败/其它异常记录日志并返回 json_fail
     * 内部说明:
     * - 使用 auth() 记录创建/更新人相关字段
     */
    public function store(Request $request)
    {
        try {
            // 记录请求数据用于调试
            Log::info('创建流程规则请求数据: ', $request->all());

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'rule_type' => 'nullable|string',
                'process_item_id' => 'nullable|integer',
                'case_type' => 'nullable|string|max:100',
                'business_type' => 'nullable|string|max:100',
                'application_type' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'process_item_type' => 'nullable|string|max:100',
                'conditions' => 'nullable|array',
                'actions' => 'nullable|array',
                'generate_or_complete' => 'nullable|string|in:generate,complete',
                'processor' => 'nullable|string|max:100',
                'fixed_personnel' => 'nullable|string|max:100',
                'is_assign_case' => 'nullable|boolean',
                'internal_deadline' => 'nullable|array',
                'customer_deadline' => 'nullable|array',
                'official_deadline' => 'nullable|array',
                'complete_date' => 'nullable|array',
                'status' => 'nullable|integer|in:0,1',
                'priority' => 'nullable|integer',
                'is_effective' => 'nullable|boolean',
                'sort_order' => 'nullable|integer'
            ]);

            $data['created_by'] = auth()->id() ?? 1; // 默认为管理员ID
            $data['updated_by_id'] = auth()->id() ?? 1; // 默认为管理员ID
            $data['updated_by'] = auth()->check() ? (auth()->user()->real_name ?? auth()->user()->name ?? '系统管理员') : '系统管理员';

            $rule = ProcessRule::create($data);

            return json_success('流程规则创建成功', $rule);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('流程规则创建验证失败: ' . json_encode($e->errors()));
            return json_fail('流程规则创建失败: ' . json_encode($e->errors()));
        } catch (\Exception $e) {
            Log::error('流程规则创建失败: ' . $e->getMessage());
            return json_fail('流程规则创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 更新流程规则
     * 路由说明:
     * - PUT /api/process-rules/{id}
     * 请求参数:
     * - id path 必填
     * - 其余字段同创建接口
     * 返回参数:
     * - 更新后的规则对象
     * 异常处理:
     * - 验证失败/其它异常记录日志并返回 json_fail
     */
    public function update(Request $request, $id)
    {
        try {
            $rule = ProcessRule::findOrFail($id);

            // 记录请求数据用于调试
            Log::info('更新流程规则请求数据: ', $request->all());

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'rule_type' => 'nullable|string',
                'process_item_id' => 'nullable|integer',
                'case_type' => 'nullable|string|max:100',
                'business_type' => 'nullable|string|max:100',
                'application_type' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'process_item_type' => 'nullable|string|max:100',
                'conditions' => 'nullable|array',
                'actions' => 'nullable|array',
                'generate_or_complete' => 'nullable|string|in:generate,complete',
                'processor' => 'nullable|string|max:100',
                'fixed_personnel' => 'nullable|string|max:100',
                'is_assign_case' => 'nullable|boolean',
                'internal_deadline' => 'nullable|array',
                'customer_deadline' => 'nullable|array',
                'official_deadline' => 'nullable|array',
                'complete_date' => 'nullable|array',
                'status' => 'nullable|integer|in:0,1',
                'priority' => 'nullable|integer',
                'is_effective' => 'nullable|boolean',
                'sort_order' => 'nullable|integer'
            ]);

            $data['updated_by_id'] = auth()->id() ?? 1; // 默认为管理员ID
            $data['updated_by'] = auth()->check() ? (auth()->user()->real_name ?? auth()->user()->name ?? '系统管理员') : '系统管理员';

            $rule->update($data);

            return json_success('流程规则更新成功', $rule);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('流程规则更新验证失败: ' . json_encode($e->errors()));
            return json_fail('流程规则更新失败: ' . json_encode($e->errors()));
        } catch (\Exception $e) {
            Log::error('流程规则更新失败: ' . $e->getMessage());
            return json_fail('流程规则更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 删除流程规则
     * 路由说明:
     * - DELETE /api/process-rules/{id}
     * 请求参数:
     * - id path 必填
     * 返回参数:
     * - 成功消息
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function destroy($id)
    {
        try {
            $rule = ProcessRule::findOrFail($id);
            $rule->delete();
            
            return json_success('流程规则删除成功');
        } catch (\Exception $e) {
            return json_fail('流程规则删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 切换流程规则的启用状态
     * 路由说明:
     * - PUT /api/process-rules/{id}/toggle-status
     * 请求参数:
     * - id path 必填
     * 返回参数:
     * - 更新后的规则对象
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function toggleStatus($id)
    {
        try {
            $rule = ProcessRule::findOrFail($id);
            $rule->status = $rule->status == 1 ? 0 : 1;
            $rule->updated_by = auth()->id();
            $rule->save();
            
            return json_success('流程规则状态更新成功', $rule);
        } catch (\Exception $e) {
            return json_fail('流程规则状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取处理事项的树形结构数据
     * 路由说明:
     * - GET /api/process-rules/process-item-tree
     * - GET /api/process-rules/process-items（同上）
     * 请求参数:
     * - 无
     * 返回参数:
     * - 树形结构：case_type -> country -> process_item
     * 异常处理:
     * - 记录日志并返回 json_fail
     * 内部说明:
     * - 依赖 \App\Models\ProcessInformation 数据
     */
    public function getProcessItemTree()
    {
        try {
            // 从处理事项信息表获取数据
            $processInformations = \App\Models\ProcessInformation::where('is_valid', 1)
                ->select('case_type', 'country', 'process_name', 'id')
                ->orderBy('case_type')
                ->orderBy('process_name')
                ->get();

            // 构建树形结构：项目类别 -> 国家 -> 处理事项名称
            $tree = [];
            $caseTypeMap = [];

            foreach ($processInformations as $item) {
                // 解析country字段（JSON格式）
                $countries = $item->country;
                if (is_string($countries)) {
                    $countries = json_decode($countries, true);
                }
                if (!is_array($countries)) {
                    $countries = [$item->country]; // 如果不是数组，当作单个国家处理
                }

                // 一级：项目类别
                if (!isset($caseTypeMap[$item->case_type])) {
                    $caseTypeId = count($tree) + 1;
                    $caseTypeMap[$item->case_type] = $caseTypeId;
                    $tree[] = [
                        'id' => $caseTypeId,
                        'label' => $item->case_type,
                        'icon' => 'el-icon-folder',
                        'type' => 'case_type',
                        'value' => $item->case_type,
                        'children' => []
                    ];
                }

                $caseTypeIndex = array_search($caseTypeMap[$item->case_type], array_column($tree, 'id'));

                // 为每个国家创建节点
                foreach ($countries as $country) {
                    // 二级：国家
                    $countryExists = false;
                    $countryIndex = 0;
                    foreach ($tree[$caseTypeIndex]['children'] as $index => $countryNode) {
                        if ($countryNode['value'] === $country && $countryNode['parent_case_type'] === $item->case_type) {
                            $countryExists = true;
                            $countryIndex = $index;
                            break;
                        }
                    }

                    if (!$countryExists) {
                        $countryId = ($caseTypeMap[$item->case_type] * 100) + count($tree[$caseTypeIndex]['children']) + 1;
                        $tree[$caseTypeIndex]['children'][] = [
                            'id' => $countryId,
                            'label' => $country,
                            'icon' => 'el-icon-folder',
                            'type' => 'country',
                            'value' => $country,
                            'parent_case_type' => $item->case_type,
                            'children' => []
                        ];
                        $countryIndex = count($tree[$caseTypeIndex]['children']) - 1;
                    }

                    // 三级：处理事项名称
                    $processId = ($countryId * 100) + count($tree[$caseTypeIndex]['children'][$countryIndex]['children']) + 1;
                    $tree[$caseTypeIndex]['children'][$countryIndex]['children'][] = [
                        'id' => $processId,
                        'label' => $item->process_name,
                        'icon' => 'el-icon-document',
                        'type' => 'process_item',
                        'value' => $item->process_name,
                        'process_item_id' => $item->id,
                        'parent_case_type' => $item->case_type,
                        'parent_country' => $country
                    ];
                }
            }

            return json_success('获取成功', $tree);
        } catch (\Exception $e) {
            Log::error('获取处理事项树失败: ' . $e->getMessage());
            return json_fail('获取处理事项失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取规则类型选项
     * 路由说明:
     * - GET /api/process-rules/rule-types
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getRuleTypes()
    {
        try {
            $ruleTypes = [
                ['value' => 1, 'label' => '自动生成'],
                ['value' => 2, 'label' => '提醒通知'],
                ['value' => 3, 'label' => '状态变更']
            ];

            return json_success('获取成功', $ruleTypes);
        } catch (\Exception $e) {
            return json_fail('获取规则类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取项目类型选项
     * 路由说明:
     * - GET /api/process-rules/case-types
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getCaseTypes()
    {
        try {
            $caseTypes = [
                ['value' => 1, 'label' => '商标'],
                ['value' => 2, 'label' => '专利'],
                ['value' => 3, 'label' => '版权']
            ];

            return json_success('获取成功', $caseTypes);
        } catch (\Exception $e) {
            return json_fail('获取项目类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取业务类型选项
     * 路由说明:
     * - GET /api/process-rules/business-types
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getBusinessTypes()
    {
        try {
            $businessTypes = [
                ['value' => 1, 'label' => '申请'],
                ['value' => 2, 'label' => '维护'],
                ['value' => 3, 'label' => '诉讼']
            ];

            return json_success('获取成功', $businessTypes);
        } catch (\Exception $e) {
            return json_fail('获取业务类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取申请类型选项
     * 路由说明:
     * - GET /api/process-rules/application-types
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getApplicationTypes()
    {
        try {
            $applicationTypes = [
                ['value' => 1, 'label' => '发明专利'],
                ['value' => 2, 'label' => '实用新型'],
                ['value' => 3, 'label' => '外观设计'],
                ['value' => 4, 'label' => '商品商标'],
                ['value' => 5, 'label' => '服务商标']
            ];

            return json_success('获取成功', $applicationTypes);
        } catch (\Exception $e) {
            return json_fail('获取申请类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取国家地区选项
     * 路由说明:
     * - GET /api/process-rules/countries
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getCountries()
    {
        try {
            $countries = [
                ['value' => 1, 'label' => '中国'],
                ['value' => 2, 'label' => '美国'],
                ['value' => 3, 'label' => '欧盟'],
                ['value' => 4, 'label' => '日本'],
                ['value' => 5, 'label' => '韩国']
            ];

            return json_success('获取成功', $countries);
        } catch (\Exception $e) {
            return json_fail('获取国家地区失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取处理事项类型选项
     * 路由说明:
     * - GET /api/process-rules/process-item-types
     * 返回参数:
     * - [{value,label}]
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getProcessItemTypes()
    {
        try {
            $processItemTypes = [
                ['value' => 'internal', 'label' => '内部处理'],
                ['value' => 'external', 'label' => '对外处理'],
                ['value' => 'client', 'label' => '客户沟通'],
                ['value' => 'official', 'label' => '官方沟通']
            ];

            return json_success('获取成功', $processItemTypes);
        } catch (\Exception $e) {
            return json_fail('获取处理事项类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 根据处理事项ID获取简要详情（右侧概览）
     * 路由说明:
     * - GET /api/process-rules/process-item-detail/{id}
     * 请求参数:
     * - id path 必填
     * 返回参数:
     * - 概览字段：sequence、processItem、caseType、businessType、applicationType、country、processItemType
     * 异常处理:
     * - 捕获异常返回 json_fail
     */
    public function getProcessItemDetail($processItemId)
    {
        try {
            $processItem = \App\Models\ProcessInformation::find($processItemId);
            
            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            $detail = [
                'sequence' => 1,
                'processItem' => $processItem->process_name,
                'caseType' => $processItem->case_type,
                'businessType' => $processItem->business_type,
                'applicationType' => is_array($processItem->application_type) 
                    ? implode(', ', $processItem->application_type) 
                    : ($processItem->application_type ?? '通用'),
                'country' => $processItem->country,
                'processItemType' => $processItem->process_type ?? '内部处理'
            ];

            return json_success('获取成功', $detail);
        } catch (\Exception $e) {
            Log::error('获取处理事项详情失败: ' . $e->getMessage());
            return json_fail('获取处理事项详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 根据条件获取处理事项相关规则列表
     * 路由说明:
     * - GET /api/process-rules/process-item-rules
     * 请求参数:
     * - process_item_id int 可空，优先按处理事项ID筛选
     * - case_type string 可空
     * - country string 可空
     * - process_item string 可空，处理事项名称模糊匹配
     * 返回参数:
     * - 规则列表：格式化字段包含 id/sequence/processItem/country/applicationType/generateOrComplete/ruleName/ruleType/ruleDescription/isEffective/updater/updateTime/priority/conditions/actions
     * 异常处理:
     * - 记录日志并返回 json_fail
     */
    public function getProcessItemRules(Request $request)
    {
        try {
            $query = ProcessRule::with(['creator', 'updater']);
            
            // 优先根据处理事项ID筛选（这是最精确的筛选条件）
            if ($request->has('process_item_id') && $request->process_item_id) {
                $query->where('process_item_id', $request->process_item_id);
            } else {
                // 如果没有process_item_id，则使用其他条件组合筛选
                if ($request->has('case_type') && $request->case_type) {
                    $query->where('case_type', $request->case_type);
                }
                
                if ($request->has('country') && $request->country) {
                    $query->where('country', $request->country);
                }
                
                // 根据处理事项名称筛选
                if ($request->has('process_item') && $request->process_item) {
                    $query->where('name', 'like', '%' . $request->process_item . '%');
                }
            }

            $rules = $query->orderBy('priority', 'asc')
                          ->orderBy('sort_order', 'asc')
                          ->get();

            // 格式化数据
            $formattedRules = $rules->map(function ($rule, $index) {
                $ruleTypeMap = [
                    'add_process' => '新增处理事项',
                    'update_process' => '更新处理事项',
                    'update_status' => '更新项目状态'
                ];

                return [
                    'id' => $rule->id,
                    'sequence' => $index + 1,
                    'processItem' => $rule->name,
                    'country' => $rule->country ?? '全球',
                    'applicationType' => $rule->application_type ?? '通用',
                    'generateOrComplete' => $rule->generate_or_complete ?? '生成',
                    'ruleName' => $rule->name,
                    'ruleType' => $ruleTypeMap[$rule->rule_type] ?? $rule->rule_type,
                    'ruleDescription' => $rule->description,
                    'isEffective' => $rule->is_effective,
                    'updater' => $rule->updated_by ?? ($rule->creator ? $rule->creator->real_name : '系统'),
                    'updateTime' => $rule->updated_at ? $rule->updated_at->format('Y-m-d H:i:s') : '',
                    'priority' => $rule->priority,
                    'conditions' => $rule->conditions,
                    'actions' => $rule->actions
                ];
            });

            return json_success('获取成功', $formattedRules);
        } catch (\Exception $e) {
            Log::error('获取处理事项规则失败: ' . $e->getMessage());
            return json_fail('获取处理事项规则失败: ' . $e->getMessage());
        }
    }

    /**
     * 功能: 获取处理事项状态选项列表
     * 路由说明:
     * - GET /api/process-rules/process-statuses
     * 请求参数:
     * - process_item_id int 可空
     * - process_item_name string 可空
     * 返回参数:
     * - [{id,label,value}]，若表不存在或无数据，返回默认状态集合
     * 异常处理:
     * - 出错时记录日志并返回默认状态集合
     * 内部说明:
     * - 使用 Schema 检查表存在性，优雅降级
     */
    public function getProcessStatuses(Request $request)
    {
        try {
            // 检查process_statuses表是否存在
            if (!Schema::hasTable('process_statuses')) {
                // 如果表不存在，返回默认状态
                $defaultStatuses = [
                    ['id' => 1, 'label' => '待处理', 'value' => 'pending'],
                    ['id' => 2, 'label' => '处理中', 'value' => 'processing'],
                    ['id' => 3, 'label' => '已完成', 'value' => 'completed'],
                    ['id' => 4, 'label' => '已暂停', 'value' => 'paused'],
                    ['id' => 5, 'label' => '已取消', 'value' => 'cancelled']
                ];

                return response()->json([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => $defaultStatuses
                ]);
            }

            $query = DB::table('process_statuses')->where('is_valid', 1);

            // 如果指定了处理事项，可以根据业务逻辑筛选相关状态
            if ($request->has('process_item_id') && $request->process_item_id) {
                // 这里可以根据处理事项ID获取相关状态
                // 暂时返回所有有效状态
            }

            if ($request->has('process_item_name') && $request->process_item_name) {
                // 这里可以根据处理事项名称获取相关状态
                // 暂时返回所有有效状态
            }

            $statuses = $query->select('id', 'status_name as label', 'status_code as value')
                ->orderBy('sort_order')
                ->get();

            // 如果数据库中没有数据，返回默认状态
            if ($statuses->isEmpty()) {
                $defaultStatuses = [
                    ['id' => 1, 'label' => '待处理', 'value' => 'pending'],
                    ['id' => 2, 'label' => '处理中', 'value' => 'processing'],
                    ['id' => 3, 'label' => '已完成', 'value' => 'completed'],
                    ['id' => 4, 'label' => '已暂停', 'value' => 'paused'],
                    ['id' => 5, 'label' => '已取消', 'value' => 'cancelled']
                ];

                return response()->json([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => $defaultStatuses
                ]);
            }

            return response()->json([
                'code' => 0,
                'msg' => 'success',
                'data' => $statuses
            ]);
        } catch (\Exception $e) {
            Log::error('获取处理事项状态列表失败: ' . $e->getMessage());

            // 出错时返回默认状态
            $defaultStatuses = [
                ['id' => 1, 'label' => '待处理', 'value' => 'pending'],
                ['id' => 2, 'label' => '处理中', 'value' => 'processing'],
                ['id' => 3, 'label' => '已完成', 'value' => 'completed'],
                ['id' => 4, 'label' => '已暂停', 'value' => 'paused'],
                ['id' => 5, 'label' => '已取消', 'value' => 'cancelled']
            ];

            return response()->json([
                'code' => 0,
                'msg' => 'success',
                'data' => $defaultStatuses
            ]);
        }
    }

    /**
     * 功能: 获取启用用户列表（用于固定人员选择）
     * 路由说明:
     * - GET /api/process-rules/users
     * 请求参数:
     * - department_id int 可空，按部门筛选
     * 返回参数:
     * - [{id,label,value,real_name,username,department_name}]
     * 异常处理:
     * - 出错时返回错误码并记录日志
     */
    public function getUsers(Request $request)
    {
        try {
            $query = DB::table('users')->where('status', 1);

            // 可以根据部门筛选
            if ($request->has('department_id') && $request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            $users = $query->select('id', 'real_name', 'username', 'department_id')
                ->orderBy('real_name')
                ->get();

            // 格式化数据
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'label' => $user->real_name . '(' . $user->username . ')',
                    'value' => $user->id,
                    'real_name' => $user->real_name,
                    'username' => $user->username,
                    'department_name' => '' // 暂时不获取部门名称
                ];
            });

            return response()->json([
                'code' => 0,
                'msg' => 'success',
                'data' => $formattedUsers
            ]);
        } catch (\Exception $e) {
            Log::error('获取用户列表失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1,
                'msg' => '获取用户列表失败',
                'data' => []
            ]);
        }
    }
}
