<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RelatedType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 相关类型（树形层级）配置控制器（旧版）
 *
 * 功能:
 * - 维护相关类型的数据：列表、详情、创建、更新、删除、批量状态更新、选项获取、分类列表、树形结构
 * - 支持按分类、编码、名称、层级、父级编码、有效状态进行筛选与分页
 * - 返回统一 JSON 响应结构（json_success/json_fail），异常记录统一日志
 *
 * 路由说明:
 * - 当前控制器未在 routes/api.php 中直接绑定；线上接口由 `RelatedTypesController`（复数）负责
 * - 实际对外路径为 `/api/config/related-types*` 与 `/api/data-config/related-types*`
 *
 * 依赖:
 * - 模型 `App\Models\RelatedType`
 * - 认证 `Auth::id()`、表单验证器 `Validator`
 */
class RelatedTypeController extends Controller
{
    /**
     * 获取相关类型列表（分页）
     *
     * 功能:
     * - 按条件筛选相关类型并分页返回，包含创建/更新人及父级名称等关联信息
     *
     * 接口:
     * - 未直接绑定；线上对应 `GET /api/config/related-types` 或 `GET /api/data-config/related-types`
     *
     * 请求参数:
     * - `category` 分类（可选，精确匹配）
     * - `code` 编码（可选，精确匹配）
     * - `name` 名称（可选，模糊匹配）
     * - `level` 层级（可选，整数）
     * - `parent_code` 父级编码（可选，精确匹配）
     * - `is_valid` 是否有效（可选，0/1）
     * - `page` 页码（可选，默认 1，最小 1）
     * - `limit` 每页数量（可选，默认 10，最大 100）
     *
     * 返回参数:
     * - `json_success` 统一结构：
     *   - `data.list` 列表数据（含 `id/category/code/name/description/parent_code/parent_name/level/full_path/is_valid/sort/created_by/updated_by/created_at/updated_at`）
     *   - `data.total` 总数
     *   - `data.page` 当前页
     *   - `data.limit` 每页数量
     *   - `data.pages` 总页数
     *
     * 内部说明:
     * - 排序顺序：`category asc`、`level asc`、`sort asc`、`id desc`
     * - 使用 `with()` 关联 `creator/updater/parent` 并格式化时间
     */
    public function index(Request $request)
    {
        try {
            $query = RelatedType::query();

            // 搜索条件
            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('code')) {
                $query->byCode($request->code);
            }

            if ($request->filled('name')) {
                $query->byName($request->name);
            }

            if ($request->has('level') && $request->level !== '' && $request->level !== null) {
                $query->byLevel($request->level);
            }

            if ($request->filled('parent_code')) {
                $query->where('parent_code', $request->parent_code);
            }

            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->orderBy('category', 'asc')
                  ->orderBy('level', 'asc')
                  ->orderBy('sort', 'asc')
                  ->orderBy('id', 'desc');

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->with(['creator:id,real_name', 'updater:id,real_name', 'parent:id,code,name'])
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'category' => $item->category,
                                 'code' => $item->code,
                                 'name' => $item->name,
                                 'description' => $item->description,
                                 'parent_code' => $item->parent_code,
                                 'parent_name' => $item->parent ? $item->parent->name : '',
                                 'level' => $item->level,
                                 'full_path' => $item->full_path,
                                 'is_valid' => $item->is_valid,
                                 'sort' => $item->sort,
                                 'created_by' => $item->creator ? $item->creator->real_name : '',
                                 'updated_by' => $item->updater ? $item->updater->real_name : '',
                                 'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                                 'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                             ];
                         });

            return json_success('获取列表成功', [
                'list' => $list,
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
     * 创建相关类型
     *
     * 功能:
     * - 新增一个相关类型节点，校验父子层级关系与唯一性
     *
     * 接口:
     * - 未直接绑定；线上对应 `POST /api/config/related-types` 或 `POST /api/data-config/related-types`
     *
     * 请求参数:
     * - `category` 必填，字符串，最长 50
     * - `code` 必填，字符串，最长 50，唯一（related_types.code）
     * - `name` 必填，字符串，最长 255
     * - `description` 可选，字符串
     * - `parent_code` 可选，字符串，最长 50
     * - `level` 必填，整数，范围 1..5；无父级时必须为 1
     * - `is_valid` 必填，布尔
     * - `sort` 可选，整数，最小 1
     *
     * 返回参数:
     * - `json_success` 返回新建记录完整信息
     *
     * 内部说明:
     * - 若有父级：校验父级存在，且子级层级必须大于父级层级
     * - 若无父级：`level` 必须为 1
     * - 记录 `created_by`/`updated_by` 为当前登录用户
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'required|string|max:50',
                'code' => 'required|string|max:50|unique:related_types,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_code' => 'nullable|string|max:50',
                'level' => 'required|integer|min:1|max:5',
                'is_valid' => 'required|boolean',
                'sort' => 'integer|min:1',
            ], [
                'category.required' => '类型分类不能为空',
                'code.required' => '类型编码不能为空',
                'code.unique' => '类型编码已存在',
                'name.required' => '类型名称不能为空',
                'name.max' => '类型名称长度不能超过255个字符',
                'level.required' => '层级不能为空',
                'level.min' => '层级最小为1',
                'level.max' => '层级最大为5',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 验证父级编码是否存在
            if ($request->filled('parent_code')) {
                $parent = RelatedType::where('code', $request->parent_code)->first();
                if (!$parent) {
                    return json_fail('父级类型不存在');
                }
                
                // 验证层级关系
                if ($request->level <= $parent->level) {
                    return json_fail('子级层级必须大于父级层级');
                }
            } else {
                // 没有父级编码，层级必须为1
                if ($request->level != 1) {
                    return json_fail('顶级类型层级必须为1');
                }
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $item = RelatedType::create($data);

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '创建相关类型失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 获取相关类型详情
     *
     * 功能:
     * - 根据 `id` 返回单条相关类型的详细信息，包含关联的创建/更新人及父级名称
     *
     * 接口:
     * - 未直接绑定；线上对应 `GET /api/config/related-types/{id}` 或 `GET /api/data-config/related-types/{id}`
     *
     * 请求参数:
     * - `id` 路径参数，整数
     *
     * 返回参数:
     * - `json_success` 返回字段：`id/category/code/name/description/parent_code/parent_name/level/full_path/is_valid/sort/created_by/updated_by/created_at/updated_at`
     */
    public function show($id)
    {
        try {
            $item = RelatedType::with(['creator:id,real_name', 'updater:id,real_name', 'parent:id,code,name'])->find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $result = [
                'id' => $item->id,
                'category' => $item->category,
                'code' => $item->code,
                'name' => $item->name,
                'description' => $item->description,
                'parent_code' => $item->parent_code,
                'parent_name' => $item->parent ? $item->parent->name : '',
                'level' => $item->level,
                'full_path' => $item->full_path,
                'is_valid' => $item->is_valid,
                'sort' => $item->sort,
                'created_by' => $item->creator ? $item->creator->real_name : '',
                'updated_by' => $item->updater ? $item->updater->real_name : '',
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            log_exception($e, '获取相关类型详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 更新相关类型
     *
     * 功能:
     * - 修改指定记录，校验唯一性、父子关系与循环引用
     *
     * 接口:
     * - 未直接绑定；线上对应 `PUT /api/config/related-types/{id}` 或 `PUT /api/data-config/related-types/{id}`
     *
     * 请求参数:
     * - `category` 必填，字符串，最长 50
     * - `code` 必填，字符串，最长 50，唯一（排除当前 id）
     * - `name` 必填，字符串，最长 255
     * - `description` 可选，字符串
     * - `parent_code` 可选，字符串，最长 50，不能为自身编码
     * - `level` 必填，整数，范围 1..5；无父级时必须为 1
     * - `is_valid` 必填，布尔
     * - `sort` 可选，整数，最小 1
     *
     * 返回参数:
     * - `json_success` 返回更新后的记录完整信息
     *
     * 内部说明:
     * - 若指定父级：必须存在；子级层级必须大于父级层级；且不能造成循环引用（使用 `getAllChildrenIds` 校验）
     * - 记录 `updated_by` 为当前登录用户
     */
    public function update(Request $request, $id)
    {
        try {
            $item = RelatedType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'category' => 'required|string|max:50',
                'code' => 'required|string|max:50|unique:related_types,code,' . $id,
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_code' => 'nullable|string|max:50',
                'level' => 'required|integer|min:1|max:5',
                'is_valid' => 'required|boolean',
                'sort' => 'integer|min:1',
            ], [
                'category.required' => '类型分类不能为空',
                'code.required' => '类型编码不能为空',
                'code.unique' => '类型编码已存在',
                'name.required' => '类型名称不能为空',
                'name.max' => '类型名称长度不能超过255个字符',
                'level.required' => '层级不能为空',
                'level.min' => '层级最小为1',
                'level.max' => '层级最大为5',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 验证不能将自己设为父级
            if ($request->filled('parent_code') && $request->parent_code == $item->code) {
                return json_fail('不能将自己设为父级');
            }

            // 验证父级编码是否存在
            if ($request->filled('parent_code')) {
                $parent = RelatedType::where('code', $request->parent_code)->first();
                if (!$parent) {
                    return json_fail('父级类型不存在');
                }
                
                // 验证层级关系
                if ($request->level <= $parent->level) {
                    return json_fail('子级层级必须大于父级层级');
                }

                // 验证不会造成循环引用
                $allChildren = $item->getAllChildrenIds();
                if (in_array($parent->id, $allChildren)) {
                    return json_fail('不能将子级类型设为父级');
                }
            } else {
                // 没有父级编码，层级必须为1
                if ($request->level != 1) {
                    return json_fail('顶级类型层级必须为1');
                }
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();

            $item->update($data);

            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '更新相关类型失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除相关类型
     *
     * 功能:
     * - 删除指定记录，若存在子级则禁止删除
     *
     * 接口:
     * - 未直接绑定；线上对应 `DELETE /api/config/related-types/{id}` 或 `DELETE /api/data-config/related-types/{id}`
     *
     * 请求参数:
     * - `id` 路径参数，整数
     *
     * 返回参数:
     * - `json_success` 文本消息：删除成功
     */
    public function destroy($id)
    {
        try {
            $item = RelatedType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            // 检查是否有子级类型
            $childrenCount = $item->children()->count();
            if ($childrenCount > 0) {
                return json_fail('该类型下还有子级类型，无法删除');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除相关类型失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     *
     * 功能:
     * - 返回有效的相关类型选项，按排序与层级组织，供下拉选择使用
     *
     * 接口:
     * - 未直接绑定；线上对应 `GET /api/config/related-types/options` 或 `GET /api/data-config/related-types/options`
     *
     * 请求参数:
     * - `category` 分类（可选，精确匹配）
     * - `level` 层级（可选，整数）
     *
     * 返回参数:
     * - `json_success` 返回数组：`[{id, category, code, name, level, parent_code, full_path}]`
     *
     * 内部说明:
     * - 使用模型作用域：`valid()` 与 `ordered()`
     */
    public function options(Request $request)
    {
        try {
            $query = RelatedType::valid()->ordered();

            // 可以按分类筛选
            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            // 可以按层级筛选
            if ($request->has('level') && $request->level !== '' && $request->level !== null) {
                $query->byLevel($request->level);
            }

            $data = $query->select('id', 'category', 'code', 'name', 'level', 'parent_code')
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'category' => $item->category,
                                 'code' => $item->code,
                                 'name' => $item->name,
                                 'level' => $item->level,
                                 'parent_code' => $item->parent_code,
                                 'full_path' => $item->full_path,
                             ];
                         });

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取选项列表失败');
            return json_fail('获取选项列表失败');
        }
    }

    /**
     * 批量更新状态
     *
     * 功能:
     * - 批量设置 `is_valid` 状态，并记录更新人
     *
     * 接口:
     * - 未直接绑定；线上对应 `POST /api/data-config/related-types/batch-status`
     *
     * 请求参数:
     * - `ids` 必填，数组，元素为整数 id
     * - `is_valid` 必填，布尔（0/1）
     *
     * 返回参数:
     * - `json_success` 文本消息：包含更新记录数
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer',
                'is_valid' => 'required|boolean'
            ], [
                'ids.required' => '请选择要更新的记录',
                'ids.array' => 'ids必须是数组',
                'is_valid.required' => '请选择状态',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $updated = RelatedType::whereIn('id', $request->ids)
                                 ->update([
                                     'is_valid' => $request->is_valid,
                                     'updated_by' => Auth::id()
                                 ]);

            return json_success("批量更新成功，共更新{$updated}条记录");

        } catch (\Exception $e) {
            log_exception($e, '批量更新状态失败');
            return json_fail('批量更新失败');
        }
    }

    /**
     * 获取分类列表
     *
     * 功能:
     * - 返回所有存在的分类值（去重、排序），供前端筛选使用
     *
     * 接口:
     * - 未直接绑定；可作为内部辅助接口
     *
     * 返回参数:
     * - `json_success` 返回数组：`[category, ...]`
     */
    public function getCategories()
    {
        try {
            $categories = RelatedType::select('category')
                                    ->distinct()
                                    ->orderBy('category')
                                    ->pluck('category');

            return json_success('获取分类成功', $categories);

        } catch (\Exception $e) {
            log_exception($e, '获取分类列表失败');
            return json_fail('获取分类列表失败');
        }
    }

    /**
     * 获取树形结构
     *
     * 功能:
     * - 按分类筛选有效记录并构建层级树，返回前端展示所需的嵌套结构
     *
     * 接口:
     * - 未直接绑定；可作为内部辅助接口
     *
     * 请求参数:
     * - `category` 分类（可选，精确匹配）
     *
     * 返回参数:
     * - `json_success` 树形数组：`[{id, code, name, category, level, children: [...]}, ...]`
     */
    public function getTree(Request $request)
    {
        try {
            $query = RelatedType::valid()->ordered();

            // 可以按分类筛选
            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            $allTypes = $query->get();

            // 构建树形结构
            $tree = $this->buildTree($allTypes);

            return json_success('获取树形结构成功', $tree);

        } catch (\Exception $e) {
            log_exception($e, '获取树形结构失败');
            return json_fail('获取树形结构失败');
        }
    }

    /**
     * 构建树形结构（内部方法）
     *
     * 功能:
     * - 根据传入的类型集合与父级编码递归构建树形节点
     *
     * 请求参数:
     * - `$types` 相关类型集合（`Collection|array`）
     * - `$parentCode` 父级编码（可选，字符串或 null）
     *
     * 返回参数:
     * - `array` 树形结构数组
     *
     * 内部说明:
     * - 仅供 `getTree` 使用，不暴露为路由接口
     */
    private function buildTree($types, $parentCode = null)
    {
        $tree = [];

        foreach ($types as $type) {
            if ($type->parent_code == $parentCode) {
                $node = [
                    'id' => $type->id,
                    'code' => $type->code,
                    'name' => $type->name,
                    'category' => $type->category,
                    'level' => $type->level,
                    'children' => $this->buildTree($types, $type->code)
                ];
                $tree[] = $node;
            }
        }

        return $tree;
    }
}
