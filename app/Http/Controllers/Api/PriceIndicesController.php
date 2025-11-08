<?php

namespace App\Http\Controllers\Api;

use App\Models\PriceIndices;
use Illuminate\Http\Request;

/**
 * 价格指数（PriceIndices）数据配置控制器
 *
 * 功能:
 * - 提供价格指数的数据维护与查询接口，支持列表筛选、创建/更新校验、选项获取等
 * - 继承 `BaseDataConfigController` 复用通用的 CRUD、分页、统一返回与异常处理能力
 *
 * 路由说明（见 routes/api.php）:
 * - GET    /api/data-config/price-indices                 => index           获取列表（支持关键字/状态筛选、分页）
 * - GET    /api/data-config/price-indices/options         => options         获取选项（下拉等，启用状态）
 * - POST   /api/data-config/price-indices                 => store           创建记录（继承父类方法）
 * - GET    /api/data-config/price-indices/{id}            => show            获取详情（继承父类方法）
 * - PUT    /api/data-config/price-indices/{id}            => update          更新记录（继承父类方法）
 * - DELETE /api/data-config/price-indices/{id}            => destroy         删除记录（继承父类方法）
 * - POST   /api/data-config/price-indices/batch-status    => batchUpdateStatus 批量启用/禁用（继承父类方法）
 *
 * 统一返回:
 * - 成功使用 `json_success(msg, data)`；失败使用 `json_fail(msg)`，详见 `app/Helpers/json.php`
 *
 * 依赖:
 * - 模型 `App\Models\PriceIndices`
 * - 父类 `App\Http\Controllers\Api\BaseDataConfigController`
 *
 * 异常处理:
 * - 捕获异常后调用 `log_exception($e, $scene)` 记录错误，返回统一失败响应
 */
class PriceIndicesController extends BaseDataConfigController
{
    /**
     * 获取模型类名
     *
     * 功能:
     * - 向父类通用方法提供当前控制器绑定的 Eloquent 模型
     *
     * 请求参数: 无
     * 返回参数: string|class 模型类名（PriceIndices::class）
     */
    protected function getModelClass()
    {
        return PriceIndices::class;
    }

    /**
     * 获取验证规则
     *
     * 功能:
     * - 定义创建/更新场景下的字段校验规则，更新时放宽唯一约束
     *
     * 路由说明:
     * - 创建: POST `/api/data-config/price-indices`
     * - 更新: PUT  `/api/data-config/price-indices/{id}`
     *
     * 字段规则示例:
     * - name: 必填、字符串、最长100
     * - code: 可空、字符串、最长50、唯一（更新时排除当前记录）
     * - index_name: 必填、字符串、最长200
     * - description: 可空、字符串
     * - base_value/current_value: 可空、数值
     * - status: 必填、枚举 0|1
     * - sort_order: 可空、整数、最小值0
     *
     * 返回参数:
     * - array 验证规则数组
     *
     * 内部说明:
     * - `$isUpdate=true` 时，通过路由参数 `{id}` 排除唯一约束；新增场景直接唯一
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'index_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'base_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:price_indices,code,' . $id;
        } else {
            $rules['code'] .= '|unique:price_indices,code';
        }

        return $rules;
    }

    /**
     * 获取验证提示文案
     *
     * 功能:
     * - 在父类基础上补充或覆盖特定字段的提示信息
     *
     * 返回参数:
     * - array 提示文案数组，如 `name.required => 价格指数名称不能为空`
     *
     * 内部说明:
     * - 使用 `array_merge` 合并父类默认提示与当前控制器的自定义提示
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '价格指数名称不能为空'
        ]);
    }

    /**
     * 列表查询（分页 + 关键字/状态筛选）
     *
     * 功能:
     * - 重写列表获取逻辑，支持对 `name/code/description/index_name` 的模糊搜索
     * - 支持状态筛选与分页，按 `sort_order`、`id` 排序
     *
     * 路由说明:
     * - GET `/api/data-config/price-indices`
     *
     * 请求参数:
     * - keyword: 关键字，匹配 `name/code/description/index_name`
     * - status: 状态筛选，0/1
     * - page: 页码，默认 1，最小 1
     * - limit: 每页条数，默认 15，范围 1-100
     *
     * 返回参数:
     * - list: 数组，每条包含 `id/name/code/index_name/description/base_value/current_value/status/status_text/sort_order/created_at/updated_at/created_by/updated_by`
     * - total/page/limit/pages: 分页元信息
     *
     * 异常处理:
     * - 捕获异常记录日志 `log_exception`，返回统一失败信息
     */
    public function index(Request $request)
    {
        try {
            $query = PriceIndices::query();

            // 关键字
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('index_name', 'like', "%{$keyword}%");
                });
            }

            // 状态
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            $total = $query->count();

            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'code' => $item->code,
                                'index_name' => $item->index_name,
                                'description' => $item->description,
                                'base_value' => $item->base_value ?? '',
                                'current_value' => $item->current_value ?? '',
                                'status' => $item->status,
                                'status_text' => $item->status_text,
                                'sort_order' => $item->sort_order,
                                'created_at' => $item->created_at,
                                'updated_at' => $item->updated_at,
                                'created_by' => $item->creator->real_name ?? '',
                                'updated_by' => $item->updater->real_name ?? '',
                            ];
                         });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($total / $limit)
            ]);
        } catch (\Exception $e) {
            log_exception($e, '获取价格指数列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取选项列表（启用状态，下拉等场景）
     *
     * 功能:
     * - 返回启用状态的价格指数选项，字段为 `value=id`、`label=index_name`
     *
     * 路由说明:
     * - GET `/api/data-config/price-indices/options`
     *
     * 返回参数:
     * - 数组: `[{ value, label }]`
     *
     * 异常处理:
     * - 捕获异常返回统一失败信息
     */
    public function options(\Illuminate\Http\Request $request)
    {
        try {
            $data = \App\Models\PriceIndices::where('status', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->select('id as value', 'index_name as label')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            return json_fail('获取选项列表失败');
        }
    }
}
