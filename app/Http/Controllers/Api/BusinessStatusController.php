<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 商机状态控制器
 */
class BusinessStatusController extends Controller
{
    /**
    获取商机状态列表
    请求参数：
    statusName（状态名称）：可选，字符串，非空时模糊匹配状态名称
    isValid（是否有效）：可选，整数 / 布尔值，非空时筛选对应有效性的状态（如 0 = 无效、1 = 有效）
    page（页码）：可选，整数，默认 1，最小值 1，指定分页页码
    limit（每页条数）：可选，整数，默认 15，最小值 1、最大值 100，指定分页每页显示的记录数
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，获取列表结果的描述信息
    data（列表数据）：对象，包含以下字段：
    list（商机状态列表）：数组，包含商机状态详情对象，字段包括 id、status_name、is_valid、sort 等表中字段
    total（总条数）：整数，符合条件的商机状态总记录数
    page（当前页码）：整数，当前分页的页码
    limit（每页条数）：整数，当前分页每页显示的记录数
    pages（总页数）：整数，分页的总页数（向上取整计算）
    @param Request $request 请求对象，包含搜索参数和分页参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含商机状态列表及分页信息
     */
    public function index(Request $request)
    {
        try {
            // 创建 BusinessStatus 模型查询构建器
            $query = BusinessStatus::query();

            // 状态名称搜索 - 当statusName参数存在且不为空时进行模糊匹配
            if ($request->has('statusName') && !empty($request->statusName)) {
                $query->where('status_name', 'like', '%' . $request->statusName . '%');
            }

            // 状态筛选 - 当isValid参数存在且不为空时进行有效性匹配
            if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
                $query->where('is_valid', $request->isValid);
            }

            // 分页处理
            // 确保页码至少为1，防止负数或0
            $page = max(1, (int)$request->get('page', 1));
            // 确保每页记录数在1-100范围内
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取符合条件的总记录数
            $total = $query->count();

            // 获取当前页的数据列表，按 sort 和 id 升序排列
            $data = $query->orderBy('sort')
                ->orderBy('id')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            // 返回成功响应，包含列表数据和分页信息
            return json_success('获取列表成功', [
                'list' => $data->toArray(),
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            // 记录错误日志并返回失败响应
            \Log::error('获取商机状态列表失败: ' . $e->getMessage());
            return json_fail('获取列表失败');
        }
    }

    /**
    获取商机状态详情
    请求参数：
    路径参数：id（商机状态 ID）：必填，整数，商机状态的唯一标识 ID，用于查询特定状态的详情
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，获取详情结果的描述信息（如 "获取详情成功" 或 "记录不存在"）
    data（详情数据）：对象，获取成功时返回商机状态的详细信息，包含以下字段：
    id（主键 ID）：整数，商机状态的自增主键
    status_name（状态名称）：字符串，商机状态的名称
    is_valid（是否有效）：整数 / 布尔值，0 = 无效，1 = 有效，标识该状态是否可用
    sort（排序）：整数，用于商机状态列表的排序
    其他字段：如创建时间、更新时间等（根据 BusinessStatus 模型实际字段而定）
    @param int $id 商机状态 ID，用于指定查询的状态记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含商机状态的详情信息
     */
    public function show($id)
    {
        try {
            $item = BusinessStatus::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item->toArray());

        } catch (\Exception $e) {
            \Log::error('获取商机状态详情失败: ' . $e->getMessage());
            return json_fail('获取详情失败');
        }
    }

    /**
    创建商机状态
    请求参数：
    statusName（状态名称）：必填，字符串，最大长度 100 字符，商机状态的名称
    isValid（是否有效）：必填，布尔值，true = 有效，false = 无效，标识该状态是否可用
    sort（排序）：可选，整数，最小值 1，用于商机状态列表的排序，默认值为 1
    验证错误提示：
    商机状态名称不能为空：当 statusName 未提供时返回
    商机状态名称长度不能超过 100 个字符：当 statusName 长度超过限制时返回
    是否有效不能为空：当 isValid 未提供时返回
    是否有效必须是布尔值：当 isValid 不是布尔类型时返回
    排序必须是整数：当 sort 存在但不是整数时返回
    排序不能小于 1：当 sort 存在且小于 1 时返回
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，创建结果的描述信息（如 "创建成功" 或具体错误提示）
    data（创建的记录）：对象，创建成功时返回新创建的商机状态详情，包含以下字段：
    id（主键 ID）：整数，新创建状态的自增主键
    status_name（状态名称）：字符串，商机状态的名称
    is_valid（是否有效）：布尔值，状态的有效性标识
    sort（排序）：整数，状态的排序值
    updated_by（更新人）：字符串，创建该记录的用户名称（默认为 "系统管理员"）
    其他字段：如创建时间等（根据 BusinessStatus 模型实际字段而定）
    @param Request $request 请求对象，包含创建商机状态所需的参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含创建结果信息
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statusName' => 'required|string|max:100',
                'isValid' => 'required|boolean',
                'sort' => 'nullable|integer|min:1'
            ], [
                'statusName.required' => '商机状态名称不能为空',
                'statusName.max' => '商机状态名称长度不能超过100个字符',
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

            $item = BusinessStatus::create($data);

            return json_success('创建成功', $item->toArray());

        } catch (\Exception $e) {
            \Log::error('创建商机状态失败: ' . $e->getMessage());
            return json_fail('创建失败');
        }
    }

    /**
     * 更新商机状态
     *
     * 请求参数：
     * - 路径参数：id（商机状态ID）：必填，整数，商机状态的唯一标识ID，用于指定待更新的记录
     * - 请求体参数：
     *   - statusName（状态名称）：必填，字符串，最大长度100字符，商机状态的名称
     *   - isValid（是否有效）：必填，布尔值，true=有效，false=无效，标识该状态是否可用
     *   - sort（排序）：可选，整数，最小值1，用于商机状态列表的排序，默认值为1
     *
     * 验证错误提示：
     * - 商机状态名称不能为空：当statusName未提供时返回
     * - 商机状态名称长度不能超过100个字符：当statusName长度超过限制时返回
     * - 是否有效不能为空：当isValid未提供时返回
     * - 是否有效必须是布尔值：当isValid不是布尔类型时返回
     * - 排序必须是整数：当sort存在但不是整数时返回
     * - 排序不能小于1：当sort存在且小于1时返回
     *
     * 返回参数：
     * - success（操作状态）：布尔值，true表示成功，false表示失败
     * - message（提示信息）：字符串，更新结果的描述信息（如"更新成功"、"记录不存在"或具体错误提示）
     * - data（更新后的记录）：对象，更新成功时返回更新后的商机状态详情，包含以下字段：
     *   - id（主键ID）：整数，商机状态的自增主键
     *   - status_name（状态名称）：字符串，更新后的商机状态名称
     *   - is_valid（是否有效）：布尔值，更新后的状态有效性标识
     *   - sort（排序）：整数，更新后的排序值
     *   - updated_by（更新人）：字符串，更新该记录的用户名称（默认为"系统管理员"）
     *   - 其他字段：如更新时间等（根据BusinessStatus模型实际字段而定）
     *
     * @param Request $request 请求对象，包含更新商机状态所需的参数
     * @param int $id 商机状态ID，用于指定待更新的记录
     * @return \Illuminate\Http\JsonResponse JSON响应，包含更新结果信息
     */
    public function update(Request $request, $id)
    {
        try {
            $item = BusinessStatus::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $validator = Validator::make($request->all(), [
                'statusName' => 'required|string|max:100',
                'isValid' => 'required|boolean',
                'sort' => 'nullable|integer|min:1'
            ], [
                'statusName.required' => '商机状态名称不能为空',
                'statusName.max' => '商机状态名称长度不能超过100个字符',
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
            \Log::error('更新商机状态失败: ' . $e->getMessage());
            return json_fail('更新失败');
        }
    }

    /**
    删除商机状态
    请求参数：
    路径参数：id（商机状态 ID）：必填，整数，商机状态的唯一标识 ID，用于指定待删除的记录
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，删除结果的描述信息（如 "删除成功"、"记录不存在" 或 "删除失败"）
    @param int $id 商机状态 ID，用于指定待删除的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含删除结果信息
     */
    public function destroy($id)
    {
        try {
            $item = BusinessStatus::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            \Log::error('删除商机状态失败: ' . $e->getMessage());
            return json_fail('删除失败');
        }
    }

    /**
    获取商机状态选项列表（用于下拉框等场景）
    功能说明：
    返回启用状态的商机状态列表，格式适配下拉框等选择组件（包含 id、label、value 字段）
    数据默认按排序规则排序
    请求参数：无额外请求参数
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，获取选项结果的描述信息（如 "获取选项成功" 或 "获取选项列表失败"）
    data（选项列表）：数组，包含启用状态的商机状态选项对象，每个对象包含：
    id（主键 ID）：整数，商机状态的唯一标识
    label（显示文本）：字符串，商机状态的名称（用于下拉框显示）
    value（选项值）：字符串，商机状态的名称（用于下拉框选中值）
    @param Request $request 请求对象
    @return \Illuminate\Http\JsonResponse JSON 响应，包含适配下拉框的商机状态选项列表
     */
    public function options(Request $request)
    {
        try {
            $data = BusinessStatus::enabled()->ordered()
                ->select('id', 'status_name as label', 'status_name as value')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            \Log::error('获取商机状态选项失败: ' . $e->getMessage());
            return json_fail('获取选项列表失败');
        }
    }
}
