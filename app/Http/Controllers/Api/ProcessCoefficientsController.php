<?php

namespace App\Http\Controllers\Api;

use App\Models\ProcessCoefficient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 处理事项系数控制器
 *
 * 功能:
 * - 提供处理事项系数的列表、详情、创建、更新、删除及选项接口
 * - 支持批量更新有效状态
 *
 * 路由说明(绑定于 /api/data-config/process-coefficients*):
 * - GET    /api/data-config/process-coefficients                  => index
 * - GET    /api/data-config/process-coefficients/options          => options
 * - POST   /api/data-config/process-coefficients                  => store
 * - GET    /api/data-config/process-coefficients/{id}             => show (继承自父类)
 * - PUT    /api/data-config/process-coefficients/{id}             => update
 * - DELETE /api/data-config/process-coefficients/{id}             => destroy (继承自父类)
 * - POST   /api/data-config/process-coefficients/batch-status     => batchUpdateStatus
 *
 * 统一返回:
 * - 成功: json_success(message, data?)
 * - 失败: json_fail(message)
 *
 * 依赖:
 * - 模型: App\Models\ProcessCoefficient
 * - 验证: Validator
 *
 * 异常处理:
 * - 捕获 \Exception 并通过 log_exception 记录错误详情
 */
class ProcessCoefficientsController extends BaseDataConfigController
{
    /**
     * 功能: 返回该控制器使用的模型类名
     * 返回参数: string 模型类全名
     * 内部说明: 供父类通用CRUD逻辑获取模型
     */
    protected function getModelClass()
    {
        return ProcessCoefficient::class;
    }

    /**
     * 功能: 获取创建/更新时的字段校验规则
     * 请求参数:
     * - name(string, 必填, <=100)
     * - sort(int, 可空, >=1)
     * - is_valid(0|1, 必填)
     * - updated_by(string, 可空, <=100)
     * 返回参数: 数组形式的 Laravel 验证规则
     * 内部说明: $isUpdate 仅用于区分更新场景（此处规则一致）
     */
    protected function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'sort' => 'nullable|integer|min:1',
            'is_valid' => 'required|in:0,1',
            'updated_by' => 'nullable|string|max:100'
        ];

        return $rules;
    }

    /**
     * 功能: 获取字段校验的错误消息
     * 返回参数: 数组合并父类消息后的完整提示
     */
    protected function getValidationMessages()
    {
        return array_merge(parent::getValidationMessages(), [
            'name.required' => '处理事项系数名称不能为空',
            'name.max' => '处理事项系数名称长度不能超过100个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'is_valid.required' => '是否有效不能为空',
            'is_valid.in' => '是否有效值无效',
        ]);
    }

    /**
     * 功能: 获取处理事项系数列表
     * 路由说明: GET /api/data-config/process-coefficients
     * 请求参数(Query):
     * - name(string, 可选): 名称关键词，模糊匹配
     * - is_valid(0|1, 可选): 是否有效过滤
     * - page(int, 默认1): 页码，最小1
     * - limit(int, 默认10): 每页数量，范围1-100
     * 返回参数:
     * - list(array): 列表数据
     * - total(int): 总条数
     * - page(int): 当前页码
     * - limit(int): 每页数量
     * - pages(int): 总页数
     * 内部说明: 通过作用域 ordered() 排序
     * 异常处理: 捕获异常并 log_exception 记录，返回 json_fail('获取列表失败')
     */
    public function index(Request $request)
    {
        try {
            $query = ProcessCoefficient::query();

            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('is_valid')) {
                $query->where('is_valid', $request->is_valid);
            }

            // 排序
            $query->ordered();

            // 分页
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $data = $query->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            return json_success('获取列表成功', [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项系数列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 功能: 创建处理事项系数
     * 路由说明: POST /api/data-config/process-coefficients
     * 请求参数(Body): 参考 getValidationRules
     * 内部说明: 创建前调用 beforeStore 以设置默认字段
     * 返回参数: 创建后的记录对象
     */
    public function store(Request $request)
    {
        $this->beforeStore($request);
        return parent::store($request);
    }

    /**
     * 功能: 更新处理事项系数
     * 路由说明: PUT /api/data-config/process-coefficients/{id}
     * 请求参数(Path):
     * - id(int): 记录ID
     * 请求参数(Body): 参考 getValidationRules
     * 内部说明: 更新前调用 beforeUpdate 以补充字段
     * 返回参数: 更新后的记录对象
     */
    public function update(Request $request, $id)
    {
        $this->beforeUpdate($request, $id);
        return parent::update($request, $id);
    }

    /**
     * 功能: 创建前处理数据
     * 请求参数: Request 对象，包含待创建字段
     * 内部说明:
     * - 若未提供 updated_by，则设置为 '系统'
     * - 若未提供 sort，则按当前最大排序值 + 1 设置默认排序
     */
    protected function beforeStore(Request $request)
    {
        // 设置更新人
        if (!$request->filled('updated_by')) {
            $request->merge(['updated_by' => '系统']);
        }

        // 设置默认排序
        if (!$request->filled('sort')) {
            $maxSort = ProcessCoefficient::max('sort') ?? 0;
            $request->merge(['sort' => $maxSort + 1]);
        }
    }

    /**
     * 功能: 更新前处理数据
     * 请求参数:
     * - id(int): 记录ID
     * 内部说明: 若未提供 updated_by，则设置为 '系统'
     */
    protected function beforeUpdate(Request $request, $id)
    {
        // 设置更新人
        if (!$request->filled('updated_by')) {
            $request->merge(['updated_by' => '系统']);
        }
    }

    /**
     * 功能: 获取选项数据
     * 路由说明: GET /api/data-config/process-coefficients/options
     * 请求参数: 无
     * 返回参数: 数组 [{id, name, sort}]
     * 内部说明: 仅返回有效(valid)且按排序(ordered)的记录
     * 异常处理: 捕获异常并 log_exception 记录，返回 json_fail('获取选项失败')
     */
    public function options(Request $request)
    {
        try {
            $data = ProcessCoefficient::valid()
                ->ordered()
                ->select('id', 'name', 'sort')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项系数选项失败');
            return json_fail('获取选项失败');
        }
    }

    /**
     * 功能: 批量更新处理事项系数的有效状态
     * 路由说明: POST /api/data-config/process-coefficients/batch-status
     * 请求参数(Body):
     * - ids(array<int>, 必填): 待更新记录ID集合，需存在于 process_coefficients 表
     * - is_valid(0|1, 必填): 目标状态
     * 返回参数: 成功消息
     * 异常处理: 验证失败返回 json_fail；其他异常 log_exception 记录并返回 json_fail('批量更新失败')
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:process_coefficients,id',
                'is_valid' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            ProcessCoefficient::whereIn('id', $request->ids)
                ->update([
                    'is_valid' => $request->is_valid,
                    'updated_by' => '系统',
                    'updated_at' => now()
                ]);

            return json_success('批量更新成功');

        } catch (\Exception $e) {
            log_exception($e, '批量更新处理事项系数状态失败');
            return json_fail('批量更新失败');
        }
    }
}
