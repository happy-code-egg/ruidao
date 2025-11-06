<?php

namespace App\Http\Controllers\Api;

use App\Models\PatentAnnualFee;
use App\Models\PatentAnnualFeeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PatentAnnualFeesController extends BaseDataConfigController
{
    protected function getModelClass()
    {
        return PatentAnnualFee::class;
    }

   /**
 * 获取验证规则 getValidationRules
 *
 * 功能描述：定义专利年费配置的验证规则
 *
 * 传入参数：
 * - isUpdate (bool): 是否为更新操作，默认为false
 *
 * 输出参数：
 * - array: 验证规则数组
 *   - case_type (string): 项目类型，必填，最大100字符
 *   - apply_type (string): 申请类型，必填，最大100字符
 *   - country (string): 国家（地区），必填，最大100字符
 *   - start_date (string): 起算日，必填，最大100字符
 *   - currency (string): 币别，必填，最大10字符
 *   - has_fee_guide (int): 是否有缴费导览，可为空，值为0或1
 *   - sort_order (int): 排序，可为空，最小值0
 *   - is_valid (int): 是否有效，必填，值为0或1
 *   - updated_by (string): 更新人，可为空，最大100字符
 */
protected function getValidationRules($isUpdate = false)
{
    $rules = [
        'case_type' => 'required|string|max:100',
        'apply_type' => 'required|string|max:100',
        'country' => 'required|string|max:100',
        'start_date' => 'required|string|max:100',
        'currency' => 'required|string|max:10',
        'has_fee_guide' => 'nullable|in:0,1',
        'sort_order' => 'nullable|integer|min:0',
        'is_valid' => 'required|in:0,1',
        'updated_by' => 'nullable|string|max:100'
    ];

    return $rules;
}

/**
 * 获取验证错误信息 getValidationMessages
 *
 * 功能描述：定义专利年费配置验证错误的自定义消息
 *
 * 输出参数：
 * - array: 验证错误信息数组
 */
protected function getValidationMessages()
{
    return array_merge(parent::getValidationMessages(), [
        'case_type.required' => '项目类型不能为空',
        'apply_type.required' => '申请类型不能为空',
        'country.required' => '国家（地区）不能为空',
        'start_date.required' => '起算日不能为空',
        'currency.required' => '币别不能为空',
        'is_valid.required' => '是否有效不能为空',
        'is_valid.in' => '是否有效值无效',
    ]);
}

/**
 * 获取列表 index
 *
 * 功能描述：获取专利年费配置列表，支持搜索和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - case_type (string, optional): 项目类型搜索条件
 *   - apply_type (string, optional): 申请类型搜索条件
 *   - country (string, optional): 国家（地区）搜索条件
 *   - is_valid (int, optional): 是否有效筛选条件
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为10，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 专利年费配置列表数据
 *     - id (int): ID
 *     - case_type (string): 项目类型
 *     - apply_type (string): 申请类型
 *     - country (string): 国家（地区）
 *     - start_date (string): 起算日
 *     - currency (string): 币别
 *     - has_fee_guide (int): 是否有缴费导览
 *     - has_fee_guide_text (string): 是否有缴费导览文本
 *     - sort_order (int): 排序
 *     - is_valid (int): 是否有效
 *     - is_valid_text (string): 是否有效文本
 *     - created_by (string): 创建人
 *     - updated_by (string): 更新人
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = PatentAnnualFee::query();

        // 搜索条件
        if ($request->filled('case_type')) {
            $query->byCaseType($request->case_type);
        }

        if ($request->filled('apply_type')) {
            $query->byApplyType($request->apply_type);
        }

        if ($request->filled('country')) {
            $query->byCountry($request->country);
        }

        // 是否有效筛选
        if ($request->has('is_valid') && $request->is_valid !== '' && $request->is_valid !== null) {
            $query->where('is_valid', $request->is_valid);
        }

        // 排序
        $query->ordered();

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 10)));

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询
        $data = $query->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get();

        // 处理返回数据，补充关联信息
        foreach ($data as $item) {
            $item->is_valid_text = $item->is_valid_text;
            $item->has_fee_guide_text = $item->has_fee_guide_text;
            $item->created_by = $item->creator->real_name ?? '';
            $item->updated_by = $item->updater->real_name ?? '';
            $item->created_at = $item->created_at;
            $item->updated_at = $item->updated_at;
        }

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '获取专利年费配置列表失败');
        return json_fail('获取列表失败');
    }
}

  /**
 * 获取年费详情 getFeeDetails
 *
 * 功能描述：根据专利年费配置ID获取相关的年费详情列表
 *
 * 传入参数：
 * - id (int): 专利年费配置ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 年费详情列表
 *   - id (int): 年费详情ID
 *   - patent_annual_fee_id (int): 专利年费配置ID
 *   - stage_code (string): 阶段代码
 *   - rank (int): 年费序号
 *   - official_year (int): 官方缴费年限
 *   - official_month (int): 官方缴费月数
 *   - official_day (int): 官方缴费天数
 *   - start_year (int): 起始年
 *   - end_year (int): 结束年
 *   - base_fee (float): 基本费用
 *   - small_fee (float): 小实体费用
 *   - micro_fee (float): 微实体费用
 *   - authorization_fee (float): 授权费
 *   - sort_order (int): 排序
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function getFeeDetails($id)
{
    try {
        // 根据专利年费配置ID查询年费详情，并按排序字段排序
        $details = PatentAnnualFeeDetail::where('patent_annual_fee_id', $id)
            ->ordered()
            ->get();

        // 返回成功响应
        return json_success('获取成功', $details);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '获取年费详情失败');
        return json_fail('获取年费详情失败');
    }
}

/**
 * 创建年费详情 createFeeDetail
 *
 * 功能描述：创建新的专利年费详情记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - patent_annual_fee_id (int): 专利年费配置ID，必填，必须存在
 *   - stage_code (string): 阶段代码，必填，最大100字符
 *   - rank (int): 年费序号，必填，最小值1
 *   - official_year (int, optional): 官方缴费年限，最小值0
 *   - official_month (int, optional): 官方缴费月数，0-11之间
 *   - official_day (int, optional): 官方缴费天数，0-31之间
 *   - start_year (int, optional): 起始年，最小值0
 *   - end_year (int, optional): 结束年，最小值0
 *   - base_fee (float, optional): 基本费用，最小值0
 *   - small_fee (float, optional): 小实体费用，最小值0
 *   - micro_fee (float, optional): 微实体费用，最小值0
 *   - authorization_fee (float, optional): 授权费，最小值0
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建的年费详情对象
 */
public function createFeeDetail(Request $request)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'patent_annual_fee_id' => 'required|integer|exists:patent_annual_fees,id',
            'stage_code' => 'required|string|max:100',
            'rank' => 'required|integer|min:1',
            'official_year' => 'nullable|integer|min:0',
            'official_month' => 'nullable|integer|min:0|max:11',
            'official_day' => 'nullable|integer|min:0|max:31',
            'start_year' => 'nullable|integer|min:0',
            'end_year' => 'nullable|integer|min:0',
            'base_fee' => 'nullable|numeric|min:0',
            'small_fee' => 'nullable|numeric|min:0',
            'micro_fee' => 'nullable|numeric|min:0',
            'authorization_fee' => 'nullable|numeric|min:0',
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 创建年费详情记录
        $detail = PatentAnnualFeeDetail::create($request->all());

        // 返回成功响应
        return json_success('创建成功', $detail);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '创建年费详情失败');
        return json_fail('创建失败');
    }
}

/**
 * 更新年费详情 updateFeeDetail
 *
 * 功能描述：更新指定ID的专利年费详情记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - stage_code (string): 阶段代码，必填，最大100字符
 *   - rank (int): 年费序号，必填，最小值1
 *   - official_year (int, optional): 官方缴费年限，最小值0
 *   - official_month (int, optional): 官方缴费月数，0-11之间
 *   - official_day (int, optional): 官方缴费天数，0-31之间
 *   - start_year (int, optional): 起始年，最小值0
 *   - end_year (int, optional): 结束年，最小值0
 *   - base_fee (float, optional): 基本费用，最小值0
 *   - small_fee (float, optional): 小实体费用，最小值0
 *   - micro_fee (float, optional): 微实体费用，最小值0
 *   - authorization_fee (float, optional): 授权费，最小值0
 * - id (int): 年费详情ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的年费详情对象
 */
public function updateFeeDetail(Request $request, $id)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'stage_code' => 'required|string|max:100',
            'rank' => 'required|integer|min:1',
            'official_year' => 'nullable|integer|min:0',
            'official_month' => 'nullable|integer|min:0|max:11',
            'official_day' => 'nullable|integer|min:0|max:31',
            'start_year' => 'nullable|integer|min:0',
            'end_year' => 'nullable|integer|min:0',
            'base_fee' => 'nullable|numeric|min:0',
            'small_fee' => 'nullable|numeric|min:0',
            'micro_fee' => 'nullable|numeric|min:0',
            'authorization_fee' => 'nullable|numeric|min:0',
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 查找并更新年费详情记录
        $detail = PatentAnnualFeeDetail::findOrFail($id);
        $detail->update($request->all());

        // 返回成功响应
        return json_success('更新成功', $detail);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '更新年费详情失败');
        return json_fail('更新失败');
    }
}


  /**
 * 删除年费详情 deleteFeeDetail
 *
 * 功能描述：删除指定ID的专利年费详情记录
 *
 * 传入参数：
 * - id (int): 年费详情ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function deleteFeeDetail($id)
{
    try {
        // 查找并删除年费详情记录
        $detail = PatentAnnualFeeDetail::findOrFail($id);
        $detail->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '删除年费详情失败');
        return json_fail('删除失败');
    }
}

/**
 * 创建 store
 *
 * 功能描述：创建新的专利年费配置记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象，包含专利年费配置数据
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建结果数据
 */
public function store(Request $request)
{
    // 创建前处理数据
    $this->beforeStore($request);
    // 调用父类的创建方法
    return parent::store($request);
}

/**
 * 更新 update
 *
 * 功能描述：更新指定ID的专利年费配置记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象，包含专利年费配置数据
 * - id (int): 专利年费配置ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新结果数据
 */
public function update(Request $request, $id)
{
    // 更新前处理数据
    $this->beforeUpdate($request, $id);
    // 调用父类的更新方法
    return parent::update($request, $id);
}

    /**
 * 创建前处理数据 beforeStore
 *
 * 功能描述：在创建专利年费配置之前处理默认数据
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 * - id (int): 专利年费配置ID（创建时无用）
 *
 * 处理逻辑：
 * - 如果未提供更新人，则设置默认值为"系统"
 * - 如果未提供排序值，则设置为当前最大排序值+1
 * - 如果未提供缴费导览标志，则默认设置为1（有缴费导览）
 */
protected function beforeStore(Request $request)
{
    // 设置更新人
    if (!$request->filled('updated_by')) {
        $request->merge(['updated_by' => '系统']);
    }

    // 设置默认排序
    if (!$request->filled('sort_order')) {
        $maxSort = PatentAnnualFee::max('sort_order') ?? 0;
        $request->merge(['sort_order' => $maxSort + 1]);
    }

    // 设置默认缴费导览
    if (!$request->filled('has_fee_guide')) {
        $request->merge(['has_fee_guide' => 1]);
    }
}

/**
 * 更新前处理数据 beforeUpdate
 *
 * 功能描述：在更新专利年费配置之前处理默认数据
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 * - id (int): 专利年费配置ID
 *
 * 处理逻辑：
 * - 如果未提供更新人，则设置默认值为"系统"
 */
protected function beforeUpdate(Request $request, $id)
{
    // 设置更新人
    if (!$request->filled('updated_by')) {
        $request->merge(['updated_by' => '系统']);
    }
}

/**
 * 获取选项数据 options
 *
 * 功能描述：获取有效的专利年费配置选项数据，用于下拉选择
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项数据列表
 *   - id (int): 专利年费配置ID
 *   - case_type (string): 项目类型
 *   - apply_type (string): 申请类型
 *   - country (string): 国家（地区）
 *   - currency (string): 币别
 */
public function options(Request $request)
{
    try {
        // 查询有效的专利年费配置，按排序字段排序，并选择指定字段
        $data = PatentAnnualFee::valid()
            ->ordered()
            ->select('id', 'case_type', 'apply_type', 'country', 'currency')
            ->get();

        // 返回成功响应
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        log_exception($e, '获取专利年费配置选项失败');
        return json_fail('获取选项失败');
    }
}

}
