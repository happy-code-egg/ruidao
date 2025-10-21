<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use App\Models\FileCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationRuleController extends Controller
{
    /**
     * 获取通知规则列表
     */
    public function index(Request $request)
    {
        try {
            $query = NotificationRule::with(['creator', 'updaterRelation', 'fileCategory'])
                ->orderBy('sort_order', 'asc')
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc');
            
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
                $query->where('rule_type', $request->ruleType);
            }
            
            // 文件分类筛选
            if ($request->has('file_category_id') && $request->file_category_id) {
                $query->where('file_category_id', $request->file_category_id);
            }
            
            // 状态筛选
            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('isEffective') && $request->isEffective !== '') {
                $isEffective = $request->isEffective === 'true' || $request->isEffective === true;
                $query->where('is_effective', $isEffective ? 1 : 0);
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
                return [
                    'id' => $rule->id,
                    'sequence' => ($page - 1) * $limit + $index + 1,
                    'ruleName' => $rule->name,
                    'ruleDescription' => $rule->description,
                    'ruleType' => $rule->rule_type_text,
                    'isEffective' => $rule->is_effective == 1,
                    'updater' => $rule->updater_relation ? $rule->updater_relation->real_name : ($rule->updater ?: 'System'),
                    'updateTime' => $rule->updated_at->format('Y-m-d H:i:s'),
                    'fileType' => $rule->fileCategory ? $rule->fileCategory->sub_category : '通用',
                    'mainCategory' => $rule->fileCategory ? $rule->fileCategory->main_category : '通用',
                    'subCategory' => $rule->fileCategory ? $rule->fileCategory->sub_category : '通用',
                    'created_at' => $rule->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $rule->updated_at->format('Y-m-d H:i:s'),
                    'priority' => $rule->priority,
                    'conditions' => $rule->conditions,
                    'actions' => $rule->actions
                ];
            });

            return json_page($formattedRules, $total, '获取成功');
        } catch (\Exception $e) {
            Log::error('获取通知规则列表失败: ' . $e->getMessage());
            return json_fail('获取通知规则列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取通知规则详情
     */
    public function show($id)
    {
        try {
            $rule = NotificationRule::with(['creator', 'updaterRelation', 'fileCategory'])->find($id);

            if (!$rule) {
                return json_fail('通知规则不存在');
            }

            return json_success('获取成功', [
                'id' => $rule->id,
                'name' => $rule->name,
                'description' => $rule->description,
                'rule_type' => $rule->rule_type,
                'file_category_id' => $rule->file_category_id,
                'conditions' => $rule->conditions,
                'actions' => $rule->actions,
                'is_config' => $rule->is_config,
                'process_item' => $rule->process_item,
                'process_status' => $rule->process_status,
                'is_upload' => $rule->is_upload,
                'transfer_target' => $rule->transfer_target,
                'attachment_config' => $rule->attachment_config,
                'processor' => $rule->processor,
                'fixed_personnel' => $rule->fixed_personnel,
                'internal_deadline' => $rule->internal_deadline,
                'customer_deadline' => $rule->customer_deadline,
                'official_deadline' => $rule->official_deadline,
                'complete_date' => $rule->complete_date,
                'is_effective' => $rule->is_effective,
                'priority' => $rule->priority,
                'status' => $rule->status,
                'created_at' => $rule->created_at,
                'updated_at' => $rule->updated_at,
            ]);
        } catch (\Exception $e) {
            Log::error('获取通知规则详情失败: ' . $e->getMessage());
            return json_fail('获取通知规则详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建通知规则
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:200',
                'description' => 'nullable|string',
                'rule_type' => 'required|string|max:50',
                'file_category_id' => 'nullable|integer|exists:file_descriptions,id',
            ]);

            // 处理其他字段
            $additionalData = $request->only([
                'conditions', 'actions', 'is_config', 'process_item', 'process_status',
                'is_upload', 'transfer_target', 'attachment_config', 'generated_filename',
                'processor', 'fixed_personnel', 'internal_deadline', 'customer_deadline',
                'official_deadline', 'internal_priority_deadline', 'customer_priority_deadline',
                'official_priority_deadline', 'internal_precheck_deadline', 'customer_precheck_deadline',
                'official_precheck_deadline', 'complete_date', 'is_effective', 'priority', 'sort_order'
            ]);

            $data = array_merge($data, $additionalData);
            $data['created_by'] = auth('api')->id();
            $data['status'] = $request->get('status', 1);
            
            $rule = NotificationRule::create($data);

            return json_success('创建成功', $rule);
        } catch (\Exception $e) {
            Log::error('创建通知规则失败: ' . $e->getMessage());
            return json_fail('创建通知规则失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新通知规则
     */
    public function update(Request $request, $id)
    {
        try {
            $rule = NotificationRule::find($id);
            if (!$rule) {
                return json_fail('通知规则不存在');
            }

            $data = $request->validate([
                'name' => 'required|string|max:200',
                'description' => 'nullable|string',
                'rule_type' => 'required|string|max:50',
                'file_category_id' => 'nullable|integer|exists:file_descriptions,id',
            ]);

            // 处理其他字段
            $additionalData = $request->only([
                'conditions', 'actions', 'is_config', 'process_item', 'process_status',
                'is_upload', 'transfer_target', 'attachment_config', 'generated_filename',
                'processor', 'fixed_personnel', 'internal_deadline', 'customer_deadline',
                'official_deadline', 'internal_priority_deadline', 'customer_priority_deadline',
                'official_priority_deadline', 'internal_precheck_deadline', 'customer_precheck_deadline',
                'official_precheck_deadline', 'complete_date', 'is_effective', 'priority', 'sort_order', 'status'
            ]);

            $data = array_merge($data, $additionalData);
            $data['updated_by'] = auth('api')->id();
            
            $rule->update($data);

            return json_success('更新成功', $rule);
        } catch (\Exception $e) {
            Log::error('更新通知规则失败: ' . $e->getMessage());
            return json_fail('更新通知规则失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除通知规则
     */
    public function destroy($id)
    {
        try {
            $rule = NotificationRule::find($id);
            if (!$rule) {
                return json_fail('通知规则不存在');
            }

            $rule->delete();
            return json_success('删除成功');
        } catch (\Exception $e) {
            Log::error('删除通知规则失败: ' . $e->getMessage());
            return json_fail('删除通知规则失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取文件类型树形数据
     */
    public function getFileTypeTree()
    {
        try {
            // 获取所有有效的文件描述
            $fileDescriptions = \App\Models\FileDescriptions::where('is_valid', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            Log::info('获取到文件描述数据', ['count' => $fileDescriptions->count()]);

            $tree = $this->formatFileDescriptionTreeData($fileDescriptions);

            Log::info('构建的树数据', ['tree_count' => count($tree)]);

            return json_success('获取成功', $tree);
        } catch (\Exception $e) {
            Log::error('获取文件类型树失败: ' . $e->getMessage());
            return json_fail('获取文件类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 格式化文件描述树形数据（三级结构：文件大类->文件小类->文件描述）
     */
    private function formatFileDescriptionTreeData($fileDescriptions)
    {
        // 按文件大类分组
        $groupedByMajor = $fileDescriptions->groupBy('file_category_major');

        $tree = [];
        $majorIndex = 1;

        foreach ($groupedByMajor as $majorCategory => $majorItems) {
            $majorId = 'major_' . $majorIndex;
            $majorNode = [
                'id' => $majorId,
                'label' => $majorCategory,
                'icon' => 'el-icon-folder',
                'level' => 1,
                'type' => 'major_category',
                'children' => []
            ];

            // 按文件小类分组
            $groupedByMinor = $majorItems->groupBy('file_category_minor');
            $minorIndex = 1;

            foreach ($groupedByMinor as $minorCategory => $minorItems) {
                $minorId = $majorId . '_minor_' . $minorIndex;
                $minorNode = [
                    'id' => $minorId,
                    'label' => $minorCategory,
                    'icon' => 'el-icon-folder-opened',
                    'level' => 2,
                    'type' => 'minor_category',
                    'children' => []
                ];

                // 添加文件描述
                foreach ($minorItems as $fileDesc) {
                    $descNode = [
                        'id' => 'desc_' . $fileDesc->id,
                        'label' => $fileDesc->file_name . ' (' . $fileDesc->file_code . ')',
                        'icon' => 'el-icon-document',
                        'level' => 3,
                        'type' => 'file_description',
                        'file_description_id' => $fileDesc->id,
                        'fileInfo' => [
                            'id' => $fileDesc->id,
                            'sequence' => $fileDesc->sort_order,
                            'caseType' => $fileDesc->case_type,
                            'country' => $fileDesc->country,
                            'documentType' => $majorCategory,
                            'documentSubType' => $minorCategory,
                            'documentName' => $fileDesc->file_name,
                            'documentNo' => $fileDesc->file_code,
                            'content' => $fileDesc->internal_code,
                            'fileDescription' => $fileDesc->file_description,
                            'authorizedClient' => $fileDesc->authorized_client,
                            'authorizedRole' => $fileDesc->authorized_role,
                            'isEffective' => $fileDesc->is_valid
                        ]
                    ];

                    $minorNode['children'][] = $descNode;
                }

                if (!empty($minorNode['children'])) {
                    $majorNode['children'][] = $minorNode;
                    $minorIndex++;
                }
            }

            if (!empty($majorNode['children'])) {
                $tree[] = $majorNode;
                $majorIndex++;
            }
        }

        return $tree;
    }

    /**
     * 格式化文件分类树形数据（旧方法，保留兼容性）
     */
    private function formatFileCategoryTreeData($fileCategories)
    {
        // 按文件大类分组
        $groupedByMainCategory = $fileCategories->groupBy('main_category');
        
        $tree = [];
        foreach ($groupedByMainCategory as $mainCategory => $subCategories) {
            $mainCategoryNode = [
                'id' => 'main_' . md5($mainCategory),
                'label' => $mainCategory,
                'icon' => 'el-icon-folder',
                'level' => 1,
                'type' => 'main_category',
                'children' => []
            ];
            
            foreach ($subCategories as $subCategory) {
                $subCategoryNode = [
                    'id' => $subCategory->id,
                    'label' => $subCategory->sub_category,
                    'icon' => 'el-icon-document',
                    'level' => 2,
                    'type' => 'sub_category',
                    'file_category_id' => $subCategory->id,
                    'fileInfo' => [
                        'id' => $subCategory->id,
                        'sequence' => 1,
                        'caseType' => $mainCategory,
                        'country' => '通用',
                        'documentType' => $mainCategory,
                        'documentSubType' => $subCategory->sub_category,
                        'documentName' => $subCategory->sub_category,
                        'documentNo' => '',
                        'content' => '',
                        'isEffective' => true,
                        'mainCategory' => $mainCategory,
                        'subCategory' => $subCategory->sub_category
                    ]
                ];
                
                $mainCategoryNode['children'][] = $subCategoryNode;
            }
            
            $tree[] = $mainCategoryNode;
        }
        
        return $tree;
    }

    /**
     * 获取指定文件描述的规则列表
     */
    public function getRulesByFileCategory(Request $request, $fileDescriptionId)
    {
        try {
            // 检查文件描述是否存在
            $fileDescription = \App\Models\FileDescriptions::find($fileDescriptionId);
            if (!$fileDescription) {
                Log::warning('文件描述不存在', ['id' => $fileDescriptionId]);
                return json_fail('文件描述不存在');
            }

            Log::info('获取文件描述规则', [
                'file_description_id' => $fileDescriptionId,
                'file_name' => $fileDescription->file_name
            ]);

            $query = NotificationRule::with(['creator', 'updaterRelation'])
                ->where('file_category_id', $fileDescriptionId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc');

            // 状态筛选
            if ($request->has('status') && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 有效性筛选
            if ($request->has('is_effective') && $request->is_effective !== '') {
                $query->where('is_effective', $request->is_effective);
            }

            $rules = $query->get();
            $total = $rules->count();

            Log::info('找到规则数量', ['count' => $total]);

            $formattedRules = $rules->map(function ($rule, $index) {
                return [
                    'id' => $rule->id,
                    'sequence' => $index + 1,
                    'ruleName' => $rule->name,
                    'ruleDescription' => $rule->description,
                    'ruleType' => $rule->rule_type_text,
                    'isEffective' => $rule->is_effective == 1,
                    'status' => $rule->status,
                    'priority' => $rule->priority,
                    'sortOrder' => $rule->sort_order,
                    'updater' => $rule->updaterRelation ? $rule->updaterRelation->real_name : ($rule->updater ?: 'System'),
                    'updateTime' => $rule->updated_at->format('Y-m-d H:i:s'),
                    'created_at' => $rule->created_at->format('Y-m-d H:i:s'),
                    'conditions' => $rule->conditions,
                    'actions' => $rule->actions,
                    'is_config' => $rule->is_config,
                    'process_item' => $rule->process_item,
                    'processor' => $rule->processor,
                    'fixed_personnel' => $rule->fixed_personnel
                ];
            });

            return json_success('获取成功', [
                'fileDescription' => [
                    'id' => $fileDescription->id,
                    'fileName' => $fileDescription->file_name,
                    'fileCode' => $fileDescription->file_code,
                    'majorCategory' => $fileDescription->file_category_major,
                    'minorCategory' => $fileDescription->file_category_minor,
                    'isValid' => $fileDescription->is_valid
                ],
                'rules' => $formattedRules,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            Log::error('获取文件分类规则失败: ' . $e->getMessage());
            return json_fail('获取文件分类规则失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取规则类型
     */
    public function getRuleTypes()
    {
        try {
            $ruleTypes = [
                ['value' => 'add_process', 'label' => '新增处理事项'],
                ['value' => 'update_process', 'label' => '更新处理事项'],
                ['value' => 'update_status', 'label' => '更新项目状态'],
                ['value' => 'update_info', 'label' => '更新项目信息']
            ];

            return json_success('获取成功', $ruleTypes);
        } catch (\Exception $e) {
            return json_fail('获取规则类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取处理事项
     */
    public function getProcessItems()
    {
        try {
            // 从处理事项信息表获取数据
            $processItems = \App\Models\ProcessInformation::where('is_valid', 1)
                ->select('id', 'process_name as label', 'process_code as value', 'case_type', 'country')
                ->orderBy('case_type')
                ->orderBy('process_name')
                ->get();

            return json_success('获取成功', $processItems);
        } catch (\Exception $e) {
            // 如果表不存在或查询失败，返回默认数据
            $processItems = [
                ['value' => 'payment', 'label' => '缴费'],
                ['value' => 'description', 'label' => '处理事项说明'],
                ['value' => 'writing', 'label' => '撰写']
            ];
            return json_success('获取成功', $processItems);
        }
    }

    /**
     * 获取处理事项状态
     */
    public function getProcessStatuses(Request $request)
    {
        try {
            $processItemId = $request->get('process_item_id');

            if ($processItemId) {
                // 根据处理事项获取对应的状态
                $statuses = \App\Models\ProcessStatus::where('is_valid', 1)
                    ->select('id', 'status_name as label', 'status_code as value')
                    ->orderBy('sort')
                    ->get();
            } else {
                // 获取所有处理事项状态
                $statuses = \App\Models\ProcessStatus::where('is_valid', 1)
                    ->select('id', 'status_name as label', 'status_code as value')
                    ->orderBy('sort')
                    ->get();
            }

            return json_success('获取成功', $statuses);
        } catch (\Exception $e) {
            // 如果表不存在或查询失败，返回默认数据
            $statuses = [
                ['value' => 'pending', 'label' => '待处理'],
                ['value' => 'processing', 'label' => '处理中'],
                ['value' => 'completed', 'label' => '已完成']
            ];
            return json_success('获取成功', $statuses);
        }
    }

    /**
     * 获取用户列表（用于固定人员选择）
     */
    public function getUsers()
    {
        try {
            $users = \App\Models\User::where('status', 1)
                ->select('id', 'real_name as label', 'username', 'department_id')
                ->with(['department:id,department_name'])
                ->orderBy('real_name')
                ->get();

            $formattedUsers = $users->map(function($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->label,
                    'username' => $user->username,
                    'department' => $user->department ? $user->department->department_name : '未分配'
                ];
            });

            return json_success('获取成功', $formattedUsers);
        } catch (\Exception $e) {
            // 如果表不存在或查询失败，返回默认数据
            $users = [
                ['value' => 1, 'label' => '张三', 'username' => 'zhangsan', 'department' => '业务部'],
                ['value' => 2, 'label' => '李四', 'username' => 'lisi', 'department' => '技术部'],
                ['value' => 3, 'label' => '王五', 'username' => 'wangwu', 'department' => '管理部']
            ];
            return json_success('获取成功', $users);
        }
    }

    /**
     * 获取国家地区
     */
    public function getCountries()
    {
        try {
            $countries = [
                ['value' => '中国', 'label' => '中国'],
                ['value' => '美国', 'label' => '美国'],
                ['value' => '欧盟', 'label' => '欧盟'],
                ['value' => '日本', 'label' => '日本'],
                ['value' => '韩国', 'label' => '韩国']
            ];

            return json_success('获取成功', $countries);
        } catch (\Exception $e) {
            return json_fail('获取国家地区失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取项目类型
     */
    public function getCaseTypes()
    {
        try {
            $caseTypes = [
                ['value' => '专利申请', 'label' => '专利申请'],
                ['value' => '商标注册', 'label' => '商标注册'],
                ['value' => '版权登记', 'label' => '版权登记']
            ];

            return json_success('获取成功', $caseTypes);
        } catch (\Exception $e) {
            return json_fail('获取项目类型失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换状态
     */
    public function toggleStatus($id)
    {
        try {
            $rule = NotificationRule::find($id);
            if (!$rule) {
                return json_fail('通知规则不存在');
            }

            $rule->update(['status' => $rule->status == 1 ? 0 : 1]);
            return json_success('状态切换成功', ['status' => $rule->status]);
        } catch (\Exception $e) {
            Log::error('切换通知规则状态失败: ' . $e->getMessage());
            return json_fail('状态切换失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量操作
     */
    public function batchOperation(Request $request)
    {
        try {
            $action = $request->input('action');
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return json_fail('请选择要操作的规则');
            }
            
            $message = '';
            switch ($action) {
                case 'enable':
                    NotificationRule::whereIn('id', $ids)->update(['status' => 1]);
                    $message = '批量启用成功';
                    break;
                case 'disable':
                    NotificationRule::whereIn('id', $ids)->update(['status' => 0]);
                    $message = '批量禁用成功';
                    break;
                case 'delete':
                    NotificationRule::whereIn('id', $ids)->delete();
                    $message = '批量删除成功';
                    break;
                default:
                    return json_fail('不支持的操作类型');
            }

            return json_success($message);
        } catch (\Exception $e) {
            Log::error('批量操作失败: ' . $e->getMessage());
            return json_fail('批量操作失败: ' . $e->getMessage());
        }
    }
}