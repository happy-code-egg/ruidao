<?php

namespace App\Http\Controllers\Api;

use App\Models\TechServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 科技服务事项设置控制器
 *
 * 功能:
 * - 继承 BaseDataConfigController，提供基础 CRUD（由父类实现）
 * - 扩展按类型/地区获取事项列表的查询接口
 * - 重写 index，支持地区/类型/关键字/状态筛选与分页
 *
 * 相关接口:
 * - GET /data-config/tech-service-items                   列表（本类 index）
 * - GET /data-config/tech-service-items/type/{typeId}     按类型查询（本类 getByTypeId）
 * - GET /data-config/tech-service-items/region/{regionId}  按地区查询（本类 getByRegionId）
 * - POST /data-config/tech-service-items                  新增（父类 store）
 * - GET /data-config/tech-service-items/{id}              详情（父类 show）
 * - PUT /data-config/tech-service-items/{id}              更新（父类 update）
 * - DELETE /data-config/tech-service-items/{id}           删除（父类 destroy）
 */
class TechServiceItemsController extends BaseDataConfigController
{
    /**
     * 指定当前控制器对应的模型类
     *
     * 功能:
     * - 返回本控制器所绑定的数据模型类名。
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - string 模型类名（`TechServiceItem::class`）
     *
     * 接口:
     * - 无（内部使用）
     */
    protected function getModelClass()
    {
        return TechServiceItem::class;
    }

    /**
     * 返回新增/更新时的字段校验规则
     *
     * 功能:
     * - 根据是否为更新操作，动态生成唯一性等验证规则。
     *
     * 请求参数:
     * - bool $isUpdate 是否为更新操作（默认 false）。
     *
     * 返回参数:
     * - array 验证规则数组（用于父类的 store/update）。
     *
     * 字段说明:
     * - tech_service_region_id: 必填，整数，存在于 tech_service_regions 表。
     * - name: 必填，字符串，最长 200。
     * - code: 必填，字符串，最长 50，需唯一（新增时全局唯一，更新时排除当前 ID）。
     * - description: 可选，字符串。
     * - expected_start_date/internal_deadline/official_deadline: 可选，日期。
     * - status: 必填，枚举 0/1。
     * - sort_order: 可选，整数，最小 0。
     * - updater: 可选，字符串，最长 100。
     *
     * 接口:
     * - 无（内部使用，被父类的新增/更新接口调用）。
     *
     * 内部说明:
     * - 当 $isUpdate 为 true 时，从路由参数 `id` 读取当前记录 ID，用于设置 `code` 的唯一约束排除当前记录。
     * - 当 $isUpdate 为 false 时，设置 `code` 全局唯一。
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'tech_service_region_id' => 'required|integer|exists:tech_service_regions,id',
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'expected_start_date' => 'nullable|date',
            'internal_deadline' => 'nullable|date',
            'official_deadline' => 'nullable|date',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['code'] .= '|unique:tech_service_items,code,' . $id;
        } else {
            $rules['code'] .= '|unique:tech_service_items,code';
        }

        return $rules;
    }

    /**
     * 返回字段验证消息
     *
     * 功能:
     * - 合并父类的默认提示消息，补充本模型相关的字段提示。
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - array 验证消息数组。
     *
     * 接口:
     * - 无（内部使用，被父类的新增/更新接口调用）。
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'tech_service_region_id.required' => '科技服务地区不能为空',
            'tech_service_region_id.exists' => '科技服务地区不存在',
            'name.required' => '处理事项名称不能为空',
            'code.required' => '处理事项编码不能为空',
            'code.unique' => '处理事项编码已存在',
        ]);
    }

    /**
     * 根据科技服务类型ID获取处理事项列表
     *
     * 功能:
     * - 按 `tech_service_type_id` 过滤处理事项，并支持状态与关键字筛选。
     *
     * 请求参数:
     * - path: int $typeId 科技服务类型 ID。
     * - query: string|null keyword 关键字（匹配名称/编码/描述）。
     * - query: int|string|null status 状态（0/1）。
     *
     * 返回参数:
     * - 成功: array 事项列表（包含 `techServiceType`、`techServiceRegion` 关系）。
     * - 失败: { message: string } 错误信息。
     *
     * 接口:
     * - GET /data-config/tech-service-items/type/{typeId}
     *
     * 内部说明:
     * - 构建查询：按类型、状态与关键字过滤；
     * - 预加载关联并按 `ordered()` 排序；
     * - 捕获异常并返回统一错误结构。
     */
    public function getByTypeId(Request $request, $typeId)
    {
        try {
            $query = TechServiceItem::where('tech_service_type_id', $typeId);

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            $items = $query->with(['techServiceType', 'techServiceRegion'])->ordered()->get();

            return json_success('获取处理事项列表成功', $items);

        } catch (\Exception $e) {
            return json_error('获取处理事项列表失败：' . $e->getMessage());
        }
    }

    /**
     * 根据科技服务地区ID获取处理事项列表
     *
     * 功能:
     * - 按 `tech_service_region_id` 过滤处理事项，并支持状态与关键字筛选。
     * - 返回经过字段映射与日期格式化的结构化数据。
     *
     * 请求参数:
     * - path: int $regionId 科技服务地区 ID。
     * - query: string|null keyword 关键字（匹配名称/编码/描述）。
     * - query: int|string|null status 状态（0/1）。
     *
     * 返回参数:
     * - 成功: array 事项列表（含地区子对象，日期字段格式为 `Y-m-d`）。
     * - 失败: { message: string } 错误信息。
     *
     * 接口:
     * - GET /data-config/tech-service-items/region/{regionId}
     *
     * 内部说明:
     * - 构建查询：按地区、状态与关键字过滤；
     * - 预加载关联并按 `ordered()` 排序；
     * - 通过 `map` 映射返回字段并格式化日期；
     * - 失败时记录异常并返回统一失败结构。
     */
    public function getByRegionId(Request $request, $regionId)
    {
        try {
            $query = TechServiceItem::where('tech_service_region_id', $regionId);

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            $items = $query->with(['techServiceType', 'techServiceRegion'])->ordered()->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tech_service_region_id' => $item->tech_service_region_id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'description' => $item->description,
                    'expected_start_date' => $item->expected_start_date ? $item->expected_start_date->format('Y-m-d') : null,
                    'internal_deadline' => $item->internal_deadline ? $item->internal_deadline->format('Y-m-d') : null,
                    'official_deadline' => $item->official_deadline ? $item->official_deadline->format('Y-m-d') : null,
                    'status' => $item->status,
                    'status_text' => $item->status_text,
                    'sort_order' => $item->sort_order,
                    'updater' => $item->updater,
                    'updated_at' => $item->updated_at,
                    'tech_service_region' => $item->techServiceRegion ? [
                        'id' => $item->techServiceRegion->id,
                        'apply_type' => $item->techServiceRegion->apply_type,
                        'service_name' => $item->techServiceRegion->service_name,
                        'main_area' => $item->techServiceRegion->main_area,
                        'project_year' => $item->techServiceRegion->project_year
                    ] : null
                ];
            });

            return json_success('获取处理事项列表成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 列表查询（重写）
     *
     * 功能:
     * - 支持按地区、类型、关键字、状态筛选；
     * - 支持分页与关联数据预加载；
     * - 返回统一分页结构。
     *
     * 请求参数:
     * - query: int|string|null tech_service_region_id 地区 ID。
     * - query: int|string|null tech_service_type_id 类型 ID（兼容旧逻辑）。
     * - query: string|null keyword 关键字（匹配名称/编码/描述）。
     * - query: int|string|null status 状态（0/1）。
     * - query: int page 页码，默认 1。
     * - query: int limit 每页数量，默认 15。
     *
     * 返回参数:
     * - 成功: {
     *     list: array 列表数据，含 `tech_service_region` 与 `tech_service_type` 子对象，
     *     total: int 总数,
     *     current_page: int 当前页,
     *     per_page: int 每页数量,
     *     last_page: int 最后一页
     *   }
     * - 失败: { message: string } 错误信息。
     *
     * 接口:
     * - GET /data-config/tech-service-items
     *
     * 内部说明:
     * - 构建查询并按请求参数添加筛选；
     * - `with` 预加载地区与类型关联，`ordered()` 排序；
     * - 使用 `paginate` 返回分页结果并映射字段；
     * - 捕获异常并返回统一失败结构。
     */
    public function index(Request $request)
    {
        try {
            $query = TechServiceItem::query();

            // 按科技服务地区筛选
            if ($request->has('tech_service_region_id') && !empty($request->tech_service_region_id)) {
                $query->where('tech_service_region_id', $request->tech_service_region_id);
            }

            // 兼容旧的科技服务类型筛选（如果需要）
            if ($request->has('tech_service_type_id') && !empty($request->tech_service_type_id)) {
                $query->where('tech_service_type_id', $request->tech_service_type_id);
            }

            // 关键字搜索
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('code', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                });
            }

            // 状态筛选
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // 分页参数
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            // 排序
            $query->with(['techServiceRegion', 'techServiceType'])->ordered();

            // 分页查询
            $result = $query->paginate($limit, ['*'], 'page', $page);

            $data = $result->items();
            $data = collect($data)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tech_service_region_id' => $item->tech_service_region_id,
                    'tech_service_type_id' => $item->tech_service_type_id, // 保留兼容性
                    'name' => $item->name,
                    'code' => $item->code,
                    'description' => $item->description,
                    'expected_start_date' => $item->expected_start_date ? $item->expected_start_date->format('Y-m-d') : null,
                    'internal_deadline' => $item->internal_deadline ? $item->internal_deadline->format('Y-m-d') : null,
                    'official_deadline' => $item->official_deadline ? $item->official_deadline->format('Y-m-d') : null,
                    'status' => $item->status,
                    'status_text' => $item->status_text,
                    'sort_order' => $item->sort_order,
                    'updater' => $item->updater,
                    'updated_at' => $item->updated_at,
                    'tech_service_region' => $item->techServiceRegion ? [
                        'id' => $item->techServiceRegion->id,
                        'apply_type' => $item->techServiceRegion->apply_type,
                        'service_name' => $item->techServiceRegion->service_name,
                        'main_area' => $item->techServiceRegion->main_area,
                        'project_year' => $item->techServiceRegion->project_year
                    ] : null,
                    'tech_service_type' => $item->techServiceType ? [
                        'id' => $item->techServiceType->id,
                        'name' => $item->techServiceType->name,
                        'apply_type' => $item->techServiceType->apply_type
                    ] : null
                ];
            });

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'last_page' => $result->lastPage()
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }
}
