<?php

namespace App\Http\Controllers\Api;

use App\Models\FeeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 费用配置控制器
 */
class FeeConfigController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return FeeConfig::class;
    }

    /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义费用配置数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - sort (int): 排序，可选，整数，最小值1
 *   - case_type (array): 项目类型，必填，数组，至少1个元素
 *   - business_type (array): 业务类型，必填，数组，至少1个元素
 *   - apply_type (array): 申请类型，必填，数组，至少1个元素
 *   - country (array): 国家(地区)，必填，数组，至少1个元素
 *   - fee_type (string): 费用类型，必填，字符串，最大100字符
 *   - fee_name (string): 费用名称，必填，字符串，最大200字符
 *   - fee_name_en (string): 英文费用名称，可选，字符串，最大200字符
 *   - currency (string): 货币，可选，字符串，最大10字符
 *   - fee_code (string): 费用代码，可选，字符串，最大100字符，唯一性验证
 *   - base_fee (numeric): 基础费用，可选，数值型，最小值0
 *   - small_entity_fee (numeric): 小实体费用，可选，数值型，最小值0
 *   - micro_entity_fee (numeric): 微实体费用，可选，数值型，最小值0
 *   - role (array): 角色，可选，数组，至少1个元素
 *   - use_stage (array): 使用阶段，可选，数组，至少1个元素
 *   - is_valid (boolean): 是否有效，可选，布尔值
 *   - sort_order (int): 排序顺序，可选，整数，最小值0
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'sort' => 'nullable|integer|min:1',                    // 排序：可选，整数，最小值1
        'case_type' => 'required|array|min:1',                 // 项目类型：必填，数组，至少1个元素
        'business_type' => 'required|array|min:1',             // 业务类型：必填，数组，至少1个元素
        'apply_type' => 'required|array|min:1',                // 申请类型：必填，数组，至少1个元素
        'country' => 'required|array|min:1',                   // 国家(地区)：必填，数组，至少1个元素
        'fee_type' => 'required|string|max:100',               // 费用类型：必填，字符串，最大100字符
        'fee_name' => 'required|string|max:200',               // 费用名称：必填，字符串，最大200字符
        'fee_name_en' => 'nullable|string|max:200',            // 英文费用名称：可选，字符串，最大200字符
        'currency' => 'nullable|string|max:10',                // 货币：可选，字符串，最大10字符
        'fee_code' => 'nullable|string|max:100',               // 费用代码：可选，字符串，最大100字符
        'base_fee' => 'nullable|numeric|min:0',                // 基础费用：可选，数值型，最小值0
        'small_entity_fee' => 'nullable|numeric|min:0',        // 小实体费用：可选，数值型，最小值0
        'micro_entity_fee' => 'nullable|numeric|min:0',        // 微实体费用：可选，数值型，最小值0
        'role' => 'nullable|array|min:1',                      // 角色：可选，数组，至少1个元素
        'use_stage' => 'nullable|array|min:1',                 // 使用阶段：可选，数组，至少1个元素
        'is_valid' => 'nullable|boolean',                      // 是否有效：可选，布尔值
        'sort_order' => 'nullable|integer|min:0'               // 排序顺序：可选，整数，最小值0
    ];

    // 根据是否为更新操作设置费用代码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性检查
        $id = request()->route('id');
        if (!empty(request('fee_code'))) {
            $rules['fee_code'] .= '|unique:fee_configs,fee_code,' . $id;
        }
    } else {
        // 创建时，全局唯一性验证
        if (!empty(request('fee_code'))) {
            $rules['fee_code'] .= '|unique:fee_configs,fee_code';
        }
    }

    // 返回验证规则
    return $rules;
}

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'case_type.required' => '项目类型不能为空',
            'business_type.required' => '业务类型不能为空',
            'business_type.array' => '业务类型必须是数组',
            'business_type.min' => '请至少选择一个业务类型',
            'apply_type.required' => '申请类型不能为空',
            'apply_type.array' => '申请类型必须是数组',
            'apply_type.min' => '请至少选择一个申请类型',
            'country.required' => '国家(地区)不能为空',
            'country.array' => '国家(地区)必须是数组',
            'country.min' => '请至少选择一个国家(地区)',
            'fee_type.required' => '费用类型不能为空',
            'fee_name.required' => '费用名称不能为空',
            'fee_code.unique' => '费用代码已存在',
            'base_fee.numeric' => '基础费用必须是数字',
            'base_fee.min' => '基础费用不能小于0',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }

   /**
 * 获取列表 - 重写以支持特定的搜索条件 index
 *
 * 功能描述：获取费用配置列表，支持多种筛选条件和分页功能
 *
 * 传入参数：
 * - case_type (array|string, optional): 项目类型筛选条件
 * - business_type (array|string, optional): 业务类型筛选条件
 * - apply_type (array|string, optional): 申请类型筛选条件
 * - country (array|string, optional): 国家(地区)筛选条件
 * - fee_type (string, optional): 费用类型筛选条件
 * - fee_name (string, optional): 费用名称搜索关键词
 * - role (array|string, optional): 角色筛选条件
 * - is_valid (boolean, optional): 是否有效筛选条件
 * - page (int, optional): 页码，默认为1
 * - limit (int, optional): 每页数量，默认为10，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 费用配置列表
 *     - id (int): 记录ID
 *     - sort (int): 排序值
 *     - caseType (array): 项目类型
 *     - businessType (array): 业务类型
 *     - applyType (array): 申请类型
 *     - country (array): 国家(地区)
 *     - feeType (string): 费用类型
 *     - feeName (string): 费用名称
 *     - feeNameEn (string): 英文费用名称
 *     - currency (string): 货币
 *     - feeCode (string): 费用代码
 *     - baseFee (float): 基础费用
 *     - smallEntityFee (float): 小实体费用
 *     - microEntityFee (float): 微实体费用
 *     - role (array): 角色
 *     - useStage (array): 使用阶段
 *     - isValid (boolean): 是否有效
 *     - sortOrder (int): 排序顺序
 *     - updatedBy (string): 更新人
 *     - updatedAt (string): 更新时间
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = FeeConfig::query();

        // 项目类型搜索条件
        if ($request->has('case_type') && !empty($request->case_type)) {
            $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
            // 排除'all'选项后进行搜索
            if (!in_array('all', $caseTypes)) {
                $query->where(function($q) use ($caseTypes) {
                    foreach ($caseTypes as $type) {
                        $q->orWhereJsonContains('case_type', $type);
                    }
                });
            }
        }

        // 业务类型搜索条件
        if ($request->has('business_type') && !empty($request->business_type)) {
            $businessTypes = is_array($request->business_type) ? $request->business_type : [$request->business_type];
            // 排除'all'选项后进行搜索
            if (!in_array('all', $businessTypes)) {
                $query->where(function($q) use ($businessTypes) {
                    foreach ($businessTypes as $type) {
                        $q->orWhereJsonContains('business_type', $type);
                    }
                });
            }
        }

        // 申请类型搜索条件
        if ($request->has('apply_type') && !empty($request->apply_type)) {
            $applyTypes = is_array($request->apply_type) ? $request->apply_type : [$request->apply_type];
            // 排除'all'选项后进行搜索
            if (!in_array('all', $applyTypes)) {
                $query->where(function($q) use ($applyTypes) {
                    foreach ($applyTypes as $type) {
                        $q->orWhereJsonContains('apply_type', $type);
                    }
                });
            }
        }

        // 国家(地区)搜索条件
        if ($request->has('country') && !empty($request->country)) {
            $countries = is_array($request->country) ? $request->country : [$request->country];
            // 排除'all'选项后进行搜索
            if (!in_array('all', $countries)) {
                $query->where(function($q) use ($countries) {
                    foreach ($countries as $country) {
                        $q->orWhereJsonContains('country', $country);
                    }
                });
            }
        }

        // 费用类型搜索条件
        if ($request->has('fee_type') && !empty($request->fee_type)) {
            $query->where('fee_type', $request->fee_type);
        }

        // 费用名称模糊搜索条件
        if ($request->has('fee_name') && !empty($request->fee_name)) {
            $query->where('fee_name', 'like', "%{$request->fee_name}%");
        }

        // 角色搜索条件
        if ($request->has('role') && !empty($request->role)) {
            $roles = is_array($request->role) ? $request->role : [$request->role];
            // 排除'all'选项后进行搜索
            if (!in_array('all', $roles)) {
                $query->where(function($q) use ($roles) {
                    foreach ($roles as $role) {
                        $q->orWhereJsonContains('role', $role);
                    }
                });
            }
        }

        // 是否有效状态筛选条件
        if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
            $query->where('is_valid', (bool)$request->is_valid);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 10)));

        // 获取总记录数
        $total = $query->count();

        // 执行查询并获取数据，按排序字段排序
        $data = $query->orderBy('sort')
                     ->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get()
                     ->map(function ($item) {
                         // 格式化返回数据
                         return [
                             'id' => $item->id,
                             'sort' => $item->sort,
                             'caseType' => $item->case_type,
                             'businessType' => $item->business_type,
                             'applyType' => $item->apply_type,
                             'country' => $item->country,
                             'feeType' => $item->fee_type,
                             'feeName' => $item->fee_name,
                             'feeNameEn' => $item->fee_name_en,
                             'currency' => $item->currency,
                             'feeCode' => $item->fee_code,
                             'baseFee' => $item->base_fee,
                             'smallEntityFee' => $item->small_entity_fee,
                             'microEntityFee' => $item->micro_entity_fee,
                             'role' => $item->role,
                             'useStage' => $item->use_stage,
                             'isValid' => (bool)$item->is_valid,
                             'sortOrder' => $item->sort_order,
                             'updatedBy' => $item->updater->real_name ?? '',
                             'updatedAt' => $item->updated_at,
                         ];
                     });

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(8, '获取费用配置列表失败：' . $e->getMessage(), [
            'title' => '费用配置管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED,
            'trace' => $e->getTraceAsString(),
        ]);
        return json_fail('获取列表失败');
    }
}


  /**
 * 创建 store
 *
 * 功能描述：创建新的费用配置记录，包含所有必要的费用信息和关联数据
 *
 * 传入参数：
 * - sort (int, optional): 排序值，默认为1
 * - case_type (array): 项目类型，必填
 * - business_type (array): 业务类型，必填
 * - apply_type (array): 申请类型，必填
 * - country (array): 国家(地区)，必填
 * - fee_type (string): 费用类型，必填
 * - fee_name (string): 费用名称，必填
 * - fee_name_en (string, optional): 英文费用名称
 * - currency (string, optional): 货币，默认为'CNY'
 * - fee_code (string, optional): 费用代码
 * - base_fee (float, optional): 基础费用，默认为0
 * - small_entity_fee (float, optional): 小实体费用，默认为0
 * - micro_entity_fee (float, optional): 微实体费用，默认为0
 * - role (array, optional): 角色
 * - use_stage (array, optional): 使用阶段
 * - is_valid (boolean, optional): 是否有效，默认为true
 * - sort_order (int, optional): 排序顺序，默认为0
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建成功的费用配置信息
 *   - id (int): 记录ID
 *   - sort (int): 排序值
 *   - caseType (array): 项目类型
 *   - businessType (array): 业务类型
 *   - applyType (array): 申请类型
 *   - country (array): 国家(地区)
 *   - feeType (string): 费用类型
 *   - feeName (string): 费用名称
 *   - baseFee (float): 基础费用
 *   - isValid (boolean): 是否有效
 *   - updateTime (string): 更新时间
 */
public function store(Request $request)
{
    try {
        // 验证输入数据
        $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

        // 验证失败处理
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 获取所有请求数据
        $data = $request->all();

        // 设置默认值
        $data['updater'] = $data['updater'] ?? '系统记录';
        $data['sort'] = $data['sort'] ?? 1;
        $data['currency'] = $data['currency'] ?? 'CNY';
        $data['base_fee'] = $data['base_fee'] ?? 0;
        $data['small_entity_fee'] = $data['small_entity_fee'] ?? 0;
        $data['micro_entity_fee'] = $data['micro_entity_fee'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;

        // 处理数组字段，过滤掉'all'选项
        if (isset($data['business_type']) && is_array($data['business_type'])) {
            $data['business_type'] = array_values(array_filter($data['business_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['apply_type']) && is_array($data['apply_type'])) {
            $data['apply_type'] = array_values(array_filter($data['apply_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['case_type']) && is_array($data['case_type'])) {
            $data['case_type'] = array_values(array_filter($data['case_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['country']) && is_array($data['country'])) {
            $data['country'] = array_values(array_filter($data['country'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['role']) && is_array($data['role'])) {
            $data['role'] = array_values(array_filter($data['role'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['use_stage']) && is_array($data['use_stage'])) {
            $data['use_stage'] = array_values(array_filter($data['use_stage'], function($item) {
                return $item !== 'all';
            }));
        }

        // 设置创建和更新用户ID
        $data['created_by'] = auth()->user()->id;
        $data['updated_by'] = auth()->user()->id;

        // 创建费用配置记录
        $item = FeeConfig::create($data);

        // 返回成功响应
        return json_success('创建成功', [
            'id' => $item->id,
            'sort' => $item->sort,
            'caseType' => $item->case_type,
            'businessType' => $item->business_type,
            'applyType' => $item->apply_type,
            'country' => $item->country,
            'feeType' => $item->fee_type,
            'feeName' => $item->fee_name,
            'baseFee' => $item->base_fee,
            'isValid' => (bool)$item->is_valid,
            'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(8, '创建费用配置失败：' . $e->getMessage(), [
            'title' => '费用配置管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED,
            'trace' => $e->getTraceAsString(),
        ]);
        return json_fail('创建失败');
    }
}


   /**
 * 更新 update
 *
 * 功能描述：更新指定ID的费用配置记录，包含所有费用信息和关联数据
 *
 * 传入参数：
 * - id (int): 费用配置记录ID
 * - sort (int, optional): 排序值
 * - case_type (array): 项目类型
 * - business_type (array): 业务类型
 * - apply_type (array): 申请类型
 * - country (array): 国家(地区)
 * - fee_type (string): 费用类型
 * - fee_name (string): 费用名称
 * - fee_name_en (string, optional): 英文费用名称
 * - currency (string, optional): 货币
 * - fee_code (string, optional): 费用代码
 * - base_fee (float, optional): 基础费用
 * - small_entity_fee (float, optional): 小实体费用
 * - micro_entity_fee (float, optional): 微实体费用
 * - role (array, optional): 角色
 * - use_stage (array, optional): 使用阶段
 * - is_valid (boolean, optional): 是否有效
 * - sort_order (int, optional): 排序顺序
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的费用配置信息
 *   - id (int): 记录ID
 *   - sort (int): 排序值
 *   - caseType (array): 项目类型
 *   - businessType (array): 业务类型
 *   - applyType (array): 申请类型
 *   - country (array): 国家(地区)
 *   - feeType (string): 费用类型
 *   - feeName (string): 费用名称
 *   - baseFee (float): 基础费用
 *   - isValid (boolean): 是否有效
 *   - updatedAt (string): 更新时间
 */
public function update(Request $request, $id)
{
    try {
        // 查找要更新的费用配置记录
        $item = FeeConfig::find($id);

        // 检查记录是否存在
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证输入数据，使用更新模式的验证规则
        $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

        // 验证失败处理
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 获取所有请求数据
        $data = $request->all();

        // 设置更新人信息
        $data['updater'] = $data['updater'] ?? '系统记录';
        // 保持原有的is_valid状态或使用新值
        $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

        // 处理数组字段，过滤掉'all'选项
        if (isset($data['business_type']) && is_array($data['business_type'])) {
            $data['business_type'] = array_values(array_filter($data['business_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['apply_type']) && is_array($data['apply_type'])) {
            $data['apply_type'] = array_values(array_filter($data['apply_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['case_type']) && is_array($data['case_type'])) {
            $data['case_type'] = array_values(array_filter($data['case_type'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['country']) && is_array($data['country'])) {
            $data['country'] = array_values(array_filter($data['country'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['role']) && is_array($data['role'])) {
            $data['role'] = array_values(array_filter($data['role'], function($item) {
                return $item !== 'all';
            }));
        }

        if (isset($data['use_stage']) && is_array($data['use_stage'])) {
            $data['use_stage'] = array_values(array_filter($data['use_stage'], function($item) {
                return $item !== 'all';
            }));
        }

        // 移除创建人字段，防止被修改
        unset($data['created_by']);
        // 设置更新人ID
        $data['updated_by'] = auth()->user()->id;

        // 更新费用配置记录
        $item->update($data);

        // 返回成功响应
        return json_success('更新成功', [
            'id' => $item->id,
            'sort' => $item->sort,
            'caseType' => $item->case_type,
            'businessType' => $item->business_type,
            'applyType' => $item->apply_type,
            'country' => $item->country,
            'feeType' => $item->fee_type,
            'feeName' => $item->fee_name,
            'baseFee' => $item->base_fee,
            'isValid' => (bool)$item->is_valid,
            'updatedAt' => $item->updated_at,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(8, '更新费用配置失败：' . $e->getMessage(), [
            'title' => '费用配置管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED,
            'trace' => $e->getTraceAsString(),
        ]);
        return json_fail('更新失败');
    }
}

    /**
 * 获取详情 show
 *
 * 功能描述：根据ID获取指定费用配置的详细信息
 *
 * 传入参数：
 * - id (int): 费用配置记录ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 费用配置详细信息
 *   - id (int): 记录ID
 *   - sort (int): 排序值
 *   - caseType (array): 项目类型
 *   - businessType (array): 业务类型
 *   - applyType (array): 申请类型
 *   - country (array): 国家(地区)
 *   - feeType (string): 费用类型
 *   - feeName (string): 费用名称
 *   - feeNameEn (string): 英文费用名称
 *   - currency (string): 货币
 *   - feeCode (string): 费用代码
 *   - baseFee (float): 基础费用
 *   - smallEntityFee (float): 小实体费用
 *   - microEntityFee (float): 微实体费用
 *   - role (array): 角色
 *   - useStage (array): 使用阶段
 *   - isValid (boolean): 是否有效
 *   - sortOrder (int): 排序顺序
 *   - updatedBy (string): 更新人
 *   - updatedAt (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找费用配置记录
        $item = FeeConfig::find($id);

        // 检查记录是否存在
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应和详细信息
        return json_success('获取详情成功', [
            'id' => $item->id,
            'sort' => $item->sort,
            'caseType' => $item->case_type,
            'businessType' => $item->business_type,
            'applyType' => $item->apply_type,
            'country' => $item->country,
            'feeType' => $item->fee_type,
            'feeName' => $item->fee_name,
            'feeNameEn' => $item->fee_name_en,
            'currency' => $item->currency,
            'feeCode' => $item->fee_code,
            'baseFee' => $item->base_fee,
            'smallEntityFee' => $item->small_entity_fee,
            'microEntityFee' => $item->micro_entity_fee,
            'role' => $item->role,
            'useStage' => $item->use_stage,
            'isValid' => (bool)$item->is_valid,
            'sortOrder' => $item->sort_order,
            'updatedBy' => $item->updater->real_name ?? '',
            'updatedAt' => $item->updated_at,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(8, '获取费用配置详情失败：' . $e->getMessage(), [
            'title' => '费用配置管理',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED,
            'trace' => $e->getTraceAsString(),
        ]);
        return json_fail('获取详情失败');
    }
}

}
