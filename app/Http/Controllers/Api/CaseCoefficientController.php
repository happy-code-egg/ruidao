<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseCoefficient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CaseCoefficientController extends Controller
{
    /**
     * 获取项目系数列表
     */
    public function index(Request $request)
    {
        try {
            $query = CaseCoefficient::query();

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->orderBy('sort', 'asc');

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'sort' => $item->sort,
                                 'name' => $item->name,
                                 'is_valid' => $item->is_valid ? 1 : 0,
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
            $this->log(8, "获取项目系数列表失败：{$e->getMessage()}", [
                'title' => '项目系数列表',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建项目系数
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sort' => 'integer|min:1',
                'name' => 'required|string|max:255',
                'is_valid' => 'required|in:0,1',
            ], [
                'name.required' => '项目系数名称不能为空',
                'name.max' => '项目系数名称长度不能超过255个字符',
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

            $item = CaseCoefficient::create($data);

            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            $this->log(8, "创建项目系数失败：{$e->getMessage()}", [
                'title' => '项目系数',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('创建失败');
        }
    }

    /**
     * 获取项目系数详情
     */
    public function show($id)
    {
        try {
            $item = CaseCoefficient::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $result = [
                'id' => $item->id,
                'sort' => $item->sort,
                'name' => $item->name,
                'is_valid' => $item->is_valid,
                'created_by' => $item->created_by,
                'updated_by' => $item->updated_by,
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            $this->log(8, "获取项目系数详情失败：{$e->getMessage()}", [
                'title' => '项目系数',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('获取详情失败');
        }
    }

    /**
     * 更新项目系数
     */
    public function update(Request $request, $id)
    {
        try {
            $item = CaseCoefficient::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'sort' => 'integer|min:1',
                'name' => 'required|string|max:255',
                'is_valid' => 'required|in:0,1',
            ], [
                'name.required' => '项目系数名称不能为空',
                'name.max' => '项目系数名称长度不能超过255个字符',
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
            $this->log(8, "更新项目系数失败：{$e->getMessage()}", [
                'title' => '项目系数',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('更新失败');
        }
    }

    /**
     * 删除项目系数
     */
    public function destroy($id)
    {
        try {
            $item = CaseCoefficient::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            $this->log(8, "删除项目系数失败：{$e->getMessage()}", [
                'title' => '项目系数',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表
     */
    public function options()
    {
        try {
            $data = CaseCoefficient::valid()
                                  ->ordered()
                                  ->select('id', 'name', 'sort')
                                  ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            $this->log(8, "获取选项列表失败：{$e->getMessage()}", [
                'title' => '项目系数',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]);
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

            $updated = CaseCoefficient::whereIn('id', $request->ids)
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
