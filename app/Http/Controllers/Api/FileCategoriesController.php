<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 文件大类小类控制器
 */
class FileCategoriesController extends Controller
{
   /**
 * 获取列表 index
 *
 * 功能描述：获取文件大类小类的列表数据，支持搜索和分页功能
 *
 * 传入参数：
 * - mainCategory (string, optional): 文件大类名称搜索关键词
 * - subCategory (string, optional): 文件小类名称搜索关键词
 * - isValid (boolean, optional): 是否有效状态筛选
 * - page (int, optional): 页码，默认为1
 * - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 文件大类小类列表数据
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器
        $query = FileCategories::query();

        // 文件大类名称搜索条件
        if ($request->has('mainCategory') && !empty($request->mainCategory)) {
            $query->where('main_category', 'like', '%' . $request->mainCategory . '%');
        }

        // 文件小类名称搜索条件
        if ($request->has('subCategory') && !empty($request->subCategory)) {
            $query->where('sub_category', 'like', '%' . $request->subCategory . '%');
        }

        // 是否有效状态筛选条件
        if ($request->has('isValid') && $request->isValid !== '' && $request->isValid !== null) {
            $query->where('is_valid', $request->isValid);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行查询并获取数据，按sort和id排序
        $data = $query->orderBy('sort')
                     ->orderBy('id')
                     ->offset(($page - 1) * $limit)
                     ->limit($limit)
                     ->get();

        // 返回成功响应
        return json_success('获取列表成功', [
            'list' => $data->toArray(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "获取文件大类小类列表失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类列表',
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
 * 功能描述：根据ID获取文件大类小类的详细信息
 *
 * 传入参数：
 * - id (int): 文件大类小类记录的唯一标识符
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 文件大类小类详细信息
 *   - id (int): 记录ID
 *   - main_category (string): 文件大类名称
 *   - sub_category (string): 文件小类名称
 *   - is_valid (boolean): 是否有效
 *   - sort (int): 排序值
 *   - updated_by (string): 最后更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找文件大类小类记录
        $item = FileCategories::find($id);

        // 检查记录是否存在
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应和记录详情
        return json_success('获取详情成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "获取文件大类小类详情失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类详情',
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
 * 功能描述：创建新的文件大类小类记录
 *
 * 传入参数：
 * - mainCategory (string): 文件大类名称，必填，最长100个字符
 * - subCategory (string): 文件小类名称，必填，最长100个字符
 * - isValid (boolean): 是否有效状态，必填
 * - sort (int, optional): 排序值，最小为1，默认为1
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建成功的记录信息
 *   - id (int): 记录ID
 *   - main_category (string): 文件大类名称
 *   - sub_category (string): 文件小类名称
 *   - is_valid (boolean): 是否有效
 *   - sort (int): 排序值
 *   - updated_by (string): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function store(Request $request)
{
    try {
        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'mainCategory' => 'required|string|max:100',
            'subCategory' => 'required|string|max:100',
            'isValid' => 'required|boolean',
            'sort' => 'nullable|integer|min:1'
        ], [
            'mainCategory.required' => '文件大类名称不能为空',
            'mainCategory.max' => '文件大类名称长度不能超过100个字符',
            'subCategory.required' => '文件小类名称不能为空',
            'subCategory.max' => '文件小类名称长度不能超过100个字符',
            'isValid.required' => '是否有效不能为空',
            'isValid.boolean' => '是否有效必须是布尔值',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于1'
        ]);

        // 如果验证失败，返回错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备要保存的数据
        $data = [
            'main_category' => $request->mainCategory,
            'sub_category' => $request->subCategory,
            'is_valid' => $request->isValid,
            'sort' => $request->sort ?? 1,
            'updated_by' => auth()->user()->name ?? '系统管理员'
        ];

        // 创建新记录
        $item = FileCategories::create($data);

        // 返回成功响应和创建的记录信息
        return json_success('创建成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "创建文件大类小类失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类创建',
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
 * 功能描述：根据ID更新文件大类小类记录
 *
 * 传入参数：
 * - id (int): 要更新的记录ID
 * - mainCategory (string): 文件大类名称，必填，最长100个字符
 * - subCategory (string): 文件小类名称，必填，最长100个字符
 * - isValid (boolean): 是否有效状态，必填
 * - sort (int, optional): 排序值，最小为1，默认为1
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的记录信息
 *   - id (int): 记录ID
 *   - main_category (string): 文件大类名称
 *   - sub_category (string): 文件小类名称
 *   - is_valid (boolean): 是否有效
 *   - sort (int): 排序值
 *   - updated_by (string): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找要更新的文件大类小类记录
        $item = FileCategories::find($id);

        // 检查记录是否存在
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证请求参数
        $validator = Validator::make($request->all(), [
            'mainCategory' => 'required|string|max:100',
            'subCategory' => 'required|string|max:100',
            'isValid' => 'required|boolean',
            'sort' => 'nullable|integer|min:1'
        ], [
            'mainCategory.required' => '文件大类名称不能为空',
            'mainCategory.max' => '文件大类名称长度不能超过100个字符',
            'subCategory.required' => '文件小类名称不能为空',
            'subCategory.max' => '文件小类名称长度不能超过100个字符',
            'isValid.required' => '是否有效不能为空',
            'isValid.boolean' => '是否有效必须是布尔值',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于1'
        ]);

        // 如果验证失败，返回错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备要更新的数据
        $data = [
            'main_category' => $request->mainCategory,
            'sub_category' => $request->subCategory,
            'is_valid' => $request->isValid,
            'sort' => $request->sort ?? 1,
            'updated_by' => auth()->user()->name ?? '系统管理员'
        ];

        // 执行更新操作
        $item->update($data);

        // 返回成功响应和更新后的记录信息
        return json_success('更新成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "更新文件大类小类失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类更新',
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
 * 功能描述：根据ID删除文件大类小类记录
 *
 * 传入参数：
 * - id (int): 要删除的记录ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找要删除的文件大类小类记录
        $item = FileCategories::find($id);

        // 检查记录是否存在
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 执行删除操作
        $item->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "删除文件大类小类失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类删除',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('删除失败');
    }
}


    /**
 * 获取选项列表（用于下拉框等） options
 *
 * 功能描述：获取所有有效的文件大类小类选项列表，用于下拉框等场景
 *
 * 传入参数：
 * - 无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项列表数据
 *   - id (int): 记录ID
 *   - label (string): 显示标签（文件大类名称）
 *   - value (string): 选项值（文件大类名称）
 */
public function options(Request $request)
{
    try {
        // 获取所有有效且已排序的文件大类小类记录，并选择需要的字段
        $data = FileCategories::enabled()->ordered()
            ->select('id', 'main_category as label', 'main_category as value')
            ->get();

        // 返回成功响应和选项数据
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志
        $this->log(
            8,
            "获取文件大类小类选项失败: {$e->getMessage()}",
            [
                'title' => '文件大类小类选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取选项列表失败');
    }
}

}
