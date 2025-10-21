<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RelatedType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RelatedTypeController extends Controller
{
    /**
     * 获取相关类型列表
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
     * 构建树形结构
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
