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
    获取项目系数列表
    功能说明：
    支持按名称模糊搜索和有效性筛选，精准匹配所需数据
    结果按排序字段升序排列，返回格式化数据（含创建人、更新人真实姓名关联信息）
    分页参数做边界控制，确保请求合法性，同时记录异常日志便于排查
    请求参数：
    name（系数名称）：可选，字符串，非空时模糊匹配项目系数名称（如 "服务费率"“折扣系数” 等）
    is_valid（是否有效）：可选，整数 / 布尔值，非空时筛选有效性（1 = 有效，0 = 无效），控制系数是否可用于业务计算
    page（页码）：可选，整数，默认 1，最小值 1，指定分页页码
    limit（每页条数）：可选，整数，默认 10，最小值 1、最大值 100，控制单页返回数据量
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取列表成功" 或 "获取列表失败"
    data（列表数据）：对象，包含以下字段：
    list（项目系数列表）：数组，每个元素为格式化后的系数对象，字段包括：
    id（主键 ID）：整数，项目系数的唯一标识
    sort（排序）：整数，用于列表展示顺序的排序值
    name（系数名称）：字符串，项目系数的业务名称（如 "基础系数"“加急系数”）
    is_valid（是否有效）：整数，1 = 有效（可参与业务计算），0 = 无效（暂不使用）
    created_by（创建人）：字符串，创建该系数的用户真实姓名（无关联用户则为空）
    updated_by（更新人）：字符串，最后更新该系数的用户真实姓名（无关联用户则为空）
    created_at（创建时间）：时间戳，系数记录的创建时间
    updated_at（更新时间）：时间戳，系数记录的最后更新时间
    total（总条数）：整数，符合筛选条件的项目系数总记录数
    page（当前页码）：整数，当前返回数据的分页页码
    limit（每页条数）：整数，当前分页的单页数据量
    pages（总页数）：整数，按当前分页参数计算的总页数（向上取整）
    @param Request $request 请求对象，包含搜索条件和分页参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含项目系数列表及分页元信息
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
    创建项目系数
    功能说明：
    验证核心参数合法性，记录创建人和更新人为当前登录用户
    支持自定义验证错误提示，异常时记录日志便于问题排查
    请求参数：
    sort（排序）：可选，整数，最小值 1，用于项目系数列表的排序展示
    name（系数名称）：必填，字符串，最大长度 255 字符，项目系数的业务名称（如 "基础系数"“加急系数”）
    is_valid（是否有效）：必填，整数，仅允许值为 0（无效，暂不使用）或 1（有效，可参与业务计算）
    验证错误提示：
    项目系数名称不能为空：未提供 name 参数时返回
    项目系数名称长度不能超过 255 个字符：name 参数长度超出限制时返回
    请选择是否有效：未提供 is_valid 参数时返回
    排序必须是整数：sort 参数存在但非整数时返回
    排序值最小为 1：sort 参数存在且小于 1 时返回
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "创建成功"“创建失败” 或具体验证错误提示
    data（项目系数记录）：对象，创建成功时返回新创建的系数详情，包含以下字段：
    id（主键 ID）：整数，项目系数的唯一标识
    sort（排序）：整数，系数的排序值
    name（系数名称）：字符串，系数的业务名称
    is_valid（是否有效）：整数，0 = 无效，1 = 有效
    created_by（创建人 ID）：整数，当前登录用户的 ID
    updated_by（更新人 ID）：整数，当前登录用户的 ID
    created_at（创建时间）：时间戳，系数记录的创建时间
    updated_at（更新时间）：时间戳，系数记录的最后更新时间
    @param Request $request 请求对象，包含创建项目系数所需的参数
    @return \Illuminate\Http\JsonResponse JSON 响应，包含创建结果信息
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
    获取项目系数详情
    功能说明：
    根据 ID 查询项目系数记录，不存在时返回明确提示
    格式化时间字段为标准格式（Y-m-d H:i:s），无时间值时返回空字符串
    异常时记录日志，便于问题追溯
    请求参数：
    路径参数：id（项目系数 ID）：必填，整数，项目系数的唯一标识 ID，用于指定查询的记录
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取详情成功"“记录不存在” 或 “获取详情失败”
    data（详情数据）：对象，查询成功时返回格式化后的项目系数详情，包含以下字段：
    id（主键 ID）：整数，项目系数的唯一标识
    sort（排序）：整数，系数的排序值
    name（系数名称）：字符串，系数的业务名称（如 "基础系数"“加急系数”）
    is_valid（是否有效）：整数，0 = 无效（暂不使用），1 = 有效（可参与业务计算）
    created_by（创建人 ID）：整数，创建该系数的用户 ID
    updated_by（更新人 ID）：整数，最后更新该系数的用户 ID
    created_at（创建时间）：字符串，标准格式时间（Y-m-d H:i:s），无值时为空
    updated_at（更新时间）：字符串，标准格式时间（Y-m-d H:i:s），无值时为空
    @param int $id 项目系数 ID，用于指定查询的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含项目系数的详情信息
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
    更新项目系数
    功能说明：
    先校验记录是否存在，不存在返回明确提示
    验证更新参数合法性，支持自定义错误提示
    自动记录更新人为当前登录用户 ID，异常时记录日志便于排查
    请求参数：
    路径参数：id（项目系数 ID）：必填，整数，项目系数的唯一标识 ID，用于指定待更新的记录
    请求体参数：
    sort（排序）：可选，整数，最小值 1，用于更新项目系数的排序展示顺序
    name（系数名称）：必填，字符串，最大长度 255 字符，项目系数的业务名称（如 "基础系数"“加急系数”）
    is_valid（是否有效）：必填，整数，仅允许值为 0（无效，暂不使用）或 1（有效，可参与业务计算）
    验证错误提示：
    项目系数名称不能为空：未提供 name 参数时返回
    项目系数名称长度不能超过 255 个字符：name 参数长度超出限制时返回
    请选择是否有效：未提供 is_valid 参数时返回
    排序必须是整数：sort 参数存在但非整数时返回
    排序值最小为 1：sort 参数存在且小于 1 时返回
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "更新成功"“记录不存在”“更新失败” 或具体验证错误提示
    data（更新后记录）：对象，更新成功时返回更新后的项目系数详情，包含以下字段：
    id（主键 ID）：整数，项目系数的唯一标识
    sort（排序）：整数，更新后的排序值
    name（系数名称）：字符串，更新后的业务名称
    is_valid（是否有效）：整数，更新后的有效性状态（0 = 无效，1 = 有效）
    created_by（创建人 ID）：整数，原创建人的用户 ID（不随更新改变）
    updated_by（更新人 ID）：整数，当前登录用户的 ID（更新操作人）
    created_at（创建时间）：时间戳，原记录的创建时间
    updated_at（更新时间）：时间戳，本次更新后的时间
    @param Request $request 请求对象，包含更新项目系数所需的参数
    @param int $id 项目系数 ID，用于指定待更新的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含更新结果信息
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
    删除项目系数
    功能说明：
    先校验待删除记录是否存在，不存在返回明确提示
    执行物理删除操作，异常时记录日志便于问题追溯
    请求参数：
    路径参数：id（项目系数 ID）：必填，整数，项目系数的唯一标识 ID，用于指定待删除的记录
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "删除成功"“记录不存在” 或 “删除失败”
    @param int $id 项目系数 ID，用于指定待删除的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含删除结果信息
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
    获取项目系数选项列表（用于下拉框等选择场景）
    功能说明：
    仅返回有效状态的项目系数，按排序字段有序排列
    精简返回字段（ID、名称、排序），适配下拉框等组件的选择需求
    异常时记录日志，便于问题排查
    请求参数：无额外请求参数
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 "获取选项成功" 或 "获取选项列表失败"
    data（选项列表）：数组，每个元素为有效项目系数的核心信息，包含：
    id（主键 ID）：整数，项目系数的唯一标识（用于组件选中值）
    name（系数名称）：字符串，项目系数的业务名称（用于组件显示文本）
    sort（排序）：整数，系数的排序值（用于组件选项展示顺序）
    @return \Illuminate\Http\JsonResponse JSON 响应，包含适配选择组件的项目系数选项列表
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
    批量更新项目系数状态
    功能说明：
    支持一次性更新多条项目系数的有效性状态，统一设置为有效或无效
    自动记录更新人为当前登录用户 ID，返回明确的更新条数提示
    校验参数格式合法性，异常时记录异常日志便于排查
    请求参数：
    ids（记录 ID 数组）：必填，数组类型，元素为整数，需批量更新状态的项目系数 ID 集合
    is_valid（目标状态）：必填，整数，仅允许值为 0（无效，暂不使用）或 1（有效，可参与业务计算）
    验证错误提示：
    请选择要更新的记录：未提供 ids 参数或 ids 为空数组时返回
    ids 必须是数组：ids 参数非数组类型时返回
    请选择状态：未提供 is_valid 参数时返回
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，返回 “批量更新成功，共更新 X 条记录”“批量更新失败” 或具体验证错误提示
    @param Request $request 请求对象，包含批量更新所需的 ID 数组和目标状态
    @return \Illuminate\Http\JsonResponse JSON 响应，包含批量更新结果信息
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
