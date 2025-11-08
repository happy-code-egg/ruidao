<?php

namespace App\Http\Controllers\Api;

use App\Models\RelatedType;

/**
 * 相关类型设置控制器
 *
 * 功能:
 * - 提供相关类型的基础数据维护：列表、详情、创建、更新、删除、批量状态更新、选项获取
 * - 支持按项目类型、名称、有效状态等筛选与分页
 * - 提供项目类型选项（静态示例）
 *
 * 路由前缀: `/api`
 * 相关接口:
 * - GET `/api/data-config/related-types` （name: api.related.types.index）
 * - GET `/api/data-config/related-types/options` （name: api.related.types.options）
 * - POST `/api/data-config/related-types` （name: api.related.types.store）
 * - GET `/api/data-config/related-types/{id}` （name: api.related.types.show）
 * - PUT `/api/data-config/related-types/{id}` （name: api.related.types.update）
 * - DELETE `/api/data-config/related-types/{id}` （name: api.related.types.destroy）
 * - POST `/api/data-config/related-types/batch-status` （name: api.related.types.batch.status）
 * - GET `/api/config/related-types` （name: api.config.related.types.index）
 * - POST `/api/config/related-types` （name: api.config.related.types.store）
 * - GET `/api/config/related-types/{id}` （name: api.config.related.types.show）
 * - PUT `/api/config/related-types/{id}` （name: api.config.related.types.update）
 * - DELETE `/api/config/related-types/{id}` （name: api.config.related.types.destroy）
 * - GET `/api/config/related-types/options` （name: api.config.related.types.options）
 * - GET `/api/config/related-types/case-type-options` （name: api.config.related.types.case.type.options）
 *
 * 内部说明:
 * - 依赖模型 `RelatedType`
 * - 大部分 CRUD 能力由 `BaseDataConfigController` 提供，本控制器重写 `index` 并新增选项接口
 */
class RelatedTypesController extends BaseDataConfigController
{
    /**
     * 获取模型类
     *
     * 功能:
     * - 为父类 `BaseDataConfigController` 指定当前控制器绑定的数据模型
     *
     * 返回参数:
     * - `string` 模型类名（FQCN），此处为 `App\Models\RelatedType`
     *
     * 内部说明:
     * - 该方法仅供内部使用，不直接暴露为路由接口
     */
    protected function getModelClass()
    {
        return RelatedType::class;
    }

    /**
     * 获取创建/更新的校验规则
     *
     * 功能:
     * - 返回用于 `store`/`update` 的字段校验规则
     *
     * 接口:
     * - POST `/api/data-config/related-types`（创建）
     * - PUT `/api/data-config/related-types/{id}`（更新）
     * - POST `/api/config/related-types`（创建）
     * - PUT `/api/config/related-types/{id}`（更新）
     *
     * 请求参数:
     * - `$isUpdate` 是否更新场景（影响唯一性校验）
     *
     * 返回参数:
     * - `array` 验证规则数组
     *
     * 内部说明:
     * - `type_code` 在创建时唯一，更新时排除当前 `id`
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'case_type' => 'required|string|max:100',
            'type_name' => 'required|string|max:100',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'updater' => 'nullable|string|max:100'
        ];

        if ($isUpdate) {
            $id = request()->route('id');
            $rules['type_code'] .= '|unique:related_types,type_code,' . $id;
        } else {
            $rules['type_code'] .= '|unique:related_types,type_code';
        }

        return $rules;
    }

    /**
     * 获取验证失败提示消息
     *
     * 功能:
     * - 返回字段验证的中文提示文案
     *
     * 返回参数:
     * - `array` 验证消息数组
     *
     * 内部说明:
     * - 合并父类默认消息，并覆盖/补充本控制器特定字段
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'case_type.required' => '项目类型不能为空',
            'case_type.max' => '项目类型长度不能超过100个字符',
            'type_name.required' => '相关类型名称不能为空',
            'type_name.max' => '相关类型名称长度不能超过100个字符',
            'type_code.required' => '相关类型编码不能为空',
            'type_code.unique' => '相关类型编码已存在',
            'type_code.max' => '相关类型编码长度不能超过50个字符',

            'sort_order.integer' => '排序号必须是整数',
            'sort_order.min' => '排序号不能小于0',
        ]);
    }

    /**
     * 重写 index 方法以支持特定搜索条件
     *
     * 接口:
     * - GET `/api/data-config/related-types`（name: api.related.types.index）
     * - GET `/api/config/related-types`（name: api.config.related.types.index）
     *
     * 请求参数:
     * - `case_type` 项目类型（精确匹配）
     * - `type_name` 相关类型名称（模糊匹配）
     * - `is_valid` 是否有效（0/1）
     * - `page` 页码（默认 1，最小 1）
     * - `limit` 每页数量（默认 15，最大 100）
     *
     * 返回参数:
     * - 使用 `json_success` 返回统一结构：
     *   - `code` 0 表示成功
     *   - `msg` 文本消息（如“获取列表成功”）
     *   - `success` 布尔（code===0）
     *   - `data.list` 列表数据
     *   - `data.total` 总数
     *   - `data.page` 当前页
     *   - `data.limit` 每页数量
     *   - `data.pages` 总页数
     *
     * 内部说明:
     * - 按 `sort_order` 与 `id` 排序；分页使用 offset/limit
     */
    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $query = RelatedType::query();

            // 项目类型筛选
            if ($request->has('case_type') && !empty($request->case_type)) {
                $query->where('case_type', $request->case_type);
            }

            // 相关类型名称搜索
            if ($request->has('type_name') && !empty($request->type_name)) {
                $query->where('type_name', 'like', '%' . $request->type_name . '%');
            }

            // 是否有效筛选
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort_order')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取相关类型列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取项目类型选项
     *
     * 接口:
     * - GET `/api/config/related-types/case-type-options`（name: api.config.related.types.case.type.options）
     *
     * 请求参数:
     * - 无
     *
     * 返回参数:
     * - 使用 `json_success` 返回：
     *   - `code` 0 表示成功
     *   - `msg` 文本消息（如“获取项目类型选项成功”）
     *   - `success` 布尔
     *   - `data` 为选项数组，每项含 `value` 与 `label`
     *
     * 内部说明:
     * - 当前为静态示例，后续可改为从配置表或字典表获取
     */
    public function getCaseTypeOptions()
    {
        try {
            $caseTypes = [
                ['value' => '发明专利', 'label' => '发明专利'],
                ['value' => '实用新型', 'label' => '实用新型'],
                ['value' => '外观设计', 'label' => '外观设计'],
                ['value' => '商标', 'label' => '商标'],
                ['value' => '版权', 'label' => '版权'],
                ['value' => '集成电路', 'label' => '集成电路'],
                ['value' => '植物新品种', 'label' => '植物新品种'],
                ['value' => '地理标志', 'label' => '地理标志'],
            ];

            return json_success('获取项目类型选项成功', $caseTypes);

        } catch (\Exception $e) {
            log_exception($e, '获取项目类型选项失败');
            return json_fail('获取项目类型选项失败');
        }
    }
}