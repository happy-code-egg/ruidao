<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileCategories;
use App\Models\FileDescriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 文件描述控制器
 */
class FileDescriptionsController extends Controller
{
    /**
 * 获取列表 index
 *
 * 功能描述：获取文件描述列表数据，支持多种筛选条件和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - caseType (string, optional): 项目类型搜索条件
 *   - fileName (string, optional): 文件名称搜索关键词
 *   - country (string|array, optional): 国家搜索条件，支持单个或多个
 *   - fileCode (string, optional): 文件编号搜索关键词
 *   - fileCategoryMajor (string, optional): 文件大类搜索条件
 *   - fileCategoryMinor (string, optional): 文件小类搜索条件
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15，最大100
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 文件描述列表数据
 *     - id (int): ID
 *     - case_type (string): 项目类型
 *     - country (string): 国家（地区）
 *     - file_category_major (string): 文件大类
 *     - file_category_minor (string): 文件小类
 *     - file_name (string): 文件名称
 *     - file_name_en (string): 文件英文名称
 *     - file_code (string): 文件编号
 *     - internal_code (string): 内部编号
 *     - sort_order (int): 排序
 *     - file_description (string): 文件描述
 *     - authorized_client (int): 授权客户端
 *     - authorized_role (string): 授权角色
 *     - is_valid (int): 是否有效
 *     - created_by (int): 创建人
 *     - updated_by (int): 更新人
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
        $query = FileDescriptions::query();

        // 项目类型搜索条件（支持多选）
        if ($request->has('caseType') && !empty($request->caseType)) {
            if (is_array($request->caseType)) {
                $query->whereIn('case_type', $request->caseType);
            } else {
                $query->where('case_type', $request->caseType);
            }
        }

        // 文件名称搜索条件
        if ($request->has('fileName') && !empty($request->fileName)) {
            $query->where('file_name', 'like', '%' . $request->fileName . '%');
        }

        // 国家搜索条件（支持多选）
        if ($request->has('country') && !empty($request->country)) {
            if (is_array($request->country)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->country as $country) {
                        $q->orWhere('country', 'like', '%' . $country . '%');
                    }
                });
            } else {
                $query->where('country', 'like', '%' . $request->country . '%');
            }
        }

        // 文件编号搜索条件
        if ($request->has('fileCode') && !empty($request->fileCode)) {
            $query->where('file_code', 'like', '%' . $request->fileCode . '%');
        }

        // 文件大类搜索条件
        if ($request->has('fileCategoryMajor') && !empty($request->fileCategoryMajor)) {
            $query->where('file_category_major', $request->fileCategoryMajor);
        }

        // 文件小类搜索条件
        if ($request->has('fileCategoryMinor') && !empty($request->fileCategoryMinor)) {
            $query->where('file_category_minor', $request->fileCategoryMinor);
        }

        // 分页参数处理
        $page = max(1, (int)$request->get('page', 1));
        $limit = max(1, min(100, (int)$request->get('limit', 15)));

        // 获取总记录数
        $total = $query->count();

        // 执行分页查询，按排序和ID排序
        $data = $query->orderBy('sort_order')
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
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件描述列表失败: {$e->getMessage()}",
            [
                'title' => '文件描述列表',
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
 * 功能描述：根据ID获取文件描述的详细信息
 *
 * 传入参数：
 * - id (int): 文件描述ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 文件描述详细信息
 *   - id (int): ID
 *   - case_type (string): 项目类型
 *   - country (string): 国家（地区）
 *   - file_category_major (string): 文件大类
 *   - file_category_minor (string): 文件小类
 *   - file_name (string): 文件名称
 *   - file_name_en (string): 文件英文名称
 *   - file_code (string): 文件编号
 *   - internal_code (string): 内部编号
 *   - sort_order (int): 排序
 *   - file_description (string): 文件描述
 *   - authorized_client (int): 授权客户端
 *   - authorized_role (string): 授权角色
 *   - is_valid (int): 是否有效
 *   - created_by (int): 创建人
 *   - updated_by (int): 更新人
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 */
public function show($id)
{
    try {
        // 根据ID查找文件描述记录
        $item = FileDescriptions::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 返回成功响应，包含详细信息
        return json_success('获取详情成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件描述详情失败: {$e->getMessage()}",
            [
                'title' => '文件描述详情',
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
 * 功能描述：创建新的文件描述记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - caseType (string): 项目类型，必填，最大100字符
 *   - country (string): 国家（地区），必填
 *   - fileCategoryMajor (string): 文件大类，必填，最大100字符
 *   - fileCategoryMinor (string): 文件小类，必填，最大100字符
 *   - fileName (string): 文件名称，必填，最大200字符
 *   - fileNameEn (string, optional): 文件英文名称，最大200字符
 *   - fileCode (string): 文件编号，必填，最大50字符
 *   - internalCode (string, optional): 内部编号，最大50字符
 *   - sortOrder (int, optional): 排序，默认为1
 *   - fileDescription (string, optional): 文件描述
 *   - authorizedClient (int, optional): 授权客户端
 *   - authorizedRole (string, optional): 授权角色
 *   - isValid (int): 是否有效，必填，值为0或1
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 创建的文件描述对象
 */
public function store(Request $request)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'caseType' => 'required|string|max:100',
            'country' => 'required',
            'fileCategoryMajor' => 'required|string|max:100',
            'fileCategoryMinor' => 'required|string|max:100',
            'fileName' => 'required|string|max:200',
            'fileNameEn' => 'nullable|string|max:200',
            'fileCode' => 'required|string|max:50',
            'internalCode' => 'nullable|string|max:50',
            'fileDescription' => 'nullable|string',
            'authorizedClient' => 'nullable|integer',
            'authorizedRole' => 'nullable',
            'isValid' => 'required|in:0,1'
        ], [
            'caseType.required' => '项目类型不能为空',
            'country.required' => '国家（地区）不能为空',
            'fileCategoryMajor.required' => '文件大类不能为空',
            'fileCategoryMinor.required' => '文件小类不能为空',
            'fileName.required' => '文件名称不能为空',
            'fileCode.required' => '文件编号不能为空',
            'isValid.required' => '是否有效不能为空'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备创建数据
        $data = [
            'case_type' => $request->caseType,
            'country' => $request->country,
            'file_category_major' => $request->fileCategoryMajor,
            'file_category_minor' => $request->fileCategoryMinor,
            'file_name' => $request->fileName,
            'file_name_en' => $request->fileNameEn,
            'file_code' => $request->fileCode,
            'internal_code' => $request->internalCode,
            'sort_order' => $request->sortOrder ?? 1,
            'file_description' => $request->fileDescription,
            'authorized_client' => $request->authorizedClient,
            'authorized_role' => $request->authorizedRole,
            'is_valid' => $request->isValid,
            'created_by' => auth()->user()->id ?? 1,
            'updated_by' => auth()->user()->id ?? 1
        ];

        // 创建文件描述记录
        $item = FileDescriptions::create($data);

        // 返回成功响应
        return json_success('创建成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "创建文件描述失败: {$e->getMessage()}",
            [
                'title' => '文件描述创建',
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
 * 功能描述：更新指定ID的文件描述记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - caseType (string): 项目类型，必填，最大100字符
 *   - country (string): 国家（地区），必填
 *   - fileCategoryMajor (string): 文件大类，必填，最大100字符
 *   - fileCategoryMinor (string): 文件小类，必填，最大100字符
 *   - fileName (string): 文件名称，必填，最大200字符
 *   - fileNameEn (string, optional): 文件英文名称，最大200字符
 *   - fileCode (string): 文件编号，必填，最大50字符
 *   - internalCode (string, optional): 内部编号，最大50字符
 *   - sortOrder (int, optional): 排序，默认为1
 *   - fileDescription (string, optional): 文件描述
 *   - authorizedClient (int, optional): 授权客户端
 *   - authorizedRole (string, optional): 授权角色
 *   - isValid (int): 是否有效，必填，值为0或1
 * - id (int): 文件描述ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新后的文件描述对象
 */
public function update(Request $request, $id)
{
    try {
        // 根据ID查找文件描述记录
        $item = FileDescriptions::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'caseType' => 'required|string|max:100',
            'country' => 'required',
            'fileCategoryMajor' => 'required|string|max:100',
            'fileCategoryMinor' => 'required|string|max:100',
            'fileName' => 'required|string|max:200',
            'fileNameEn' => 'nullable|string|max:200',
            'fileCode' => 'required|string|max:50',
            'internalCode' => 'nullable|string|max:50',
            'fileDescription' => 'nullable|string',
            'authorizedClient' => 'nullable|integer',
            'authorizedRole' => 'nullable',
            'isValid' => 'required|in:0,1'
        ], [
            'caseType.required' => '项目类型不能为空',
            'country.required' => '国家（地区）不能为空',
            'fileCategoryMajor.required' => '文件大类不能为空',
            'fileCategoryMinor.required' => '文件小类不能为空',
            'fileName.required' => '文件名称不能为空',
            'fileCode.required' => '文件编号不能为空',
            'isValid.required' => '是否有效不能为空'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 准备更新数据
        $data = [
            'case_type' => $request->caseType,
            'country' => $request->country,
            'file_category_major' => $request->fileCategoryMajor,
            'file_category_minor' => $request->fileCategoryMinor,
            'file_name' => $request->fileName,
            'file_name_en' => $request->fileNameEn,
            'file_code' => $request->fileCode,
            'internal_code' => $request->internalCode,
            'sort_order' => $request->sortOrder ?? 1,
            'file_description' => $request->fileDescription,
            'authorized_client' => $request->authorizedClient,
            'authorized_role' => $request->authorizedRole,
            'is_valid' => $request->isValid,
            'updated_by' => auth()->user()->id ?? 1
        ];

        // 更新文件描述记录
        $item->update($data);

        // 返回成功响应
        return json_success('更新成功', $item->toArray());

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "更新文件描述失败: {$e->getMessage()}",
            [
                'title' => '文件描述更新',
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
 * 功能描述：删除指定ID的文件描述记录
 *
 * 传入参数：
 * - id (int): 文件描述ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找文件描述记录
        $item = FileDescriptions::find($id);

        // 如果记录不存在，返回失败响应
        if (!$item) {
            return json_fail('记录不存在');
        }

        // 执行删除操作
        $item->delete();

        // 返回成功响应
        return json_success('删除成功');

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "删除文件描述失败: {$e->getMessage()}",
            [
                'title' => '文件描述删除',
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
 * 功能描述：获取所有有效的文件描述选项，用于下拉选择框
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 选项数据列表
 *   - id (int): 文件描述ID
 *   - label (string): 显示标签（文件名称）
 *   - value (string): 选项值（文件名称）
 */
public function options(Request $request)
{
    try {
        // 查询所有有效的文件描述，按排序字段排序，并选择指定字段
        $data = FileDescriptions::enabled()->ordered()
            ->select('id', 'file_name as label', 'file_name as value')
            ->get();

        // 返回成功响应
        return json_success('获取选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件描述选项失败: {$e->getMessage()}",
            [
                'title' => '文件描述选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取选项列表失败');
    }
}

/**
 * 批量更新状态 batchUpdateStatus
 *
 * 功能描述：批量更新文件描述记录的有效状态
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - ids (array): 文件描述ID数组，必填
 *   - ids.* (int): 文件描述ID，必须存在
 *   - isValid (boolean): 是否有效状态，必填
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 更新结果
 *   - updated_count (int): 更新记录数
 */
public function batchUpdateStatus(Request $request)
{
    try {
        // 验证输入参数
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:file_descriptions,id',
            'isValid' => 'required|boolean'
        ], [
            'ids.required' => '请选择要更新的记录',
            'ids.array' => 'IDs必须是数组格式',
            'ids.*.integer' => 'ID必须是整数',
            'ids.*.exists' => '选择的记录不存在',
            'isValid.required' => '请指定状态',
            'isValid.boolean' => '状态值格式错误'
        ]);

        // 如果验证失败，返回第一个错误信息
        if ($validator->fails()) {
            return json_fail($validator->errors()->first());
        }

        // 批量更新文件描述状态
        $updated = FileDescriptions::whereIn('id', $request->ids)
            ->update([
                'is_valid' => $request->isValid,
                'updated_by' => auth()->user()->name ?? '系统管理员'
            ]);

        // 返回成功响应
        return json_success('批量更新成功', ['updated_count' => $updated]);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "批量更新文件描述状态失败: {$e->getMessage()}",
            [
                'title' => '文件描述状态批量更新',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('批量更新失败');
    }
}


   /**
 * 获取文件分类树（用于左侧树形筛选） getTree
 *
 * 功能描述：获取所有有效的文件描述数据，构建树形结构用于左侧筛选
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 树形结构数据
 *   - id (int): 节点ID
 *   - label (string): 节点标签
 *   - icon (string): 节点图标
 *   - type (string): 节点类型
 *   - value (string): 节点值
 *   - children (array): 子节点数组
 */
public function getTree(Request $request)
{
    try {
        // 获取所有有效的文件描述数据，按分类分组
        $data = FileDescriptions::enabled()
            ->select('case_type', 'file_category_major', 'file_category_minor', 'country')
            ->distinct()
            ->orderBy('case_type')
            ->orderBy('file_category_major')
            ->orderBy('file_category_minor')
            ->get();

        // 构建树形结构
        $tree = [];
        $caseTypeMap = [];

        foreach ($data as $item) {
            // 项目类型级别
            if (!isset($caseTypeMap[$item->case_type])) {
                $caseTypeId = count($tree) + 1;
                $caseTypeMap[$item->case_type] = $caseTypeId;
                $tree[] = [
                    'id' => $caseTypeId,
                    'label' => $item->case_type,
                    'icon' => 'el-icon-folder',
                    'type' => 'case_type',
                    'value' => $item->case_type,
                    'children' => []
                ];
            }

            $caseTypeIndex = array_search($caseTypeMap[$item->case_type], array_column($tree, 'id'));

            // 文件大类级别
            $majorKey = $item->case_type . '_' . $item->file_category_major;
            $majorExists = false;
            foreach ($tree[$caseTypeIndex]['children'] as $majorIndex => $major) {
                if ($major['value'] === $item->file_category_major && $major['parent_case_type'] === $item->case_type) {
                    $majorExists = true;
                    break;
                }
            }

            if (!$majorExists) {
                $majorId = ($caseTypeMap[$item->case_type] * 100) + count($tree[$caseTypeIndex]['children']) + 1;
                $tree[$caseTypeIndex]['children'][] = [
                    'id' => $majorId,
                    'label' => $item->file_category_major,
                    'icon' => 'el-icon-document',
                    'type' => 'file_category_major',
                    'value' => $item->file_category_major,
                    'parent_case_type' => $item->case_type,
                    'children' => []
                ];
                $majorIndex = count($tree[$caseTypeIndex]['children']) - 1;
            }

            // 文件小类级别
            $minorExists = false;
            foreach ($tree[$caseTypeIndex]['children'][$majorIndex]['children'] as $minor) {
                if ($minor['value'] === $item->file_category_minor) {
                    $minorExists = true;
                    break;
                }
            }

            if (!$minorExists && $item->file_category_minor) {
                $minorId = ($majorId * 100) + count($tree[$caseTypeIndex]['children'][$majorIndex]['children']) + 1;
                $tree[$caseTypeIndex]['children'][$majorIndex]['children'][] = [
                    'id' => $minorId,
                    'label' => $item->file_category_minor,
                    'icon' => 'el-icon-document',
                    'type' => 'file_category_minor',
                    'value' => $item->file_category_minor,
                    'parent_case_type' => $item->case_type,
                    'parent_major' => $item->file_category_major
                ];
            }
        }

        // 返回成功响应
        return json_success('获取文件分类树成功', $tree);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件分类树失败: {$e->getMessage()}",
            [
                'title' => '文件分类树',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取文件分类树失败');
    }
}

/**
 * 获取文件大类选项（基于项目类型） getFileCategoryMajor
 *
 * 功能描述：根据项目类型获取文件大类选项
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - caseType (string, optional): 项目类型筛选条件
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 文件大类选项列表
 *   - value (string): 文件大类值
 *   - label (string): 文件大类标签
 */
public function getFileCategoryMajor(Request $request)
{
    try {
        // 初始化查询构建器
        $query = FileDescriptions::query()
            ->select('file_category_major')
            ->distinct();

        // 根据项目类型筛选
        if ($request->has('caseType') && !empty($request->caseType)) {
            $query->where('case_type', $request->caseType);
        }

        // 查询数据并格式化
        $data = $query->orderBy('file_category_major')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->file_category_major,
                    'label' => $item->file_category_major
                ];
            });

        // 返回成功响应
        return json_success('获取文件大类选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件大类选项失败: {$e->getMessage()}",
            [
                'title' => '文件大类选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取文件大类选项失败');
    }
}


   /**
 * 获取文件小类选项（基于项目类型和文件大类） getFileCategoryMinor
 *
 * 功能描述：根据文件大类获取文件小类选项
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - fileCategoryMajor (string, optional): 文件大类筛选条件
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 文件小类选项列表
 *   - value (string): 文件小类值
 *   - label (string): 文件小类标签
 */
public function getFileCategoryMinor(Request $request)
{
    try {
        // 初始化查询构建器，从FileCategories模型查询
        $query = FileCategories::query()
            ->select('sub_category')
            ->distinct();

        // 根据文件大类筛选
        if ($request->has('fileCategoryMajor') && !empty($request->fileCategoryMajor)) {
            $query->where('main_category', $request->fileCategoryMajor);
        }

        // 查询非空的子分类，按子分类排序
        $data = $query->whereNotNull('sub_category')
            ->where('sub_category', '!=', '')
            ->orderBy('sub_category')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->sub_category,
                    'label' => $item->sub_category
                ];
            });

        // 返回成功响应
        return json_success('获取文件小类选项成功', $data);

    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(
            8,
            "获取文件小类选项失败: {$e->getMessage()}",
            [
                'title' => '文件小类选项',
                'error' => $e->getMessage(),
                'status' => \App\Models\Logs::STATUS_FAILED
            ]
        );
        return json_fail('获取文件小类选项失败');
    }
}

}
