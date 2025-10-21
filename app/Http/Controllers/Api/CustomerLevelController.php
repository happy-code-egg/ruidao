<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CustomerLevelController extends Controller
{
    /**
     * 获取客户等级列表
     */
    public function index(Request $request)
    {
        try {
            $query = CustomerLevel::query();

            // 搜索条件
            if ($request->filled('code')) {
                $query->byCode($request->code);
            }

            if ($request->filled('name')) {
                $query->byName($request->name);
            }

            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->orderBy('sort', 'asc')->orderBy('level_value', 'desc');

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->with(['creator:id,real_name', 'updater:id,real_name'])
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort,
                                 'level_name' => $item->level_name,
                                 'level_code' => $item->level_code,
                                 'description' => $item->description,
                                 'is_valid' => $item->is_valid,
                                 'created_by' => $item->creator->real_name ?? '',
                                 'updated_by' => $item->updater->real_name ?? '',
                                 'created_at' => $item->created_at,
                                 'updated_at' => $item->updated_at,
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
            log_exception($e, '获取客户等级列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建客户等级
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'level_name' => 'required|string|max:255',
                'level_code' => 'required|string|max:50|unique:customer_levels,level_code',
                'description' => 'nullable|string',
                'is_valid' => 'required|in:0,1',
                'sort' => 'integer|min:1',
            ], [
                'level_name.required' => '等级名称不能为空',
                'level_name.max' => '等级名称长度不能超过255个字符',
                'level_code.required' => '等级编码不能为空',
                'level_code.unique' => '等级编码已存在',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $item = CustomerLevel::create($data);

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '创建客户等级失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 获取客户等级详情
     */
    public function show($id)
    {
        try {
            $item = CustomerLevel::with(['creator:id,real_name', 'updater:id,real_name'])->find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $result = [
                'id' => $item->id,
                'sort' => $item->sort,
                'level_name' => $item->level_name,
                'level_code' => $item->level_code,
                'description' => $item->description,
                'is_valid' => $item->is_valid,
                'created_by' => $item->creator->real_name ?? '',
                'updated_by' => $item->updater->real_name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            log_exception($e, '获取客户等级详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 更新客户等级
     */
    public function update(Request $request, $id)
    {
        try {
            $item = CustomerLevel::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'level_name' => 'required|string|max:255',
                'level_code' => 'required|string|max:50|unique:customer_levels,level_code,' . $id,
                'description' => 'nullable|string',
                'is_valid' => 'required|in:0,1',
                'sort' => 'integer|min:1',
            ], [
                'level_name.required' => '等级名称不能为空',
                'level_name.max' => '等级名称长度不能超过255个字符',
                'level_code.required' => '等级编码不能为空',
                'level_code.unique' => '等级编码已存在',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();

            $item->update($data);

            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            log_exception($e, '更新客户等级失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除客户等级
     */
    public function destroy($id)
    {
        try {
            $item = CustomerLevel::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            // TODO: 检查是否有客户在使用此等级（当Customer模型存在时启用）
            // $customersCount = $item->customers()->count();
            // if ($customersCount > 0) {
            //     return json_fail('该等级下还有客户在使用，无法删除');
            // }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除客户等级失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     */
    public function options()
    {
        try {
            $data = CustomerLevel::valid()
                                ->select('id', 'level_name', 'level_code', 'description')
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'id' => $item->id,
                                        'level_name' => $item->level_name,
                                        'level_code' => $item->level_code,
                                        'description' => $item->description,
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
                'is_valid' => 'required|in:0,1'
            ], [
                'ids.required' => '请选择要更新的记录',
                'ids.array' => 'ids必须是数组',
                'is_valid.required' => '请选择状态',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $updated = CustomerLevel::whereIn('id', $request->ids)
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
}