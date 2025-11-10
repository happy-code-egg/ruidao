<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use App\Models\FileCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 通知规则控制器
 * 负责通知规则的增删改查、状态管理和规则配置
 */
class NotificationRuleController extends Controller
{
    /**
 * 获取通知规则列表 index
 *
 * 功能描述：获取系统通知规则列表，支持多种筛选条件和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - keyword (string, optional): 关键词搜索条件（匹配规则名称或描述）
 *   - ruleName (string, optional): 规则名称精确筛选
 *   - ruleType (string, optional): 规则类型筛选
 *   - file_category_id (int, optional): 文件分类ID筛选
 *   - status (int, optional): 状态筛选（0-禁用，1-启用）
 *   - isEffective (boolean|string, optional): 是否有效（true/false或"true"/"false"）
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 通知规则列表数据
 *     - id (int): 规则ID
 *     - sequence (int): 序号（当前页的序号）
 *     - ruleName (string): 规则名称
 *     - ruleDescription (string): 规则描述
 *     - ruleType (string): 规则类型文本
 *     - isEffective (boolean): 是否有效
 *     - updater (string): 最后更新人
 *     - updateTime (string): 更新时间
 *     - fileType (string): 文件类型
 *     - mainCategory (string): 主分类
 *     - subCategory (string): 子分类
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *     - priority (int): 优先级
 *     - conditions (array): 规则条件
 *     - actions (array): 规则动作
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器，预加载关联关系，并按排序字段、优先级和创建时间排序
        $query = NotificationRule::with(['creator', 'updaterRelation', 'fileCategory'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc');

        // 关键词搜索条件（匹配规则名称或描述）
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

        // 状态筛选（0-禁用，1-启用）
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // 有效性筛选（是否生效）
        if ($request->has('isEffective') && $request->isEffective !== '') {
            // 处理字符串和布尔值两种情况
            $isEffective = $request->isEffective === 'true' || $request->isEffective === true;
            $query->where('is_effective', $isEffective ? 1 : 0);
        }

        // 分页处理
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);
        // 获取总记录数
        $total = $query->count();

        // 执行分页查询
        $rules = $query->offset(($page - 1) * $limit)
                      ->limit($limit)
                      ->get();

        // 格式化数据以匹配前端期望的格式
        $formattedRules = $rules->map(function ($rule, $index) use ($page, $limit) {
            return [
                'id' => $rule->id,
                // 计算当前页面的序号
                'sequence' => ($page - 1) * $limit + $index + 1,
                'ruleName' => $rule->name,
                'ruleDescription' => $rule->description,
                // 使用模型访问器获取规则类型文本
                'ruleType' => $rule->rule_type_text,
                // 转换是否有效字段为布尔值
                'isEffective' => $rule->is_effective == 1,
                // 获取最后更新人信息
                'updater' => $rule->updater_relation ? $rule->updater_relation->real_name : ($rule->updater ?: 'System'),
                'updateTime' => $rule->updated_at->format('Y-m-d H:i:s'),
                // 获取文件分类信息
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

        // 返回分页响应
        return json_page($formattedRules, $total, '获取成功');
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('获取通知规则列表失败: ' . $e->getMessage());
        return json_fail('获取通知规则列表失败: ' . $e->getMessage());
    }
}


   /**
 * 获取通知规则详情 show
 *
 * 功能描述：根据ID获取单条通知规则的详细信息
 *
 * 传入参数：
 * - id (int): 通知规则的ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 通知规则详细信息
 *   - id (int): 规则ID
 *   - name (string): 规则名称
 *   - description (string): 规则描述
 *   - rule_type (string): 规则类型
 *   - file_category_id (int): 文件分类ID
 *   - conditions (array): 规则条件
 *   - actions (array): 规则动作
 *   - is_config (int): 是否需要配置
 *   - process_item (string): 处理事项
 *   - process_status (string): 处理状态
 *   - is_upload (int): 是否需要上传
 *   - transfer_target (string): 转移目标
 *   - attachment_config (string): 附件配置
 *   - processor (string): 处理人
 *   - fixed_personnel (string): 固定人员
 *   - internal_deadline (int): 内部截止时间
 *   - customer_deadline (int): 客户截止时间
 *   - official_deadline (int): 官方截止时间
 *   - complete_date (string): 完成日期
 *   - is_effective (int): 是否有效
 *   - priority (int): 优先级
 *   - status (int): 状态
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找通知规则，并预加载关联关系
        $rule = NotificationRule::with(['creator', 'updaterRelation', 'fileCategory'])->find($id);

        // 如果规则不存在，返回失败响应
        if (!$rule) {
            return json_fail('通知规则不存在');
        }

        // 返回成功响应，包含规则详细信息
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
        // 记录错误日志并返回失败响应
        Log::error('获取通知规则详情失败: ' . $e->getMessage());
        return json_fail('获取通知规则详情失败: ' . $e->getMessage());
    }
}


   /**
 * 创建通知规则 store
 *
 * 功能描述：创建新的通知规则
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - name (string, required): 规则名称，最大200字符
 *   - description (string, optional): 规则描述
 *   - rule_type (string, required): 规则类型，最大50字符
 *   - file_category_id (int, optional): 文件分类ID，必须存在于file_descriptions表中
 *   - conditions (array, optional): 规则条件
 *   - actions (array, optional): 规则动作
 *   - is_config (int, optional): 是否需要配置
 *   - process_item (string, optional): 处理事项
 *   - process_status (string, optional): 处理状态
 *   - is_upload (int, optional): 是否需要上传
 *   - transfer_target (string, optional): 转移目标
 *   - attachment_config (string, optional): 附件配置
 *   - generated_filename (string, optional): 生成文件名
 *   - processor (string, optional): 处理人
 *   - fixed_personnel (string, optional): 固定人员
 *   - internal_deadline (int, optional): 内部截止时间
 *   - customer_deadline (int, optional): 客户截止时间
 *   - official_deadline (int, optional): 官方截止时间
 *   - internal_priority_deadline (int, optional): 内部优先级截止时间
 *   - customer_priority_deadline (int, optional): 客户优先级截止时间
 *   - official_priority_deadline (int, optional): 官方优先级截止时间
 *   - internal_precheck_deadline (int, optional): 内部预检查截止时间
 *   - customer_precheck_deadline (int, optional): 客户预检查截止时间
 *   - official_precheck_deadline (int, optional): 官方预检查截止时间
 *   - complete_date (string, optional): 完成日期
 *   - is_effective (int, optional): 是否有效
 *   - priority (int, optional): 优先级
 *   - sort_order (int, optional): 排序
 *   - status (int, optional): 状态，默认为1(启用)
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建的通知规则对象
 */
public function store(Request $request)
{
    try {
        // 验证必填字段
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'rule_type' => 'required|string|max:50',
            'file_category_id' => 'nullable|integer|exists:file_descriptions,id',
        ]);

        // 处理其他可选字段
        $additionalData = $request->only([
            'conditions', 'actions', 'is_config', 'process_item', 'process_status',
            'is_upload', 'transfer_target', 'attachment_config', 'generated_filename',
            'processor', 'fixed_personnel', 'internal_deadline', 'customer_deadline',
            'official_deadline', 'internal_priority_deadline', 'customer_priority_deadline',
            'official_priority_deadline', 'internal_precheck_deadline', 'customer_precheck_deadline',
            'official_precheck_deadline', 'complete_date', 'is_effective', 'priority', 'sort_order'
        ]);

        // 合并验证数据和其他字段数据
        $data = array_merge($data, $additionalData);
        // 设置创建者ID
        $data['created_by'] = auth('api')->id();
        // 设置状态，默认为1(启用)
        $data['status'] = $request->get('status', 1);

        // 创建通知规则
        $rule = NotificationRule::create($data);

        // 返回成功响应，包含创建的规则对象
        return json_success('创建成功', $rule);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('创建通知规则失败: ' . $e->getMessage());
        return json_fail('创建通知规则失败: ' . $e->getMessage());
    }
}

/**
 * 更新通知规则 update
 *
 * 功能描述：根据ID更新通知规则信息
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - name (string, required): 规则名称，最大200字符
 *   - description (string, optional): 规则描述
 *   - rule_type (string, required): 规则类型，最大50字符
 *   - file_category_id (int, optional): 文件分类ID，必须存在于file_descriptions表中
 *   - conditions (array, optional): 规则条件
 *   - actions (array, optional): 规则动作
 *   - is_config (int, optional): 是否需要配置
 *   - process_item (string, optional): 处理事项
 *   - process_status (string, optional): 处理状态
 *   - is_upload (int, optional): 是否需要上传
 *   - transfer_target (string, optional): 转移目标
 *   - attachment_config (string, optional): 附件配置
 *   - generated_filename (string, optional): 生成文件名
 *   - processor (string, optional): 处理人
 *   - fixed_personnel (string, optional): 固定人员
 *   - internal_deadline (int, optional): 内部截止时间
 *   - customer_deadline (int, optional): 客户截止时间
 *   - official_deadline (int, optional): 官方截止时间
 *   - internal_priority_deadline (int, optional): 内部优先级截止时间
 *   - customer_priority_deadline (int, optional): 客户优先级截止时间
 *   - official_priority_deadline (int, optional): 官方优先级截止时间
 *   - internal_precheck_deadline (int, optional): 内部预检查截止时间
 *   - customer_precheck_deadline (int, optional): 客户预检查截止时间
 *   - official_precheck_deadline (int, optional): 官方预检查截止时间
 *   - complete_date (string, optional): 完成日期
 *   - is_effective (int, optional): 是否有效
 *   - priority (int, optional): 优先级
 *   - sort_order (int, optional): 排序
 *   - status (int, optional): 状态
 * - id (int): 要更新的通知规则ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的通知规则对象
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找通知规则
        $rule = NotificationRule::find($id);
        // 如果规则不存在，返回失败响应
        if (!$rule) {
            return json_fail('通知规则不存在');
        }

        // 验证必填字段
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'rule_type' => 'required|string|max:50',
            'file_category_id' => 'nullable|integer|exists:file_descriptions,id',
        ]);

        // 处理其他可选字段（包含status字段）
        $additionalData = $request->only([
            'conditions', 'actions', 'is_config', 'process_item', 'process_status',
            'is_upload', 'transfer_target', 'attachment_config', 'generated_filename',
            'processor', 'fixed_personnel', 'internal_deadline', 'customer_deadline',
            'official_deadline', 'internal_priority_deadline', 'customer_priority_deadline',
            'official_priority_deadline', 'internal_precheck_deadline', 'customer_precheck_deadline',
            'official_precheck_deadline', 'complete_date', 'is_effective', 'priority', 'sort_order', 'status'
        ]);

        // 合并验证数据和其他字段数据
        $data = array_merge($data, $additionalData);
        // 设置更新者ID
        $data['updated_by'] = auth('api')->id();

        // 更新通知规则
        $rule->update($data);

        // 返回成功响应，包含更新的规则对象
        return json_success('更新成功', $rule);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('更新通知规则失败: ' . $e->getMessage());
        return json_fail('更新通知规则失败: ' . $e->getMessage());
    }
}


  /**
 * 删除通知规则 destroy
 *
 * 功能描述：根据ID删除单条通知规则
 *
 * 传入参数：
 * - id (int): 要删除的通知规则ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找通知规则
        $rule = NotificationRule::find($id);
        // 如果规则不存在，返回失败响应
        if (!$rule) {
            return json_fail('通知规则不存在');
        }

        // 删除通知规则
        $rule->delete();
        // 返回成功响应
        return json_success('删除成功');
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('删除通知规则失败: ' . $e->getMessage());
        return json_fail('删除通知规则失败: ' . $e->getMessage());
    }
}

/**
 * 获取文件类型树形数据 getFileTypeTree
 *
 * 功能描述：获取系统中所有有效文件描述的树形结构数据，按文件大类->文件小类->文件描述三级结构组织
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 树形结构数据
 *   - id (string): 节点ID
 *   - label (string): 节点标签
 *   - icon (string): 节点图标
 *   - level (int): 节点层级（1-大类，2-小类，3-文件描述）
 *   - type (string): 节点类型
 *   - children (array): 子节点列表
 *   - fileInfo (object, optional): 文件信息（仅在文件描述层级存在）
 */
public function getFileTypeTree()
{
    try {
        // 获取所有有效的文件描述，按排序字段和ID排序
        $fileDescriptions = \App\Models\FileDescriptions::where('is_valid', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 记录获取到的文件描述数量
        Log::info('获取到文件描述数据', ['count' => $fileDescriptions->count()]);

        // 格式化为树形结构数据
        $tree = $this->formatFileDescriptionTreeData($fileDescriptions);

        // 记录构建的树节点数量
        Log::info('构建的树数据', ['tree_count' => count($tree)]);

        // 返回成功响应，包含树形数据
        return json_success('获取成功', $tree);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('获取文件类型树失败: ' . $e->getMessage());
        return json_fail('获取文件类型失败: ' . $e->getMessage());
    }
}

/**
 * 格式化文件描述树形数据 formatFileDescriptionTreeData
 *
 * 功能描述：将文件描述数据格式化为三级树形结构（文件大类->文件小类->文件描述）
 *
 * 传入参数：
 * - fileDescriptions (Collection): 文件描述数据集合
 *
 * 输出参数：
 * - tree (array): 格式化后的树形结构数据
 *   - id (string): 节点ID
 *   - label (string): 节点标签
 *   - icon (string): 节点图标
 *   - level (int): 节点层级
 *   - type (string): 节点类型
 *   - children (array): 子节点列表
 *   - fileInfo (object, optional): 文件信息（仅在文件描述层级存在）
 */
private function formatFileDescriptionTreeData($fileDescriptions)
{
    // 按文件大类分组
    $groupedByMajor = $fileDescriptions->groupBy('file_category_major');

    $tree = [];
    $majorIndex = 1;

    // 遍历每个文件大类
    foreach ($groupedByMajor as $majorCategory => $majorItems) {
        $majorId = 'major_' . $majorIndex;
        // 创建大类节点
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

        // 遍历每个文件小类
        foreach ($groupedByMinor as $minorCategory => $minorItems) {
            $minorId = $majorId . '_minor_' . $minorIndex;
            // 创建小类节点
            $minorNode = [
                'id' => $minorId,
                'label' => $minorCategory,
                'icon' => 'el-icon-folder-opened',
                'level' => 2,
                'type' => 'minor_category',
                'children' => []
            ];

            // 添加文件描述节点
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

            // 如果小类有子节点，则添加到大类节点中
            if (!empty($minorNode['children'])) {
                $majorNode['children'][] = $minorNode;
                $minorIndex++;
            }
        }

        // 如果大类有子节点，则添加到树中
        if (!empty($majorNode['children'])) {
            $tree[] = $majorNode;
            $majorIndex++;
        }
    }

    return $tree;
}

   /**
 * 格式化文件分类树形数据 formatFileCategoryTreeData
 *
 * 功能描述：将文件分类数据格式化为二级树形结构（文件大类->文件小类），用于兼容旧的数据结构
 *
 * 传入参数：
 * - fileCategories (Collection): 文件分类数据集合
 *
 * 输出参数：
 * - tree (array): 格式化后的树形结构数据
 *   - id (string): 节点ID（大类节点使用md5加密）
 *   - label (string): 节点标签
 *   - icon (string): 节点图标
 *   - level (int): 节点层级（1-大类，2-小类）
 *   - type (string): 节点类型
 *   - children (array): 子节点列表
 *   - fileInfo (object): 文件信息
 *     - id (int): 分类ID
 *     - sequence (int): 序号
 *     - caseType (string): 案件类型
 *     - country (string): 国家
 *     - documentType (string): 文档类型
 *     - documentSubType (string): 文档子类型
 *     - documentName (string): 文档名称
 *     - documentNo (string): 文档编号
 *     - content (string): 内容
 *     - isEffective (boolean): 是否有效
 *     - mainCategory (string): 主分类
 *     - subCategory (string): 子分类
 */
private function formatFileCategoryTreeData($fileCategories)
{
    // 按文件大类分组
    $groupedByMainCategory = $fileCategories->groupBy('main_category');

    $tree = [];
    // 遍历每个文件大类
    foreach ($groupedByMainCategory as $mainCategory => $subCategories) {
        // 创建大类节点
        $mainCategoryNode = [
            'id' => 'main_' . md5($mainCategory),
            'label' => $mainCategory,
            'icon' => 'el-icon-folder',
            'level' => 1,
            'type' => 'main_category',
            'children' => []
        ];

        // 遍历该大类下的所有子分类
        foreach ($subCategories as $subCategory) {
            // 创建子分类节点
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

            // 将子分类节点添加到大类节点的子节点列表中
            $mainCategoryNode['children'][] = $subCategoryNode;
        }

        // 将大类节点添加到树形结构中
        $tree[] = $mainCategoryNode;
    }

    return $tree;
}

/**
 * 获取指定文件描述的规则列表 getRulesByFileCategory
 *
 * 功能描述：根据文件描述ID获取关联的通知规则列表，支持状态和有效性筛选
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - status (int, optional): 状态筛选条件
 *   - is_effective (mixed, optional): 有效性筛选条件
 * - fileDescriptionId (int): 文件描述ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 返回数据
 *   - fileDescription (object): 文件描述信息
 *     - id (int): 文件描述ID
 *     - fileName (string): 文件名称
 *     - fileCode (string): 文件代码
 *     - majorCategory (string): 主分类
 *     - minorCategory (string): 子分类
 *     - isValid (boolean): 是否有效
 *   - rules (array): 规则列表
 *     - id (int): 规则ID
 *     - sequence (int): 序号
 *     - ruleName (string): 规则名称
 *     - ruleDescription (string): 规则描述
 *     - ruleType (string): 规则类型文本
 *     - isEffective (boolean): 是否有效
 *     - status (int): 状态
 *     - priority (int): 优先级
 *     - sortOrder (int): 排序
 *     - updater (string): 更新人
 *     - updateTime (string): 更新时间
 *     - created_at (string): 创建时间
 *     - conditions (array): 条件
 *     - actions (array): 动作
 *     - is_config (int): 是否需要配置
 *     - process_item (string): 处理事项
 *     - processor (string): 处理人
 *     - fixed_personnel (string): 固定人员
 *   - total (int): 总记录数
 */
public function getRulesByFileCategory(Request $request, $fileDescriptionId)
{
    try {
        // 检查文件描述是否存在
        $fileDescription = \App\Models\FileDescriptions::find($fileDescriptionId);
        // 如果文件描述不存在，记录警告日志并返回失败响应
        if (!$fileDescription) {
            Log::warning('文件描述不存在', ['id' => $fileDescriptionId]);
            return json_fail('文件描述不存在');
        }

        // 记录获取文件描述规则的日志
        Log::info('获取文件描述规则', [
            'file_description_id' => $fileDescriptionId,
            'file_name' => $fileDescription->file_name
        ]);

        // 初始化查询构建器，预加载关联关系，并按排序字段、优先级和创建时间排序
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

        // 执行查询获取规则列表
        $rules = $query->get();
        // 获取总记录数
        $total = $rules->count();

        // 记录找到的规则数量
        Log::info('找到规则数量', ['count' => $total]);

        // 格式化规则数据
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

        // 返回成功响应，包含文件描述信息和规则列表
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
        // 记录错误日志并返回失败响应
        Log::error('获取文件分类规则失败: ' . $e->getMessage());
        return json_fail('获取文件分类规则失败: ' . $e->getMessage());
    }
}

/**
 * 获取规则类型 getRuleTypes
 *
 * 功能描述：获取系统支持的通知规则类型列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 规则类型列表
 *   - value (string): 类型值
 *   - label (string): 类型标签
 */
public function getRuleTypes()
{
    try {
        // 定义规则类型列表
        $ruleTypes = [
            ['value' => 'add_process', 'label' => '新增处理事项'],
            ['value' => 'update_process', 'label' => '更新处理事项'],
            ['value' => 'update_status', 'label' => '更新项目状态'],
            ['value' => 'update_info', 'label' => '更新项目信息']
        ];

        // 返回成功响应，包含规则类型列表
        return json_success('获取成功', $ruleTypes);
    } catch (\Exception $e) {
        // 返回失败响应
        return json_fail('获取规则类型失败: ' . $e->getMessage());
    }
}


   /**
 * 获取处理事项 getProcessItems
 *
 * 功能描述：获取系统中所有有效的处理事项信息，用于规则配置中的处理事项选择
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 处理事项列表
 *   - id (int): 处理事项ID
 *   - label (string): 处理事项名称
 *   - value (string): 处理事项代码
 *   - case_type (string): 案件类型
 *   - country (string): 国家
 */
public function getProcessItems()
{
    try {
        // 从处理事项信息表获取数据，筛选有效的处理事项
        $processItems = \App\Models\ProcessInformation::where('is_valid', 1)
            ->select('id', 'process_name as label', 'process_code as value', 'case_type', 'country')
            ->orderBy('case_type')
            ->orderBy('process_name')
            ->get();

        // 返回成功响应，包含处理事项列表
        return json_success('获取成功', $processItems);
    } catch (\Exception $e) {
        // 如果表不存在或查询失败，返回默认数据
        $processItems = [
            ['value' => 'payment', 'label' => '缴费'],
            ['value' => 'description', 'label' => '处理事项说明'],
            ['value' => 'writing', 'label' => '撰写']
        ];
        // 返回成功响应，包含默认处理事项列表
        return json_success('获取成功', $processItems);
    }
}

/**
 * 获取处理事项状态 getProcessStatuses
 *
 * 功能描述：获取处理事项的状态列表，支持根据处理事项ID筛选特定状态
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - process_item_id (int, optional): 处理事项ID，用于获取特定处理事项的状态
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 处理事项状态列表
 *   - id (int): 状态ID
 *   - label (string): 状态名称
 *   - value (string): 状态代码
 */
public function getProcessStatuses(Request $request)
{
    try {
        // 获取请求中的处理事项ID参数
        $processItemId = $request->get('process_item_id');

        // 如果提供了处理事项ID，则根据该ID获取对应的状态
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

        // 返回成功响应，包含状态列表
        return json_success('获取成功', $statuses);
    } catch (\Exception $e) {
        // 如果表不存在或查询失败，返回默认数据
        $statuses = [
            ['value' => 'pending', 'label' => '待处理'],
            ['value' => 'processing', 'label' => '处理中'],
            ['value' => 'completed', 'label' => '已完成']
        ];
        // 返回成功响应，包含默认状态列表
        return json_success('获取成功', $statuses);
    }
}

/**
 * 获取用户列表 getUsers
 *
 * 功能描述：获取系统中所有启用的用户列表，用于规则配置中的固定人员选择
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 用户列表
 *   - value (int): 用户ID
 *   - label (string): 用户真实姓名
 *   - username (string): 用户名
 *   - department (string): 所属部门名称
 */
public function getUsers()
{
    try {
        // 获取所有启用状态的用户，预加载部门信息
        $users = \App\Models\User::where('status', 1)
            ->select('id', 'real_name as label', 'username', 'department_id')
            ->with(['department:id,department_name'])
            ->orderBy('real_name')
            ->get();

        // 格式化用户数据
        $formattedUsers = $users->map(function($user) {
            return [
                'value' => $user->id,
                'label' => $user->label,
                'username' => $user->username,
                // 获取部门名称，如果不存在则显示"未分配"
                'department' => $user->department ? $user->department->department_name : '未分配'
            ];
        });

        // 返回成功响应，包含格式化后的用户列表
        return json_success('获取成功', $formattedUsers);
    } catch (\Exception $e) {
        // 如果表不存在或查询失败，返回默认数据
        $users = [
            ['value' => 1, 'label' => '张三', 'username' => 'zhangsan', 'department' => '业务部'],
            ['value' => 2, 'label' => '李四', 'username' => 'lisi', 'department' => '技术部'],
            ['value' => 3, 'label' => '王五', 'username' => 'wangwu', 'department' => '管理部']
        ];
        // 返回成功响应，包含默认用户列表
        return json_success('获取成功', $users);
    }
}

/**
 * 获取国家地区 getCountries
 *
 * 功能描述：获取系统支持的国家地区列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 国家地区列表
 *   - value (string): 国家名称
 *   - label (string): 国家显示名称
 */
public function getCountries()
{
    try {
        // 定义国家地区列表
        $countries = [
            ['value' => '中国', 'label' => '中国'],
            ['value' => '美国', 'label' => '美国'],
            ['value' => '欧盟', 'label' => '欧盟'],
            ['value' => '日本', 'label' => '日本'],
            ['value' => '韩国', 'label' => '韩国']
        ];

        // 返回成功响应，包含国家地区列表
        return json_success('获取成功', $countries);
    } catch (\Exception $e) {
        // 返回失败响应
        return json_fail('获取国家地区失败: ' . $e->getMessage());
    }
}


    /**
 * 获取项目类型 getCaseTypes
 *
 * 功能描述：获取系统支持的项目类型列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 项目类型列表
 *   - value (string): 项目类型值
 *   - label (string): 项目类型标签
 */
public function getCaseTypes()
{
    try {
        // 定义项目类型列表
        $caseTypes = [
            ['value' => '专利申请', 'label' => '专利申请'],
            ['value' => '商标注册', 'label' => '商标注册'],
            ['value' => '版权登记', 'label' => '版权登记']
        ];

        // 返回成功响应，包含项目类型列表
        return json_success('获取成功', $caseTypes);
    } catch (\Exception $e) {
        // 返回失败响应
        return json_fail('获取项目类型失败: ' . $e->getMessage());
    }
}

/**
 * 切换状态 toggleStatus
 *
 * 功能描述：切换通知规则的状态（启用/禁用）
 *
 * 传入参数：
 * - id (int): 通知规则ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 状态信息
 *   - status (int): 切换后的状态值（0-禁用，1-启用）
 */
public function toggleStatus($id)
{
    try {
        // 根据ID查找通知规则
        $rule = NotificationRule::find($id);
        // 如果规则不存在，返回失败响应
        if (!$rule) {
            return json_fail('通知规则不存在');
        }

        // 切换状态：启用(1)变为禁用(0)，禁用(0)变为启用(1)
        $rule->update(['status' => $rule->status == 1 ? 0 : 1]);
        // 返回成功响应，包含切换后的状态
        return json_success('状态切换成功', ['status' => $rule->status]);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('切换通知规则状态失败: ' . $e->getMessage());
        return json_fail('状态切换失败: ' . $e->getMessage());
    }
}

/**
 * 批量操作 batchOperation
 *
 * 功能描述：对通知规则进行批量操作（启用、禁用、删除）
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - action (string): 操作类型（enable-启用，disable-禁用，delete-删除）
 *   - ids (array): 通知规则ID数组
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function batchOperation(Request $request)
{
    try {
        // 获取操作类型和规则ID数组
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        // 如果没有选择规则，返回失败响应
        if (empty($ids)) {
            return json_fail('请选择要操作的规则');
        }

        $message = '';
        // 根据操作类型执行相应操作
        switch ($action) {
            case 'enable':
                // 批量启用规则
                NotificationRule::whereIn('id', $ids)->update(['status' => 1]);
                $message = '批量启用成功';
                break;
            case 'disable':
                // 批量禁用规则
                NotificationRule::whereIn('id', $ids)->update(['status' => 0]);
                $message = '批量禁用成功';
                break;
            case 'delete':
                // 批量删除规则
                NotificationRule::whereIn('id', $ids)->delete();
                $message = '批量删除成功';
                break;
            default:
                // 不支持的操作类型
                return json_fail('不支持的操作类型');
        }

        // 返回成功响应
        return json_success($message);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        Log::error('批量操作失败: ' . $e->getMessage());
        return json_fail('批量操作失败: ' . $e->getMessage());
    }
}

}
