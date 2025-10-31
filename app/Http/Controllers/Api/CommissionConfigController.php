<?php

namespace App\Http\Controllers\Api;

use App\Models\CommissionConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommissionConfigController extends Controller
{
    /**
     * 获取提成配置列表
     */
    public function index(Request $request)
    {
        $query = CommissionConfig::query();

        // 按配置类型过滤
        if ($request->has('config_type') && $request->config_type) {
            $query->where('config_type', $request->config_type);
        }

        // 按状态过滤
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 按关键词搜索
        if ($request->has('keyword') && $request->keyword) {
            $query->where('config_name', 'like', '%' . $request->keyword . '%');
        }

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        $total = $query->count();
        $list = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }

    /**
     * 创建提成配置
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'configName' => 'required|string|max:100',
            'configType' => 'required|in:business,agent,consultant',
            'level' => 'required|string|max:50',
            'baseRate' => 'required|numeric|min:0|max:100',
            'bonusRate' => 'required|numeric|min:0|max:100',
            'minAmount' => 'required|numeric|min:0',
            'maxAmount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'remark' => 'nullable|string'
        ]);

        $snakeData = $this->camelToSnake($validated);
        $config = CommissionConfig::create($snakeData);

        return response()->json([
            'code' => 0,
            'message' => '创建成功',
            'data' => $config
        ], 201);
    }

    /**
     * 获取单个提成配置
     */
    public function show($id)
    {
        $config = CommissionConfig::findOrFail($id);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $config
        ]);
    }

    /**
     * 更新提成配置
     */
    public function update(Request $request, $id)
    {
        $config = CommissionConfig::findOrFail($id);

        $validated = $request->validate([
            'configName' => 'sometimes|required|string|max:100',
            'configType' => 'sometimes|required|in:business,agent,consultant',
            'level' => 'sometimes|required|string|max:50',
            'baseRate' => 'sometimes|required|numeric|min:0|max:100',
            'bonusRate' => 'sometimes|required|numeric|min:0|max:100',
            'minAmount' => 'sometimes|required|numeric|min:0',
            'maxAmount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:active,inactive',
            'remark' => 'nullable|string'
        ]);

        $snakeData = $this->camelToSnake($validated);
        $config->update($snakeData);

        return response()->json([
            'code' => 0,
            'message' => '更新成功',
            'data' => $config
        ]);
    }

    /**
     * 删除提成配置
     */
    public function destroy($id)
    {
        $config = CommissionConfig::findOrFail($id);
        $config->delete();

        return response()->json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    /**
     * 批量删除
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'code' => 1,
                'message' => '请选择要删除的配置'
            ], 400);
        }

        CommissionConfig::whereIn('id', $ids)->delete();

        return response()->json([
            'code' => 0,
            'message' => '批量删除成功'
        ]);
    }

    /**
     * 批量启用
     */
    public function batchEnable(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'code' => 1,
                'message' => '请选择要启用的配置'
            ], 400);
        }

        CommissionConfig::whereIn('id', $ids)->update(['status' => 'active']);

        return response()->json([
            'code' => 0,
            'message' => '批量启用成功'
        ]);
    }

    /**
     * 批量禁用
     */
    public function batchDisable(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'code' => 1,
                'message' => '请选择要禁用的配置'
            ], 400);
        }

        CommissionConfig::whereIn('id', $ids)->update(['status' => 'inactive']);

        return response()->json([
            'code' => 0,
            'message' => '批量禁用成功'
        ]);
    }

    /**
     * 将 camelCase 转换为 snake_case
     */
    private function camelToSnake($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }
}

