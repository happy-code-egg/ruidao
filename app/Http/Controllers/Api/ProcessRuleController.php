<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessRuleController extends Controller
{
    /**
     * 获取流程规则列表
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
     * 获取流程规则详情
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
     * 创建流程规则
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
     * 更新流程规则
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
     * 删除流程规则
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
     * 切换流程规则状态
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
     * 获取处理事项树形数据
     * 根据项目类别 -> 国家 -> 处理事项名称的层级结构
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
     * 获取规则类型
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
     * 获取项目类型
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
     * 获取业务类型
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
     * 获取申请类型
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
     * 获取国家地区
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
     * 获取处理事项类型
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
     * 根据处理事项ID获取详情（用于右侧概览表格）
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
     * 根据条件获取处理事项规则
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
     * 获取处理事项状态列表
     * 根据处理事项ID或名称获取相关状态
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
     * 获取用户列表（用于固定人员选择）
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
