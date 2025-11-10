<?php

namespace App\Http\Controllers\Api;

use App\Models\UserLevelConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserLevelConfigController extends Controller
{
    /**
     * 功能: 获取用户等级配置列表，支持按用户类型/状态/关键词筛选与分页
     * 请求参数:
     * - user_type(string, 可选): 用户类型过滤（business/agent/consultant/operation）
     * - status(string, 可选): 状态过滤（active/inactive）
     * - keyword(string, 可选): 关键词匹配等级名称
     * - page(int, 可选): 页码，默认1
     * - limit(int, 可选): 每页数量，默认20
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): {list(array), total(int), page(int), limit(int)}
     * 接口: GET /user-level-configs
     */
    public function index(Request $request)
    {
        // 步骤说明：解析过滤条件 -> 构建查询 -> 统计总数 -> 分页查询 -> 返回统一结构
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
     * 功能: 创建用户等级配置
     * 请求参数:
     * - levelName(string, 必填): 等级名称，最大100字符
     * - levelCode(string, 必填): 等级代码，最大50字符，唯一
     * - levelOrder(int, 必填): 等级排序，>=1
     * - userType(enum, 必填): 用户类型（business/agent/consultant/operation）
     * - minExperience(int, 必填): 最小经验值，>=0
     * - maxExperience(int, 必填): 最大经验值，>=0
     * - baseSalary(number, 必填): 基础薪资，>=0
     * - requiredSkills(array, 可选): 需要技能列表
     * - description(string, 可选): 描述
     * - status(enum, 必填): 状态（active/inactive）
     * - remark(string, 可选): 备注
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 新建的等级配置记录
     * - HTTP状态: 201
     * 接口: POST /user-level-configs
     */
    public function store(Request $request)
    {
        // 步骤说明：参数校验 -> 驼峰转下划线 -> 写入数据库 -> 返回创建结果
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
     * 功能: 获取单个用户等级配置详情
     * 请求参数:
     * - id(int, 必填): 路径参数，等级配置ID
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 等级配置记录
     * 接口: GET /user-level-configs/{id}
     */
    public function show($id)
    {
        // 步骤说明：按ID检索 -> 返回统一结构
        $config = UserLevelConfig::findOrFail($id);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $config
        ]);
    }

    /**
     * 功能: 更新用户等级配置（部分字段可选更新）
     * 请求参数:
     * - id(int, 必填): 路径参数，等级配置ID
     * - levelName(string, 可选): 等级名称
     * - levelCode(string, 可选): 等级代码，唯一（忽略当前ID）
     * - levelOrder(int, 可选): 等级排序
     * - userType(enum, 可选): 用户类型（business/agent/consultant/operation）
     * - minExperience(int, 可选): 最小经验值
     * - maxExperience(int, 可选): 最大经验值
     * - baseSalary(number, 可选): 基础薪资
     * - requiredSkills(array, 可选): 需要技能列表
     * - description(string, 可选): 描述
     * - status(enum, 可选): 状态（active/inactive）
     * - remark(string, 可选): 备注
     * 返回参数:
     * - JSON: {code, message, data}
     * - data(object): 更新后的等级配置记录
     * 接口: PUT /user-level-configs/{id}
     */
    public function update(Request $request, $id)
    {
        // 步骤说明：按ID检索 -> 参数校验（有则校验） -> 驼峰转下划线 -> 更新记录 -> 返回结果
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
     * 功能: 删除指定的用户等级配置
     * 请求参数:
     * - id(int, 必填): 路径参数，等级配置ID
     * 返回参数:
     * - JSON: {code, message}
     * 接口: DELETE /user-level-configs/{id}
     */
    public function destroy($id)
    {
        // 步骤说明：按ID检索 -> 删除 -> 返回结果
        $config = UserLevelConfig::findOrFail($id);
        $config->delete();

        return response()->json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    /**
     * 功能: 批量删除用户等级配置
     * 请求参数:
     * - ids(array<int>, 必填): 待删除的等级配置ID列表
     * 返回参数:
     * - JSON: {code, message}
     * - 异常情况: 当 ids 为空时返回 400
     * 接口: POST /user-level-configs/batch-destroy
     */
    public function batchDestroy(Request $request)
    {
        // 步骤说明：读取ID列表 -> 校验非空 -> 执行批量删除 -> 返回结果
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
     * 功能: 批量启用用户等级配置（状态置为 active）
     * 请求参数:
     * - ids(array<int>, 必填): 待启用的等级配置ID列表
     * 返回参数:
     * - JSON: {code, message}
     * 接口: POST /user-level-configs/batch-enable
     */
    public function batchEnable(Request $request)
    {
        // 步骤说明：读取ID列表 -> 校验非空 -> 执行批量更新 -> 返回结果
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
     * 功能: 批量禁用用户等级配置（状态置为 inactive）
     * 请求参数:
     * - ids(array<int>, 必填): 待禁用的等级配置ID列表
     * 返回参数:
     * - JSON: {code, message}
     * 接口: POST /user-level-configs/batch-disable
     */
    public function batchDisable(Request $request)
    {
        // 步骤说明：读取ID列表 -> 校验非空 -> 执行批量更新 -> 返回结果
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
     * 功能: 将输入数组键由 camelCase 转换为 snake_case
     * 请求参数:
     * - array(array): 以驼峰键命名的数组
     * 返回参数:
     * - array: 转换为下划线命名后的数组
     * 接口: 无接口（内部工具方法）
     */
    private function camelToSnake($array)
    {
        // 步骤说明：遍历键名 -> 正则插入下划线 -> 小写化 -> 复制值
        $result = [];
        foreach ($array as $key => $value) {
            $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }
}

