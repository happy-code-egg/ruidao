<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InvoiceServicesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return InvoiceService::class;
    }

   /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义开票服务类型的验证规则
 *
 * 传入参数：
 * - isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - service_name (string): 开票服务内容名称，必填，最大200字符
 *   - description (string): 描述，可为空
 *   - is_valid (int): 是否有效，可为空，值为0或1
 *   - sort_order (int): 排序，必填，最小值0
 *   - service_code (string): 服务代码，更新时需唯一
 */
protected function getValidationRules($isUpdate = false)
{
    $rules = [
        'service_name' => 'required|string|max:200',
        'description' => 'nullable|string',
        'is_valid' => 'nullable|in:0,1',
        'sort_order' => 'required|integer|min:0'
    ];

    if ($isUpdate) {
        $id = request()->route('id');
        $rules['service_code'] .= '|unique:invoice_services,service_code,' . $id . ',id';
    }

    return $rules;
}

/**
 * 获取验证错误信息 getValidationMessages
 *
 * 功能描述：定义开票服务类型验证错误的自定义消息
 *
 * 输出参数：
 * - array: 验证错误信息数组
 */
protected function getValidationMessages()
{
    return array_merge(parent::getValidationMessages(), [
        'service_name.required' => '开票服务内容名称不能为空',
        'service_name.max' => '开票服务内容名称长度不能超过200个字符',
        'sort_order.required' => '排序值不能为空',
        'sort_order.integer' => '排序值必须是整数',
        'sort_order.min' => '排序值不能小于0',
    ]);
}

/**
 * 重写index方法以支持特定的搜索条件 index
 *
 * 功能描述：获取开票服务类型列表，支持搜索和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - service_name (string, optional): 开票服务内容名称搜索条件
 *   - is_valid (int, optional): 是否有效筛选条件
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 开票服务类型列表数据
 *     - id (int): ID
 *     - service_name (string): 开票服务内容名称
 *     - service_code (string): 服务代码
 *     - description (string): 描述
 *     - is_valid (int): 是否有效
 *     - sort_order (int): 排序
 *     - created_by (string): 创建人
 *     - updated_by (string): 更新人
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(\Illuminate\Http\Request $request)
{
    try {
        $query = InvoiceService::query();

        // 开票服务内容名称搜索
        if ($request->has('service_name') && !empty($request->service_name)) {
            $query->where('service_name', 'like', '%' . $request->service_name . '%');
        }

        // 是否有效筛选
        if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
            $query->where('is_valid', $request->is_valid);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询，按排序和ID排序
        $data = $query->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get()
                     ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'service_name' => $item->service_name,
                            'service_code' => $item->service_code,
                            'description' => $item->description,
                            'is_valid' => $item->is_valid,
                            'sort_order' => $item->sort_order,
                            'created_by' => $item->creator->real_name ?? '',
                            'updated_by' => $item->updater->real_name ?? '',
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
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
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取开票服务类型列表失败：{$e->getMessage()}",
            [
                'title' => '开票服务类型列表',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取列表失败');
    }
}



 /**
 * 创建 store
 *
 * 功能描述：创建新的开票服务类型记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象，包含开票服务类型数据
 *   - service_name (string): 开票服务内容名称，必填，最大200字符
 *   - description (string, optional): 描述
 *   - is_valid (int, optional): 是否有效，值为0或1，默认为1
 *   - sort_order (int): 排序，必填，最小值0
 *   - service_code (string): 服务代码
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建的开票服务类型对象
 *   - id (int): ID
 *   - service_name (string): 开票服务内容名称
 *   - service_code (string): 服务代码
 *   - description (string): 描述
 *   - is_valid (int): 是否有效
 *   - created_by (string): 创建人
 *   - updated_by (string): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function store(\Illuminate\Http\Request $request)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), $this->getValidationRules(), $this->getValidationMessages());

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 获取所有请求数据
        $data = $request->all();

        // 设置创建人和更新人
        $data['created_by'] = Auth::user()->id ?? 1;
        $data['updated_by'] = Auth::user()->id ?? 1;
        // 设置是否有效，默认为1(有效)
        $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : 1;

        // 创建开票服务类型记录
        $item = InvoiceService::create($data);

        // 返回成功响应
        return json_success('创建成功', [
            'id' => $item->id,
            'service_name' => $item->service_name,
            'service_code' => $item->service_code,
            'description' => $item->description,
            'is_valid' => $item->is_valid,
            'created_by' => $item->creator->real_name ?? '',
            'updated_by' => $item->updater->real_name ?? '',
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(8, "创建申请类型配置失败：{$e->getMessage()}", [
            'title' => '申请类型配置',
            'error' => $e->getMessage(),
            'status' => \App\Models\Logs::STATUS_FAILED
        ]);
        return json_fail('创建失败');
    }
}

/**
 * 更新 update
 *
 * 功能描述：更新指定ID的开票服务类型记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象，包含开票服务类型数据
 *   - service_name (string): 开票服务内容名称，必填，最大200字符
 *   - description (string, optional): 描述
 *   - is_valid (int, optional): 是否有效，值为0或1
 *   - sort_order (int): 排序，必填，最小值0
 *   - service_code (string): 服务代码，更新时需唯一
 * - id (int): 开票服务类型ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的开票服务类型对象
 *   - id (int): ID
 *   - service_name (string): 开票服务内容名称
 *   - service_code (string): 服务代码
 *   - description (string): 描述
 *   - is_valid (int): 是否有效
 *   - created_by (string): 创建人
 *   - updated_by (string): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function update(\Illuminate\Http\Request $request, $id)
{
    try {
        // 查找要更新的开票服务类型记录
        $item = InvoiceService::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证输入参数
        $validator = Validator::make($request->all(), $this->getValidationRules(true), $this->getValidationMessages());

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 获取所有请求数据
        $data = $request->all();
        // 保留原有的创建人
        $data['created_by'] = $item->created_by ?? 1;
        // 设置更新人
        $data['updated_by'] = Auth::user()->id ?? 1;
        // 设置是否有效，如果未提供则保持原值
        $data['is_valid'] = isset($data['is_valid']) ? (bool)$data['is_valid'] : $item->is_valid;

        // 更新开票服务类型记录
        $item->update($data);

        // 返回成功响应
        return json_success('更新成功', [
            'id' => $item->id,
            'service_name' => $item->service_name,
            'service_code' => $item->service_code,
            'description' => $item->description,
            'is_valid' => $item->is_valid,
            'created_by' => $item->creator->real_name ?? '',
            'updated_by' => $item->updater->real_name ?? '',
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "更新开票服务类型失败：{$e->getMessage()}",
            [
                'title' => '开票服务类型',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('更新失败');
    }
}


 /**
 * 获取详情 show
 *
 * 功能描述：根据ID获取开票服务类型的详细信息
 *
 * 传入参数：
 * - id (int): 开票服务类型ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 开票服务类型详细信息
 *   - id (int): ID
 *   - service_name (string): 开票服务内容名称
 *   - service_code (string): 服务代码
 *   - description (string): 描述
 *   - is_valid (int): 是否有效
 *   - created_by (string): 创建人
 *   - updated_by (string): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找开票服务类型记录
        $item = InvoiceService::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应，包含详细信息
        return json_success('获取详情成功', [
            'id' => $item->id,
            'service_name' => $item->service_name,
            'service_code' => $item->service_code,
            'description' => $item->description,
            'is_valid' => $item->is_valid,
            'created_by' => $item->creator->real_name ?? '',
            'updated_by' => $item->updater->real_name ?? '',
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取开票服务类型详情失败：{$e->getMessage()}",
            [
                'title' => '开票服务类型',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取详情失败');
    }
}

}
