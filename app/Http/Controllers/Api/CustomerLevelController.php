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
     * 接口名称：获取客户等级列表
     *
     * 功能描述：根据筛选条件获取客户等级列表，支持分页和排序
     *
     * 传入参数：
     * - code (string, 可选): 客户等级编码，用于精确筛选
     * - name (string, 可选): 客户等级名称，用于模糊筛选
     * - is_valid (int, 可选): 是否有效（0:无效, 1:有效）
     * - page (int, 可选, 默认1): 当前页码
     * - limit (int, 可选, 默认10): 每页显示数量，最大100
     *
     * 输出参数：
     * - list (array): 客户等级列表数据
     *   - id (int): 等级ID
     *   - sort (int): 排序值
     *   - level_name (string): 等级名称
     *   - level_code (string): 等级编码
     *   - description (string): 描述
     *   - is_valid (int): 是否有效（0:无效, 1:有效）
     *   - created_by (string): 创建人姓名
     *   - updated_by (string): 更新人姓名
     *   - created_at (datetime): 创建时间
     *   - updated_at (datetime): 更新时间
     * - total (int): 总记录数
     * - page (int): 当前页码
     * - limit (int): 每页显示数量
     * - pages (int): 总页数
     */
    public function index(Request $request)
    {
        try {
            // 初始化查询构造器
            $query = CustomerLevel::query();

            // 搜索条件：根据等级编码精确查询
            if ($request->filled('code')) {
                $query->byCode($request->code);
            }

            // 搜索条件：根据等级名称模糊查询
            if ($request->filled('name')) {
                $query->byName($request->name);
            }

            // 搜索条件：根据有效性筛选
            if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序规则：先按sort升序，再按level_value降序
            $query->orderBy('sort', 'asc')->orderBy('level_value', 'desc');

            // 处理分页参数：确保page最小为1
            $page = max(1, (int)$request->get('page', 1));
            // 处理每页数量：确保limit在1-100之间
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 查询总数用于分页计算
            $total = $query->count();

            // 查询数据并进行关联查询（创建人和更新人信息）
            $list = $query->with(['creator:id,real_name', 'updater:id,real_name'])
                ->offset(($page - 1) * $limit)  // 计算偏移量
                ->limit($limit)                 // 限制返回数量
                ->get()
                // 对结果进行格式化处理
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sort' => $item->sort,
                        'level_name' => $item->level_name,
                        'level_code' => $item->level_code,
                        'description' => $item->description,
                        'is_valid' => $item->is_valid,
                        'created_by' => $item->creator->real_name ?? '',  // 获取创建人姓名，如果不存在则为空字符串
                        'updated_by' => $item->updater->real_name ?? '',  // 获取更新人姓名，如果不存在则为空字符串
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });

            // 返回成功响应，包含列表数据和分页信息
            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)  // 计算总页数
            ]);

        } catch (\Exception $e) {
            // 异常处理：记录日志并返回失败响应
            log_exception($e, '获取客户等级列表失败');
            return json_fail('获取列表失败');
        }
    }


    /**
     * 接口名称：创建客户等级
     *
     * 功能描述：创建一个新的客户等级记录
     *
     * 传入参数：
     * - level_name (string, 必填): 客户等级名称，最大255个字符
     * - level_code (string, 必填): 客户等级编码，最大50个字符，必须唯一
     * - description (string, 可选): 客户等级描述
     * - is_valid (int, 必填): 是否有效，只能是0(无效)或1(有效)
     * - sort (int, 可选): 排序值，必须是大于等于1的整数
     *
     * 输出参数：
     * - message (string): 操作结果消息
     * - data (object): 创建成功的客户等级对象
     *   - id (int): 等级ID
     *   - level_name (string): 等级名称
     *   - level_code (string): 等级编码
     *   - description (string): 描述
     *   - is_valid (int): 是否有效
     *   - sort (int): 排序值
     *   - created_by (int): 创建人ID
     *   - updated_by (int): 更新人ID
     *   - created_at (datetime): 创建时间
     *   - updated_at (datetime): 更新时间
     */
    public function store(Request $request)
    {
        try {
            // 验证请求数据的合法性
            $validator = Validator::make($request->all(), [
                'level_name' => 'required|string|max:255',           // 等级名称必填且不超过255字符
                'level_code' => 'required|string|max:50|unique:customer_levels,level_code', // 等级编码必填且唯一
                'description' => 'nullable|string',                  // 描述可为空
                'is_valid' => 'required|in:0,1',                     // 有效性只能是0或1
                'sort' => 'integer|min:1',                           // 排序值必须是大于等于1的整数
            ], [
                // 自定义验证错误消息
                'level_name.required' => '等级名称不能为空',
                'level_name.max' => '等级名称长度不能超过255个字符',
                'level_code.required' => '等级编码不能为空',
                'level_code.unique' => '等级编码已存在',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            // 如果验证失败，返回第一个错误信息
            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 获取所有请求数据并添加创建人和更新人信息
            $data = $request->all();
            $data['created_by'] = Auth::id();  // 设置创建人为当前认证用户
            $data['updated_by'] = Auth::id();  // 设置更新人为当前认证用户

            // 创建新的客户等级记录
            $item = CustomerLevel::create($data);

            // 返回成功响应及创建的数据
            return json_success('创建成功', $item);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '创建客户等级失败');
            // 返回失败响应
            return json_fail('创建失败');
        }
    }

    /**
     * 接口名称：获取客户等级详情
     *
     * 功能描述：根据ID获取指定客户等级的详细信息
     *
     * 传入参数：
     * - id (int, 路径参数): 客户等级ID
     *
     * 输出参数：
     * - message (string): 操作结果消息
     * - data (object): 客户等级详情对象
     *   - id (int): 等级ID
     *   - sort (int): 排序值
     *   - level_name (string): 等级名称
     *   - level_code (string): 等级编码
     *   - description (string): 描述
     *   - is_valid (int): 是否有效
     *   - created_by (string): 创建人姓名
     *   - updated_by (string): 更新人姓名
     *   - created_at (datetime): 创建时间
     *   - updated_at (datetime): 更新时间
     */
    public function show($id)
    {
        try {
            // 根据ID查询客户等级，并关联加载创建人和更新人信息
            $item = CustomerLevel::with(['creator:id,real_name', 'updater:id,real_name'])->find($id);

            // 如果记录不存在，返回失败响应
            if (!$item) {
                return json_fail('记录不存在');
            }

            // 构造返回结果数据
            $result = [
                'id' => $item->id,                              // 等级ID
                'sort' => $item->sort,                          // 排序值
                'level_name' => $item->level_name,              // 等级名称
                'level_code' => $item->level_code,              // 等级编码
                'description' => $item->description,            // 描述
                'is_valid' => $item->is_valid,                  // 是否有效
                'created_by' => $item->creator->real_name ?? '', // 创建人姓名，如果不存在则为空字符串
                'updated_by' => $item->updater->real_name ?? '', // 更新人姓名，如果不存在则为空字符串
                'created_at' => $item->created_at,              // 创建时间
                'updated_at' => $item->updated_at,              // 更新时间
            ];

            // 返回成功响应及详情数据
            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '获取客户等级详情失败');
            // 返回失败响应
            return json_fail('获取详情失败');
        }
    }

    /**
     * 接口名称：更新客户等级
     *
     * 功能描述：根据ID更新指定客户等级的信息
     *
     * 传入参数：
     * - id (int, 路径参数): 客户等级ID
     * - level_name (string, 必填): 客户等级名称，最大255个字符
     * - level_code (string, 必填): 客户等级编码，最大50个字符，必须唯一（排除当前记录）
     * - description (string, 可选): 客户等级描述
     * - is_valid (int, 必填): 是否有效，只能是0(无效)或1(有效)
     * - sort (int, 可选): 排序值，必须是大于等于1的整数
     *
     * 输出参数：
     * - message (string): 操作结果消息
     * - data (object): 更新后的客户等级对象
     *   - id (int): 等级ID
     *   - level_name (string): 等级名称
     *   - level_code (string): 等级编码
     *   - description (string): 描述
     *   - is_valid (int): 是否有效
     *   - sort (int): 排序值
     *   - created_by (int): 创建人ID
     *   - updated_by (int): 更新人ID
     *   - created_at (datetime): 创建时间
     *   - updated_at (datetime): 更新时间
     */
    public function update(Request $request, $id)
    {
        try {
            // 根据ID查找客户等级记录
            $item = CustomerLevel::find($id);

            // 如果记录不存在，返回失败响应
            if (!$item) {
                return json_fail('记录不存在');
            }

            // 验证请求数据的合法性
            $validator = Validator::make($request->all(), [
                'level_name' => 'required|string|max:255',                    // 等级名称必填且不超过255字符
                'level_code' => 'required|string|max:50|unique:customer_levels,level_code,' . $id, // 等级编码必填且唯一（排除当前记录）
                'description' => 'nullable|string',                           // 描述可为空
                'is_valid' => 'required|in:0,1',                              // 有效性只能是0或1
                'sort' => 'integer|min:1',                                    // 排序值必须是大于等于1的整数
            ], [
                // 自定义验证错误消息
                'level_name.required' => '等级名称不能为空',
                'level_name.max' => '等级名称长度不能超过255个字符',
                'level_code.required' => '等级编码不能为空',
                'level_code.unique' => '等级编码已存在',
                'is_valid.required' => '请选择是否有效',
                'sort.integer' => '排序必须是整数',
                'sort.min' => '排序值最小为1',
            ]);

            // 如果验证失败，返回第一个错误信息
            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 获取所有请求数据并设置更新人信息
            $data = $request->all();
            $data['updated_by'] = Auth::id();  // 设置更新人为当前认证用户

            // 更新客户等级记录
            $item->update($data);

            // 返回成功响应及更新后的数据
            return json_success('更新成功', $item);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '更新客户等级失败');
            // 返回失败响应
            return json_fail('更新失败');
        }
    }

    /**
     * 接口名称：删除客户等级
     *
     * 功能描述：根据ID删除指定的客户等级记录
     *
     * 传入参数：
     * - id (int, 路径参数): 客户等级ID
     *
     * 输出参数：
     * - message (string): 操作结果消息
     *
     * 注意事项：
     * - 删除前应检查是否有客户正在使用该等级
     */
    public function destroy($id)
    {
        try {
            // 根据ID查找客户等级记录
            $item = CustomerLevel::find($id);

            // 如果记录不存在，返回失败响应
            if (!$item) {
                return json_fail('记录不存在');
            }

            // TODO: 检查是否有客户在使用此等级（当Customer模型存在时启用）
            // $customersCount = $item->customers()->count();
            // if ($customersCount > 0) {
            //     return json_fail('该等级下还有客户在使用，无法删除');
            // }

            // 执行删除操作
            $item->delete();

            // 返回成功响应
            return json_success('删除成功');

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '删除客户等级失败');
            // 返回失败响应
            return json_fail('删除失败');
        }
    }

    /**
     * 接口名称：获取选项列表
     *
     * 功能描述：获取所有有效的客户等级选项列表，用于下拉选择等场景
     *
     * 传入参数：无
     *
     * 输出参数：
     * - message (string): 操作结果消息
     * - data (array): 客户等级选项列表
     *   - id (int): 等级ID
     *   - level_name (string): 等级名称
     *   - level_code (string): 等级编码
     *   - description (string): 描述
     */
    public function options()
    {
        try {
            // 查询所有有效的客户等级，只选择必要字段
            $data = CustomerLevel::valid()
                ->select('id', 'level_name', 'level_code', 'description')
                ->get()
                // 对结果进行格式化处理
                ->map(function ($item) {
                    return [
                        'id' => $item->id,              // 等级ID
                        'level_name' => $item->level_name, // 等级名称
                        'level_code' => $item->level_code, // 等级编码
                        'description' => $item->description, // 描述
                    ];
                });

            // 返回成功响应及选项数据
            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '获取选项列表失败');
            // 返回失败响应
            return json_fail('获取选项列表失败');
        }
    }

    /**
     * 接口名称：批量更新状态
     *
     * 功能描述：批量更新客户等级的有效状态
     *
     * 传入参数：
     * - ids (array, 必填): 客户等级ID数组
     * - is_valid (int, 必填): 状态值，只能是0(无效)或1(有效)
     *
     * 输出参数：
     * - message (string): 操作结果消息，包含更新记录数
     *
     * 示例：
     * {
     *   "ids": [1, 2, 3],
     *   "is_valid": 1
     * }
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
            // 验证请求数据的合法性
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',        // ids必须是数组且必填
                'ids.*' => 'integer',             // ids数组中的每个元素必须是整数
                'is_valid' => 'required|in:0,1'   // is_valid必须是0或1且必填
            ], [
                // 自定义验证错误消息
                'ids.required' => '请选择要更新的记录',
                'ids.array' => 'ids必须是数组',
                'is_valid.required' => '请选择状态',
            ]);

            // 如果验证失败，返回第一个错误信息
            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            // 批量更新客户等级状态
            $updated = CustomerLevel::whereIn('id', $request->ids)
                ->update([
                    'is_valid' => $request->is_valid,  // 更新状态值
                    'updated_by' => Auth::id()         // 设置更新人为当前认证用户
                ]);

            // 返回成功响应，包含更新记录数
            return json_success("批量更新成功，共更新{$updated}条记录");

        } catch (\Exception $e) {
            // 记录异常日志
            log_exception($e, '批量更新状态失败');
            // 返回失败响应
            return json_fail('批量更新失败');
        }
    }
}
