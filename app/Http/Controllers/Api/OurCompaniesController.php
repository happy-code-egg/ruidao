<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OurCompanies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OurCompaniesController extends Controller
{
 /**
 * 获取列表 index
 *
 * 功能描述：获取我方公司列表，支持关键字搜索、状态筛选和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - keyword (string, optional): 关键字搜索条件（匹配简称、全称或名称）
 *   - status (int, optional): 状态筛选条件（0-禁用，1-启用）
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 我方公司列表数据
 *     - id (int): 公司ID
 *     - status (int): 状态（0-禁用，1-启用）
 *     - status_text (string): 状态文本
 *     - sort_order (int): 排序
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *     - created_by (string): 创建人
 *     - updated_by (string): 更新人
 *     - name (string): 公司名称
 *     - code (string): 公司代码
 *     - short_name (string): 公司简称
 *     - full_name (string): 公司全称
 *     - credit_code (string): 信用代码
 *     - address (string): 地址
 *     - contact_person (string): 联系人
 *     - contact_phone (string): 联系电话
 *     - tax_number (string): 税号
 *     - bank (string): 开户行
 *     - account (string): 账号
 *     - invoice_phone (string): 发票电话
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = OurCompanies::query();

        // 关键字搜索（匹配简称、全称或名称）
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('short_name', 'like', "%{$keyword}%")
                  ->orWhere('full_name', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        // 状态筛选
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // 分页处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询，按排序字段和ID排序
        $data = $query->orderBy('sort_order')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get()->map(function ($item) {
                        // 格式化返回数据
                        return [
                            'id' => $item->id,
                            'status' => $item->status,
                            'status_text' => $item->status_text,
                            'sort_order' => $item->sort_order,
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            // 获取创建人和更新人姓名
                            'created_by' => $item->creator->real_name ?? '未知',
                            'updated_by' => $item->updater->real_name ?? '未知',
                            'name' => $item->name,
                            'code' => $item->code,
                            'short_name' => $item->short_name,
                            'full_name' => $item->full_name,
                            'credit_code' => $item->credit_code,
                            'address' => $item->address,
                            'contact_person' => $item->contact_person,
                            'contact_phone' => $item->contact_phone,
                            'tax_number' => $item->tax_number,
                            'bank' => $item->bank,
                            'account' => $item->account,
                            'invoice_phone' => $item->invoice_phone,
                        ];
                     });

        // 返回成功响应，包含分页数据
        return json_success('获取列表成功', [
            'list' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '获取我方公司列表失败: ' . $e->getMessage(),
            [
                'title' => '获取我方公司列表',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取列表失败');
    }
}


    /**
 * 获取详情 show
 *
 * 功能描述：根据ID获取单条我方公司详细信息
 *
 * 传入参数：
 * - id (int): 我方公司记录的ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 我方公司详细信息
 *   - id (int): 公司ID
 *   - status (int): 状态（0-禁用，1-启用）
 *   - status_text (string): 状态文本
 *   - sort_order (int): 排序
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 *   - created_by (int): 创建人ID
 *   - updated_by (int): 更新人ID
 *   - name (string): 公司名称
 *   - code (string): 公司代码
 *   - short_name (string): 公司简称
 *   - full_name (string): 公司全称
 *   - credit_code (string): 信用代码
 *   - address (string): 地址
 *   - contact_person (string): 联系人
 *   - contact_phone (string): 联系电话
 *   - tax_number (string): 税号
 *   - bank (string): 开户行
 *   - account (string): 账号
 *   - invoice_phone (string): 发票电话
 */
public function show($id)
{
    try {
        // 根据ID查找我方公司记录
        $item = OurCompanies::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应，包含记录详细信息
        return json_success('获取详情成功', $item);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '获取我方公司详情失败: ' . $e->getMessage(),
            [
                'title' => '获取我方公司详情',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取详情失败');
    }
}

/**
 * 创建 store
 *
 * 功能描述：创建新的我方公司记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - short_name (string, required): 公司简称，唯一，最大100字符
 *   - full_name (string, required): 公司全称，最大200字符
 *   - credit_code (string, required): 信用代码，最大50字符
 *   - address (string, optional): 地址，最大255字符
 *   - bank (string, optional): 开户行，最大100字符
 *   - account (string, optional): 账号，最大50字符
 *   - invoice_phone (string, optional): 发票电话，最大20字符
 *   - status (int, required): 状态，0或1
 *   - sort_order (int, optional): 排序，最小为0
 *   - code (string, optional): 公司代码
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建的我方公司信息
 */
public function store(Request $request)
{
    try {
        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'short_name' => 'required|string|max:100|unique:our_companies,short_name',
            'full_name' => 'required|string|max:200',
            'credit_code' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:100',
            'account' => 'nullable|string|max:50',
            'invoice_phone' => 'nullable|string|max:20',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ], [
            'short_name.required' => '我方公司简称不能为空',
            'short_name.unique' => '我方公司简称已存在',
            'full_name.required' => '我方公司全称不能为空',
            'credit_code.required' => '信用代码不能为空',
            'status.required' => '状态不能为空'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备创建数据
        $data = $request->all();
        $data['name'] = $request->short_name; // 兼容字段
        // 如果未提供code，则根据简称生成
        $data['code'] = $request->code ?? strtolower(str_replace(' ', '_', $request->short_name));

        // 设置创建人和更新人
        $data['created_by'] = auth()->user()->id;
        $data['updated_by'] = auth()->user()->id;

        // 创建我方公司记录
        $item = OurCompanies::create($data);

        // 返回成功响应，包含创建的记录信息
        return json_success('创建成功', $item);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '创建我方公司失败: ' . $e->getMessage(),
            [
                'title' => '创建我方公司',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('创建失败');
    }
}


  /**
 * 更新 update
 *
 * 功能描述：根据ID更新我方公司信息
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - short_name (string, required): 公司简称，唯一，最大100字符
 *   - full_name (string, required): 公司全称，最大200字符
 *   - credit_code (string, required): 信用代码，最大50字符
 *   - address (string, optional): 地址，最大255字符
 *   - bank (string, optional): 开户行，最大100字符
 *   - account (string, optional): 账号，最大50字符
 *   - invoice_phone (string, optional): 发票电话，最大20字符
 *   - status (int, required): 状态，0或1
 *   - sort_order (int, optional): 排序，最小为0
 *   - code (string, optional): 公司代码
 * - id (int): 要更新的我方公司ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的我方公司信息
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找我方公司记录
        $item = OurCompanies::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'short_name' => 'required|string|max:100|unique:our_companies,short_name,' . $id,
            'full_name' => 'required|string|max:200',
            'credit_code' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:100',
            'account' => 'nullable|string|max:50',
            'invoice_phone' => 'nullable|string|max:20',
            'status' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ], [
            'short_name.required' => '我方公司简称不能为空',
            'short_name.unique' => '我方公司简称已存在',
            'full_name.required' => '我方公司全称不能为空',
            'credit_code.required' => '信用代码不能为空',
            'status.required' => '状态不能为空'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备更新数据
        $data = $request->all();
        $data['name'] = $request->short_name; // 兼容字段
        // 如果未提供code，则根据简称生成
        $data['code'] = $request->code ?? strtolower(str_replace(' ', '_', $request->short_name));

        // 移除创建人字段，防止被修改
        unset($data['created_by']);
        // 设置更新人
        $data['updated_by'] = auth()->user()->id;

        // 更新我方公司记录
        $item->update($data);

        // 返回成功响应，包含更新后的记录信息
        return json_success('更新成功', $item);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '更新我方公司失败: ' . $e->getMessage(),
            [
                'title' => '更新我方公司',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('更新失败');
    }
}

/**
 * 删除 destroy
 *
 * 功能描述：根据ID删除单条我方公司记录
 *
 * 传入参数：
 * - id (int): 要删除的我方公司ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找我方公司记录
        $item = OurCompanies::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 删除我方公司记录
        $item->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '删除我方公司失败: ' . $e->getMessage(),
            [
                'title' => '删除我方公司',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('删除失败');
    }
}


   /**
 * 获取选项列表 options
 *
 * 功能描述：获取所有启用状态的我方公司选项列表，用于下拉框等场景
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项列表数据
 *   - id (int): 公司ID
 *   - label (string): 选项标签（公司简称）
 *   - value (string): 选项值（公司简称）
 */
public function options(Request $request)
{
    try {
        // 获取所有启用状态的我方公司，按排序字段和ID排序
        $data = OurCompanies::where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->select('id', 'short_name as label', 'short_name as value')
            ->get();

        // 返回成功响应，包含选项列表数据
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            \App\Models\Logs::TYPE_ERROR,
            '获取我方公司选项失败: ' . $e->getMessage(),
            [
                'title' => '获取我方公司选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取选项列表失败');
    }
}

/**
 * 获取验证错误消息 getValidationMessages
 *
 * 功能描述：获取自定义验证错误消息，用于表单验证
 *
 * 传入参数：无
 *
 * 输出参数：
 * - array: 验证错误消息数组
 *   - short_name.required: 公司简称不能为空
 *   - short_name.unique: 公司简称已存在
 *   - full_name.required: 公司全称不能为空
 *   - credit_code.required: 信用代码不能为空
 */
protected function getValidationMessages()
{
    // 合并父类验证消息和自定义验证消息
    return array_merge(parent::getValidationMessages(), [
        'short_name.required' => '公司简称不能为空',
        'short_name.unique' => '公司简称已存在',
        'full_name.required' => '公司全称不能为空',
        'credit_code.required' => '信用代码不能为空',
    ]);
}

}
