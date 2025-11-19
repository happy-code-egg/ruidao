<?php

namespace App\Http\Controllers\Api;

use App\Models\ManuscriptScoringItems;
use Illuminate\Http\Request;

class ManuscriptScoringItemsController extends BaseDataConfigController
{
   /**
 * 获取模型类名 getModelClass
 *
 * 功能描述：返回当前控制器使用的模型类名
 *
 * 传入参数：无
 *
 * 输出参数：
 * - string: 模型类名 ManuscriptScoringItems::class
 */
protected function getModelClass()
{
    return ManuscriptScoringItems::class;
}

/**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义稿件评分项数据的验证规则，包括创建和更新时的不同规则
 *
 * 传入参数：
 * - $isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - sort (int): 排序号，可选，整数，最小值1
 *   - name (string): 审核打分项名称，必填，字符串，最大100字符
 *   - code (string): 审核打分项编码，必填，字符串，最大50字符，唯一性验证
 *   - major_category (string): 大类，必填，字符串，最大100字符
 *   - minor_category (string): 小类，必填，字符串，最大100字符
 *   - description (string): 描述，可选，字符串
 *   - score (int): 得分，可选，整数，最小值0
 *   - max_score (int): 最大分值，必填，整数，最小值1
 *   - weight (numeric): 权重，可选，数值型，最小值0
 *   - status (int): 状态，必填，只能是0或1
 *   - sort_order (int): 排序，可选，整数，最小值0
 */
protected function getValidationRules($isUpdate = false)
{
    // 定义基础验证规则
    $rules = [
        'sort' => 'nullable|integer|min:1',             // 排序号：可选，整数，最小值1
        'name' => 'required|string|max:100',            // 审核打分项名称：必填，字符串，最大100字符
        'code' => 'required|string|max:50',             // 审核打分项编码：必填，字符串，最大50字符
        'major_category' => 'required|string|max:100',  // 大类：必填，字符串，最大100字符
        'minor_category' => 'required|string|max:100',  // 小类：必填，字符串，最大100字符
        'description' => 'nullable|string',              // 描述：可为空，字符串
        'score' => 'nullable|integer|min:0',            // 得分：可选，整数，最小值0
        'max_score' => 'required|integer|min:1',        // 最大分值：必填，整数，最小值1
        'weight' => 'nullable|numeric|min:0',           // 权重：可选，数值型，最小值0
        'status' => 'required|in:0,1',                  // 状态：必填，只能是0或1
        'sort_order' => 'nullable|integer|min:0',       // 排序：可为空，整数，最小值0
    ];

    // 根据是否为更新操作设置编码的唯一性验证规则
    if ($isUpdate) {
        // 更新时，排除当前记录的唯一性验证
        $id = request()->route('id');
        $rules['code'] .= '|unique:manuscript_scoring_items,code,' . $id;
    } else {
        // 创建时，全局唯一性验证
        $rules['code'] .= '|unique:manuscript_scoring_items,code';
    }

    // 返回验证规则
    return $rules;
}

/**
 * 获取验证错误消息 getValidationMessages
 *
 * 功能描述：定义验证失败时的错误消息，继承父类消息并添加特定消息
 *
 * 传入参数：无
 *
 * 输出参数：
 * - array: 验证错误消息数组
 */
protected function getValidationMessages()
{
    // 合并父类的验证消息和当前类的特定验证消息
    return array_merge(parent::getValidationMessages(), [
        'name.required' => '审核打分项名称不能为空',      // 名称必填验证消息
        'code.required' => '审核打分项编码不能为空',      // 编码必填验证消息
        'code.unique' => '审核打分项编码已存在',          // 编码唯一性验证消息
    ]);
}

/**
 * 获取审核打分项列表（重写父类方法以支持大类和小类独立筛选）
 *
 * 功能描述：查询审核打分项列表，支持大类、小类、关键字、状态等多条件筛选
 *
 * 请求参数(Query):
 * - major_category (string, 可选): 大类，模糊匹配
 * - minor_category (string, 可选): 小类，模糊匹配
 * - keyword (string, 可选): 关键字，在name、code、description字段中模糊匹配
 * - status (0|1, 可选): 状态筛选
 * - page (int, 默认1): 页码
 * - limit (int, 默认15): 每页数量
 *
 * 返回参数:
 * - list (array): 列表数据
 * - total (int): 总条数
 * - page (int): 当前页码
 * - limit (int): 每页数量
 * - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        $query = ManuscriptScoringItems::query();

        // 大类筛选（独立筛选）
        if ($request->has('major_category') && !empty($request->major_category)) {
            $query->where('major_category', 'like', '%' . $request->major_category . '%');
        }

        // 小类筛选（独立筛选）
        if ($request->has('minor_category') && !empty($request->minor_category)) {
            $query->where('minor_category', 'like', '%' . $request->minor_category . '%');
        }

        // 关键字搜索（在name、code、description中搜索）
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // 状态筛选
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // 分页
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总数
        $total = $query->count();

        // 获取数据
        $data = $query->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
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
        log_exception($e, '获取审核打分项列表失败');
        return json_fail('获取列表失败');
    }
}

}
