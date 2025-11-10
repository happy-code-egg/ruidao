<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 产品设置控制器
 *
 * 功能:
 * - 管理产品配置数据的增删改查及选项列表
 *
 * 路由说明:
 * - GET  /api/data-config/products                 (接口名: api.products.index)
 * - GET  /api/data-config/products/options         (接口名: api.products.options)
 * - POST /api/data-config/products                 (接口名: api.products.store)
 * - GET  /api/data-config/products/{id}            (接口名: api.products.show)
 * - PUT  /api/data-config/products/{id}            (接口名: api.products.update)
 * - DELETE /api/data-config/products/{id}          (接口名: api.products.destroy)
 * - POST /api/data-config/products/batch-status    (接口名: api.products.batch.status) [方法来自父类]
 *
 * 统一返回:
 * - 成功: `json_success(message, data)`
 * - 失败: `json_fail(message)`
 *
 * 依赖:
 * - 模型 `App\Models\Product`
 * - 请求对象 `Illuminate\Http\Request`
 * - 验证器 `Illuminate\Support\Facades\Validator`
 *
 * 内部说明:
 * - 部分通用方法继承自 `BaseDataConfigController`（如 `batchUpdateStatus`）。
 */
class ProductController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     *
     * 功能:
     * - 返回当前控制器所使用的 Eloquent 模型类名
     *
     * 返回参数:
     * - string 模型类名，例如 `App\Models\Product`
     *
     * 内部说明:
     * - 供父类通用逻辑（增删改查）动态加载模型使用
     */
    protected function getModelClass()
    {
        return Product::class;
    }

    /**
     * 获取验证规则
     *
     * 功能:
     * - 返回创建或更新场景下的字段验证规则
     *
     * 路由说明:
     * - 创建: POST `/api/data-config/products`
     * - 更新: PUT  `/api/data-config/products/{id}`
     *
     * 参数:
     * - bool $isUpdate 是否为更新场景（更新时放宽唯一约束）
     *
     * 字段规则示例:
     * - sort: 可空、整数、最小值1
     * - product_code: 必填、字符串、最长100、唯一
     * - project_type: 必填、字符串、最长100
     * - apply_type: 必填、字符串、最长100
     * - specification: 可空、字符串、最长200
     * - product_name: 必填、字符串、最长200
     * - official_fee / standard_price / min_price: 可空、数值、最小值0
     * - is_valid: 可空、布尔
     * - update_user: 可空、字符串、最长100
     *
     * 返回参数:
     * - array 验证规则数组
     *
     * 内部说明:
     * - 当 `$isUpdate=true` 时，使用路由参数 `{id}` 排除唯一性检查；
     *   兼容可能的路由参数名 `product`（优先取 `id`）。
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'product_code' => 'required|string|max:100',
            'project_type' => 'required|string|max:100',
            'apply_type' => 'required|string|max:100',
            'specification' => 'nullable|string|max:200',
            'product_name' => 'required|string|max:200',
            'official_fee' => 'nullable|numeric|min:0',
            'standard_price' => 'nullable|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'is_valid' => 'nullable|boolean',
            'update_user' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            // 更新时排除当前记录的唯一性检查
            $id = request()->route('id') ?? request()->route('product');
            $rules['product_code'] .= '|unique:products,product_code,' . $id;
        } else {
            $rules['product_code'] .= '|unique:products,product_code';
        }

        return $rules;
    }

    /**
     * 获取验证消息
     *
     * 功能:
     * - 定义并返回自定义字段验证失败时的错误提示文案
     *
     * 返回参数:
     * - array 验证消息数组（与 `getValidationRules` 字段对应）
     *
     * 内部说明:
     * - 与父类默认消息合并，优先级以当前控制器定义为准
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'product_code.required' => '产品编号不能为空',
            'product_code.unique' => '产品编号已存在',
            'project_type.required' => '项目类型不能为空',
            'apply_type.required' => '申请类型不能为空',
            'product_name.required' => '产品名称不能为空',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序值不能小于1',
            'official_fee.numeric' => '参考官费必须是数值',
            'official_fee.min' => '参考官费不能小于0',
            'standard_price.numeric' => '标准定价必须是数值',
            'standard_price.min' => '标准定价不能小于0',
            'min_price.numeric' => '最低售价必须是数值',
            'min_price.min' => '最低售价不能小于0',
        ]);
    }

    /**
     * 获取列表
     *
     * 功能:
     * - 查询产品配置列表，支持筛选与分页
     *
     * 路由说明:
     * - GET `/api/data-config/products` (接口名: api.products.index)
     *
     * 请求参数:
     * - case_type: string|array 项目类型筛选（支持传 `all` 表示不过滤）
     * - apply_type: string 申请类型关键字
     * - product_name: string 产品名称关键字
     * - is_valid: int|bool 是否有效（0/1 或 true/false）
     * - page: int 页码，默认1
     * - limit: int 每页条数，默认10，最大100
     *
     * 返回参数:
     * - list: array 列表数据（字段: id, sort, productCode, projectType, applyType, specification, productName, officialFee, standardPrice, minPrice, isValid, updateUser, updateTime）
     * - total: int 总数
     * - page: int 当前页码
     * - limit: int 每页条数
     * - pages: int 总页数
     *
     * 异常处理:
     * - 捕获异常并记录日志 `log_exception`，返回 `json_fail('获取列表失败')`
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // 项目类型搜索
            if ($request->has('case_type') && !empty($request->case_type)) {
                $caseTypes = is_array($request->case_type) ? $request->case_type : [$request->case_type];
                if (!in_array('all', $caseTypes)) {
                    $query->whereIn('project_type', $caseTypes);
                }
            }

            // 申请类型搜索
            if ($request->has('apply_type') && !empty($request->apply_type)) {
                $query->where('apply_type', 'like', "%{$request->apply_type}%");
            }

            // 产品名称搜索
            if ($request->has('product_name') && !empty($request->product_name)) {
                $query->where('product_name', 'like', "%{$request->product_name}%");
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
            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort ?? 1,
                                 'productCode' => $item->product_code,
                                 'projectType' => $item->project_type,
                                 'applyType' => $item->apply_type,
                                 'specification' => $item->specification,
                                 'productName' => $item->product_name,
                                 'officialFee' => $item->official_fee,
                                 'standardPrice' => $item->standard_price,
                                 'minPrice' => $item->min_price,
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
            log_exception($e, '获取产品配置列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建
     *
     * 功能:
     * - 创建一条产品配置记录
     *
     * 路由说明:
     * - POST `/api/data-config/products` (接口名: api.products.store)
     *
     * 请求参数:
     * - product_code, project_type, apply_type, specification, product_name,
     *   official_fee, standard_price, min_price, is_valid, sort, update_user
     *
     * 返回参数:
     * - 创建成功后返回创建记录的字段（id、基础字段与 `updateTime`）
     *
     * 异常处理:
     * - 验证失败返回 `json_fail(错误信息)`；
     * - 其他异常记录业务日志并返回 `json_fail('创建失败')`
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

            $item = Product::create($data);

            return json_success('创建成功', [
                'id' => $item->id,
                'sort' => $item->sort,
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            $this->log(8, "创建产品配置失败：{$e->getMessage()}", [
                'title' => '产品配置',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     *
     * 功能:
     * - 更新指定产品配置记录
     *
     * 路由说明:
     * - PUT `/api/data-config/products/{id}` (接口名: api.products.update)
     *
     * 参数:
     * - Request $request 请求对象
     * - int $id 记录ID
     *
     * 请求参数:
     * - 与创建字段一致；`product_code` 在更新场景下唯一性忽略当前ID
     *
     * 返回参数:
     * - 更新成功后返回更新后的记录字段（id、基础字段与 `updateTime`）
     *
     * 异常处理:
     * - 记录异常日志并返回 `json_fail('更新失败')`
     */
    public function update(Request $request, $id)
    {
        try {
            $item = Product::find($id);

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
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user,
                'updateTime' => $item->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            log_exception($e, '更新产品配置失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 获取详情
     *
     * 功能:
     * - 获取指定产品配置的详情
     *
     * 路由说明:
     * - GET `/api/data-config/products/{id}` (接口名: api.products.show)
     *
     * 参数:
     * - int $id 记录ID
     *
     * 返回参数:
     * - 单条记录的详细字段（id、基础字段与 `updateTime`）
     *
     * 异常处理:
     * - 记录异常日志并返回 `json_fail('获取详情失败')`
     */
    public function show($id)
    {
        try {
            $item = Product::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', [
                'id' => $item->id,
                'sort' => $item->sort ?? 1,
                'productCode' => $item->product_code,
                'projectType' => $item->project_type,
                'applyType' => $item->apply_type,
                'specification' => $item->specification,
                'productName' => $item->product_name,
                'officialFee' => $item->official_fee,
                'standardPrice' => $item->standard_price,
                'minPrice' => $item->min_price,
                'isValid' => (bool)$item->is_valid,
                'updateUser' => $item->update_user ?? '系统记录',
                'updateTime' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取产品配置详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 删除
     *
     * 功能:
     * - 删除指定产品配置记录
     *
     * 路由说明:
     * - DELETE `/api/data-config/products/{id}` (接口名: api.products.destroy)
     *
     * 参数:
     * - int $id 记录ID
     *
     * 返回参数:
     * - 成功返回 `json_success('删除成功')`
     *
     * 异常处理:
     * - 记录异常日志并返回 `json_fail('删除失败')`
     */
    public function destroy($id)
    {
        try {
            $item = Product::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除产品配置失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     *
     * 功能:
     * - 获取有效产品的下拉选项列表
     *
     * 路由说明:
     * - GET `/api/data-config/products/options` (接口名: api.products.options)
     *
     * 请求参数:
     * - 无（当前实现不需要传参）
     *
     * 返回参数:
     * - 数组: 每项包含 `id`, `value`, `label`, `productCode`, `projectType`, `applyType`
     *
     * 异常处理:
     * - 记录异常日志并返回 `json_fail('获取选项列表失败')`
     */
    public function options(Request $request = null)
    {
        try {
            $data = Product::where('is_valid', true)
                          ->orderBy('sort')
                          ->orderBy('id')
                          ->get()
                          ->map(function ($item) {
                              return [
                                  'id' => $item->id,
                                  'value' => $item->id,
                                  'label' => $item->product_name,
                                  'productCode' => $item->product_code,
                                  'projectType' => $item->project_type,
                                  'applyType' => $item->apply_type,
                              ];
                          });

            return json_success('获取选项列表成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取产品选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }
}
