<?php

namespace App\Http\Controllers\Api;

use App\Models\ApplyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
申请类型配置控制器
继承基础数据配置控制器，专门处理申请类型相关的配置管理（如增删改查、数据验证等）
/
class ApplyTypeController extends BaseDataConfigController
{
/*
获取申请类型对应的数据模型类名
用于基础数据配置控制器的通用逻辑（如模型实例化、数据查询 / 操作等）
@return string 申请类型模型类的完整命名空间路径
 */
class ApplyTypeController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     */
    protected function getModelClass()
    {
        return ApplyType::class;
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'country' => 'required|string|max:100',
            'case_type' => 'required|string|max:100',
            'apply_type_name' => 'required|string|max:100',
            'apply_type_code' => 'required|string|max:50',
            'is_valid' => 'nullable|boolean',
            'update_user' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id') ?? request()->route('apply_type');
            $rules['apply_type_code'] .= '|unique:apply_types,apply_type_code,' . $id;
        } else {
            $rules['apply_type_code'] .= '|unique:apply_types,apply_type_code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'country.required' => '国家(地区)不能为空',
            'case_type.required' => '项目类型不能为空',
            'apply_type_name.required' => '申请类型名称不能为空',
            'apply_type_code.required' => '申请类型代码不能为空',
            'apply_type_code.unique' => '申请类型代码已存在',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
        ]);
    }

    /**
    获取申请类型列表（重写方法）
    支持多条件组合搜索与分页查询，适配特定业务场景的筛选需求
    请求参数：
    country（所属国家 / 地区）：可选，字符串 / 数组，筛选特定国家 / 地区的申请类型；传 "all" 则不限制
    case_type（案件类型）：可选，字符串 / 数组，筛选特定案件类型的申请类型；传 "all" 则不限制
    apply_type（申请类型名称）：可选，字符串，模糊匹配申请类型名称
    is_valid（是否有效）：可选，布尔值 / 整数，筛选有效状态（空值不筛选）
    page（页码）：可选，整数，默认 1，最小值 1，分页查询的页码
    limit（每页条数）：可选，整数，默认 10，范围 1-100，分页查询的每页记录数
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述
    data：对象，包含列表数据及分页信息
    list：数组，申请类型列表，每个元素包含：
    id：整数，申请类型 ID
    sort：整数，排序值，默认 1
    country：字符串，所属国家 / 地区
    caseType：字符串，案件类型
    applyTypeName：字符串，申请类型名称
    applyTypeCode：字符串，申请类型编码
    isValid：布尔值，是否有效
    updateUser：字符串，更新人，默认 "系统记录"
    updateTime：字符串，更新时间，格式为 "Y-m-d H:i:s"，无更新时间则为空
    total：整数，符合条件的总记录数
    page：整数，当前页码
    limit：整数，当前每页条数
    pages：整数，总页数（向上取整）
    说明：查询结果按排序字段（sort）和 ID 升序排列，返回字段已做驼峰命名转换适配前端
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function index(Request $request)
    {
        try {
            $query = ApplyType::query();

            // 国家(地区)搜索
            if ($request->has('country') && !empty($request->country)) {
                $countries = is_array($request->country) ? $request->country : [$request->country];
                if (!in_array('all', $countries)) {
                    $query->whereIn('country', $countries);
                }
            }

            // 项目类型搜索
            if ($request->has('case_type') && !empty($request->case_type)) {
                $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
                if (!in_array('all', $caseTypes)) {
                    $query->whereIn('case_type', $caseTypes);
                }
            }

            // 申请类型名称搜索
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $query->where('apply_type_name', 'like', "%{$request->apply_type}%");
            }

            // 是否有效搜索
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据，按排序字段排序
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort ?? 1,
                                 'country' => $item->country,
                                 'caseType' => $item->case_type,
                                 'applyTypeName' => $item->apply_type_name,
                                 'applyTypeCode' => $item->apply_type_code,
                                 'isValid' => (bool)$item->is_valid,
                                 'updateUser' => $item->update_user ?? '系统记录',
                                 'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                             ];
                         });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取申请类型配置列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
    创建申请类型
    用于申请类型配置，支持参数验证与默认值填充，返回创建结果
    请求参数：
    sort（排序值）：可选，整数，最小值 1，默认 1
    country（所属国家 / 地区）：必填，字符串，最大 100 字符
    case_type（案件类型）：必填，字符串，最大 100 字符
    apply_type_name（申请类型名称）：必填，字符串，最大 100 字符
    apply_type_code（申请类型编码）：必填，字符串，最大 50 字符，需唯一
    is_valid（是否有效）：可选，布尔值，默认 true
    update_user（更新人）：可选，字符串，最大 100 字符，默认 "系统记录"
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述
    data：对象，创建成功时返回申请类型详情：
    id：整数，新创建的申请类型 ID
    sort：整数，排序值
    country：字符串，所属国家 / 地区
    caseType：字符串，案件类型（驼峰转换）
    applyTypeName：字符串，申请类型名称（驼峰转换）
    applyTypeCode：字符串，申请类型编码（驼峰转换）
    isValid：布尔值，是否有效
    updateUser：字符串，更新人
    updateTime：字符串，创建时间（格式为 "Y-m-d H:i:s"）
    说明：
    参数验证规则通过 getValidationRules () 方法获取，包含必填项、长度限制及编码唯一性校验
    缺失的 sort、update_user、is_valid 参数会自动填充默认值
    异常时会记录错误日志（类型 8），包含错误详情
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['update_user'] = $data['update_user'] ?? '系统记录';
            $data['sort'] = $data['sort'] ?? 1;
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : true;

            $item = ApplyType::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "创建申请类型配置失败：{$e->getMessage()}", [
                'title' => '申请类型配置',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('创建失败');
        }
    }

    /**
    更新申请类型
    根据 ID 更新指定申请类型的信息，支持参数验证与部分字段默认值处理
    请求参数：
    id（申请类型 ID）：必填，整数，通过 URL 路径传递，指定待更新的记录
    sort（排序值）：可选，整数，最小值 1
    country（所属国家 / 地区）：必填，字符串，最大 100 字符
    case_type（案件类型）：必填，字符串，最大 100 字符
    apply_type_name（申请类型名称）：必填，字符串，最大 100 字符
    apply_type_code（申请类型编码）：必填，字符串，最大 50 字符，需唯一（排除当前 ID）
    is_valid（是否有效）：可选，布尔值，默认沿用原记录值
    update_user（更新人）：可选，字符串，最大 100 字符，默认 "系统记录"
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（记录不存在 / 参数错误 / 更新成功 / 更新失败）
    data：对象，更新成功时返回更新后的申请类型详情：
    id：整数，申请类型 ID
    sort：整数，排序值
    country：字符串，所属国家 / 地区
    caseType：字符串，案件类型（驼峰转换）
    applyTypeName：字符串，申请类型名称（驼峰转换）
    applyTypeCode：字符串，申请类型编码（驼峰转换）
    isValid：布尔值，是否有效
    updateUser：字符串，更新人
    updateTime：字符串，更新时间（格式为 "Y-m-d H:i:s"）
    说明：
    首先校验记录是否存在，不存在返回 404 类错误
    验证规则通过 getValidationRules (true) 获取，编码唯一性校验会排除当前 ID
    未传递的 is_valid 参数会沿用原记录值，update_user 默认填充 "系统记录"
    异常时会记录错误日志，包含异常详情
    @param Request $request 请求对象
    @param int $id 申请类型 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function update(Request $request, $id)
    {
        try {
            $item = ApplyType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['update_user'] = $data['update_user'] ?? '系统记录';
            $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

            $item->update($data);

            return json_success('更新成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            log_exception($e, '更新申请类型配置失败');
            return json_fail('更新失败');
        }
    }

    /**
    获取申请类型详情
    根据 ID 查询指定申请类型的详细信息，支持数据格式化返回
    请求参数：
    id（申请类型 ID）：必填，整数，通过 URL 路径传递，指定待查询的记录
    返回参数：
    code：隐含在 json_success/json_fail 方法中，成功为成功码，失败为错误码
    message：字符串，操作结果描述（记录不存在 / 获取成功 / 获取失败）
    data：对象，查询成功时返回申请类型详情：
    id：整数，申请类型 ID
    sort：整数，排序值，默认 1（若数据库中为 null）
    country：字符串，所属国家 / 地区
    caseType：字符串，案件类型（驼峰转换）
    applyTypeName：字符串，申请类型名称（驼峰转换）
    applyTypeCode：字符串，申请类型编码（驼峰转换）
    isValid：布尔值，是否有效
    updateUser：字符串，更新人，默认 "系统记录"（若数据库中为 null）
    updateTime：字符串，更新时间，格式为 "Y-m-d H:i:s"，无更新时间则为空
    说明：
    首先校验记录是否存在，不存在返回对应错误信息
    对可能为 null 的字段（sort、update_user、updated_at）做默认值或格式化处理
    异常时会记录错误日志，包含异常详情
    @param int $id 申请类型 ID
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function show($id)
    {
        try {
            $item = ApplyType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort ?? 1,
                'country' => $item->country,
                'caseType' => $item->case_type,
                'applyTypeName' => $item->apply_type_name,
                'applyTypeCode' => $item->apply_type_code,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user ?? '系统记录',
                'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取申请类型配置详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
    根据案件类型获取所有申请类型选项
    根据指定的案件类型，查询对应的所有申请类型，并格式化为下拉选项结构（value/label）
    请求参数：
    caseType（案件类型）：必填，字符串，通过 URL 路径传递，用于筛选特定案件类型的申请类型
    返回参数：
    code：隐含在 json_success 方法中，成功为成功码
    message：字符串，操作结果描述
    data：数组，申请类型选项列表，每个元素包含：
    id：整数，申请类型 ID
    value：字符串，申请类型名称（作为选项值）
    label：字符串，申请类型名称（作为选项显示文本）
    @param string $caseType 案件类型
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function all($caseType)
    {
        $data = ApplyType::where('case_type', $caseType)->get();
        $data = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'value' => $item->apply_type_name,
                'label' => $item->apply_type_name,
            ];
        });
        return json_success('获取所有申请类型配置成功', $data);
    }
    /**
    根据国家 / 地区获取所有申请类型选项
    根据指定的国家 / 地区，查询对应的所有申请类型，并格式化为下拉选项结构（value/label）
    请求参数：
    country（国家 / 地区）：必填，字符串，通过 URL 路径传递，用于筛选特定国家 / 地区的申请类型
    返回参数：
    code：隐含在 json_success 方法中，成功为成功码
    message：字符串，操作结果描述
    data：数组，申请类型选项列表，每个元素包含：
    id：整数，申请类型 ID
    value：字符串，申请类型名称（作为选项值）
    label：字符串，申请类型名称（作为选项显示文本）
    @param string $country 国家 / 地区
    @return \Illuminate\Http\JsonResponse JSON 响应
     */
    public function allByCountry($country)
    {
        $data = ApplyType::where('country', $country)->get();
        $data = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'value' => $item->apply_type_name,
                'label' => $item->apply_type_name,
            ];
        });
        return json_success('获取所有申请类型配置成功', $data);
    }
}

