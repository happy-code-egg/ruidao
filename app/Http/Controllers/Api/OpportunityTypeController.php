<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpportunityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 商机类型控制器
 */
class OpportunityTypeController extends Controller
{
    /**
     * 获取列表
     */
    public function index(Request $request)
    {
        try {
            $query = OpportunityType::query();

            // 类型名称搜索
            if ($request->has('statusName') && !empty($request->statusName)) {
                $query->where('status_name', 'like', '%' . $request->statusName . '%');
            }

            // 状态筛选
            if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
                $query->where('is_valid', $request->isValid);
            }

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->orderBy('sort')
                         ->orderBy('id')
                         ->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data->toArray(),
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            \Log::error('获取商机类型列表失败: ' . $e->getMessage());
            return json_fail('获取列表失败');
        }
    }

    /**
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = OpportunityType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item->toArray());

        } catch (\Exception $e) {
            \Log::error('获取商机类型详情失败: ' . $e->getMessage());
            return json_fail('获取详情失败');
        }
    }

    /**
     * 创建
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statusName' => 'required|string|max:100',
                'isValid' => 'required|boolean',
                'sort' => 'nullable|integer|min:1'
            ], [
                'statusName.required' => '商机类型名称不能为空',
                'statusName.max' => '商机类型名称长度不能超过100个字符',
                'isValid.required' => '是否有效不能为空',
                'isValid.boolean' => '是否有效必须是布尔值',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序不能小于1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = [
                'status_name' => $request->statusName,
                'is_valid' => $request->isValid,
                'sort' => $request->sort ?? 1,
                'updated_by' => auth()->user()->name ?? '系统管理员'
            ];

            $item = OpportunityType::create($data);

            return json_success('创建成功', $item->toArray());

        } catch (\Exception $e) {
            \Log::error('创建商机类型失败: ' . $e->getMessage());
            return json_fail('创建失败');
        }
    }

    /**
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $item = OpportunityType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'statusName' => 'required|string|max:100',
                'isValid' => 'required|boolean',
                'sort' => 'nullable|integer|min:1'
            ], [
                'statusName.required' => '商机类型名称不能为空',
                'statusName.max' => '商机类型名称长度不能超过100个字符',
                'isValid.required' => '是否有效不能为空',
                'isValid.boolean' => '是否有效必须是布尔值',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序不能小于1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = [
                'status_name' => $request->statusName,
                'is_valid' => $request->isValid,
                'sort' => $request->sort ?? 1,
                'updated_by' => auth()->user()->name ?? '系统管理员'
            ];

            $item->update($data);

            return json_success('更新成功', $item->toArray());

        } catch (\Exception $e) {
            \Log::error('更新商机类型失败: ' . $e->getMessage());
            return json_fail('更新失败');
        }
    }

    /**
     * 删除
     */
    public function destroy($id)
    {
        try {
            $item = OpportunityType::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            \Log::error('删除商机类型失败: ' . $e->getMessage());
            return json_fail('删除失败');
        }
    }

    /**
     * 获取选项列表（用于下拉框等）
     */
    public function options(Request $request)
    {
        try {
            $data = OpportunityType::enabled()->ordered()
                ->select('id', 'status_name as label', 'status_name as value')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            \Log::error('获取商机类型选项失败: ' . $e->getMessage());
            return json_fail('获取选项列表失败');
        }
    }
}