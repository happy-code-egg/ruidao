<?php

namespace App\Http\Controllers\Api;

use App\Models\UserLevelConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserLevelConfigController extends Controller
{
    /**
     * 获取用户等级配置列表
     */
    public function index(Request $request)
    {
        $query = UserLevelConfig::query();

        // 按用户类型过滤
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        // 按状态过滤
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 按关键词搜索
        if ($request->has('keyword') && $request->keyword) {
            $query->where('level_name', 'like', '%' . $request->keyword . '%');
        }

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        $total = $query->count();
        $list = $query->orderBy('level_order', 'asc')
            ->orderBy('created_at', 'desc')
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
     * 创建用户等级配置
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'levelName' => 'required|string|max:100',
            'levelCode' => 'required|string|max:50|unique:user_level_configs,level_code',
            'levelOrder' => 'required|integer|min:1',
            'userType' => 'required|in:business,agent,consultant,operation',
            'minExperience' => 'required|integer|min:0',
            'maxExperience' => 'required|integer|min:0',
            'baseSalary' => 'required|numeric|min:0',
            'requiredSkills' => 'nullable|array',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'remark' => 'nullable|string'
        ]);

        $snakeData = $this->camelToSnake($validated);
        $config = UserLevelConfig::create($snakeData);

        return response()->json([
            'code' => 0,
            'message' => '创建成功',
            'data' => $config
        ], 201);
    }

    /**
     * 获取单个用户等级配置
     */
    public function show($id)
    {
        $config = UserLevelConfig::findOrFail($id);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $config
        ]);
    }

    /**
     * 更新用户等级配置
     */
    public function update(Request $request, $id)
    {
        $config = UserLevelConfig::findOrFail($id);

        $validated = $request->validate([
            'levelName' => 'sometimes|required|string|max:100',
            'levelCode' => 'sometimes|required|string|max:50|unique:user_level_configs,level_code,' . $id,
            'levelOrder' => 'sometimes|required|integer|min:1',
            'userType' => 'sometimes|required|in:business,agent,consultant,operation',
            'minExperience' => 'sometimes|required|integer|min:0',
            'maxExperience' => 'sometimes|required|integer|min:0',
            'baseSalary' => 'sometimes|required|numeric|min:0',
            'requiredSkills' => 'nullable|array',
            'description' => 'nullable|string',
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
     * 删除用户等级配置
     */
    public function destroy($id)
    {
        $config = UserLevelConfig::findOrFail($id);
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
                'message' => '请选择要删除的等级配置'
            ], 400);
        }

        UserLevelConfig::whereIn('id', $ids)->delete();

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
                'message' => '请选择要启用的等级配置'
            ], 400);
        }

        UserLevelConfig::whereIn('id', $ids)->update(['status' => 'active']);

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
                'message' => '请选择要禁用的等级配置'
            ], 400);
        }

        UserLevelConfig::whereIn('id', $ids)->update(['status' => 'inactive']);

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

