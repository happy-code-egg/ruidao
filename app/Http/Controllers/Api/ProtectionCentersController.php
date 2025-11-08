<?php

namespace App\Http\Controllers\Api;

use App\Models\ProtectionCenters;
use Illuminate\Http\Request;

/**
 * 保护中心（ProtectionCenters）基础数据配置控制器
 *
 * 功能:
 * - 为父类 `BaseDataConfigController` 提供模型绑定与字段校验规则
 * - 提供列表查询（支持中心名称、关键字、状态筛选）并返回统一分页结构
 *
 * 路由说明:
 * - 已在 `routes/api.php` 绑定如下接口前缀：`/api/data-config/protection-centers*`
 *   - GET `/api/data-config/protection-centers` → index
 *   - GET `/api/data-config/protection-centers/options` → options（由父类提供）
 *   - POST `/api/data-config/protection-centers` → store
 *   - GET `/api/data-config/protection-centers/{id}` → show
 *   - PUT `/api/data-config/protection-centers/{id}` → update
 *   - DELETE `/api/data-config/protection-centers/{id}` → destroy
 *   - POST `/api/data-config/protection-centers/batch-status` → batchUpdateStatus（由父类提供）
 * - 另有测试路由：`/api/test-protection-centers*` 指向本控制器，供联调使用
 *
 * 统一响应:
 * - 成功：`json_success(msg, data)` → `{ code: 0, msg, data, success: true }`
 * - 失败：`json_fail(msg, data)` → `{ code: 1, msg, data, success: false }`
 *
 * 依赖:
 * - 模型 `App\Models\ProtectionCenters`
 * - 继承 `BaseDataConfigController` 的通用 CRUD 能力和统一校验/响应结构
 */
class ProtectionCentersController extends BaseDataConfigController
{
    /**
     * 获取模型类
     *
     * 功能:
     * - 指定当前控制器使用的 Eloquent 模型类（FQCN）
     *
     * 返回参数:
     * - `string` 模型类名：`App\Models\ProtectionCenters`
     */
    protected function getModelClass()
    {
        return ProtectionCenters::class;
    }

    /**
     * 获取创建/更新的校验规则
     *
     * 功能:
     * - 返回用于 `store`/`update` 的字段校验规则，更新场景根据 `id` 放宽唯一性
     *
     * 适用接口:
     * - POST `/api/data-config/protection-centers`（创建）
     * - PUT `/api/data-config/protection-centers/{id}`（更新）
     *
     * 请求参数（按当前规则）:
     * - `sort` 可选，整数，最小 1
     * - `name` 可选，字符串，最长 100
     * - `code` 可选，字符串，最长 50，更新时保持唯一（`protection_centers.code`）
     * - `center_name` 可选，字符串，最长 200
     * - `description` 可选，字符串
     * - `status` 必填，枚举 `0/1`
     *
     * 返回参数:
     * - `array` 验证规则数组，供 `Validator` 使用
     *
     * 内部说明:
     * - 更新规则中对 `code` 的唯一性校验排除当前记录 `id`
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'sort' => 'nullable|integer|min:1',
            'name' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50',
            'center_name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:protection_centers,code,' . $id . ',id';
        }

        return $rules;
    }

    /**
     * 获取校验消息
     *
     * 功能:
     * - 返回字段校验的定制化消息，默认合并父类通用消息
     *
     * 返回参数:
     * - `array` 键值对形式的验证消息
     *
     * 内部说明:
     * - 可在此处覆盖或追加特定字段的错误提示；当前示例对 `name.required` 提供了中文提示（根据业务可调整 `name` 为必填）
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '保护中心名称不能为空'
        ]);
    }

    /**
     * 重写 index 支持 center_name 与 keyword/status 筛选
     *
     * 功能:
     * - 列表查询，支持以下筛选条件并返回分页结构
     *
     * 接口:
     * - GET `/api/data-config/protection-centers`
     * - 测试接口：GET `/api/test-protection-centers`
     *
     * 请求参数:
     * - `center_name` 可选，字符串，按中心名称与名称模糊匹配
     * - `keyword` 可选，字符串，按 `name/code/description/center_name` 模糊匹配
     * - `status` 可选，枚举 `0/1`
     * - `page` 可选，整数，默认 1，最小 1
     * - `limit` 可选，整数，默认 15，范围 1..100
     *
     * 返回参数（json_success）:
     * - `list` 列表数组：包含 `id/sort/name/code/center_name/description/status/status_text/created_at/updated_at/created_by/updated_by`
     * - `total` 总记录数
     * - `page` 当前页
     * - `limit` 每页数量
     * - `pages` 总页数（向下取整）
     *
     * 异常:
     * - 捕获异常并记录日志 `log_exception`，返回 `json_fail('获取列表失败')`
     */
    public function index(Request $request)
    {
        try {
            $query = ProtectionCenters::query();

            if ($request->has('center_name') && $request->center_name !== '') {
                $name = $request->center_name;
                $query->where(function ($q) use ($name) {
                    $q->where('center_name', 'like', "%{$name}%")
                      ->orWhere('name', 'like', "%{$name}%");
                });
            }

            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('center_name', 'like', "%{$keyword}%");
                });
            }

            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            $total = $query->count();

            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'sort' => $item->sort,
                                'name' => $item->name,
                                'code' => $item->code,
                                'center_name' => $item->center_name,
                                'description' => $item->description,
                                'status' => $item->status,
                                'status_text' => $item->status_text,
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
            log_exception($e, '获取保护中心列表失败');
            return json_fail('获取列表失败');
        }
    }
}
