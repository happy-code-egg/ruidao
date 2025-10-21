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
     * 获取列表
     */
    public function index(Request $request)
    {
        try {
            $query = FileDescriptions::query();

            // 项目类型搜索
            if ($request->has('caseType') && !empty($request->caseType)) {
                $query->where('case_type', $request->caseType);
            }

            // 文件名称搜索
            if ($request->has('fileName') && !empty($request->fileName)) {
                $query->where('file_name', 'like', '%' . $request->fileName . '%');
            }

            // 国家搜索（支持多选）
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

            // 文件编号搜索
            if ($request->has('fileCode') && !empty($request->fileCode)) {
                $query->where('file_code', 'like', '%' . $request->fileCode . '%');
            }

            // 文件大类搜索
            if ($request->has('fileCategoryMajor') && !empty($request->fileCategoryMajor)) {
                $query->where('file_category_major', $request->fileCategoryMajor);
            }

            // 文件小类搜索
            if ($request->has('fileCategoryMinor') && !empty($request->fileCategoryMinor)) {
                $query->where('file_category_minor', $request->fileCategoryMinor);
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
                'list' => $data->toArray(),
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
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
     * 获取详情
     */
    public function show($id)
    {
        try {
            $item = FileDescriptions::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            return json_success('获取详情成功', $item->toArray());

        } catch (\Exception $e) {
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
     * 创建
     */
    public function store(Request $request)
    {
        try {
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

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

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

            $item = FileDescriptions::create($data);

            return json_success('创建成功', $item->toArray());

        } catch (\Exception $e) {
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
     * 更新
     */
    public function update(Request $request, $id)
    {
        try {
            $item = FileDescriptions::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

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

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

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

            $item->update($data);

            return json_success('更新成功', $item->toArray());

        } catch (\Exception $e) {
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
     * 删除
     */
    public function destroy($id)
    {
        try {
            $item = FileDescriptions::find($id);

            if (!$item) {
                return json_fail('记录不存在');
            }

            $item->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
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
     * 获取选项列表（用于下拉框等）
     */
    public function options(Request $request)
    {
        try {
            $data = FileDescriptions::enabled()->ordered()
                ->select('id', 'file_name as label', 'file_name as value')
                ->get();

            return json_success('获取选项成功', $data);

        } catch (\Exception $e) {
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
     * 批量更新状态
     */
    public function batchUpdateStatus(Request $request)
    {
        try {
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

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $updated = FileDescriptions::whereIn('id', $request->ids)
                ->update([
                    'is_valid' => $request->isValid,
                    'updated_by' => auth()->user()->name ?? '系统管理员'
                ]);

            return json_success('批量更新成功', ['updated_count' => $updated]);

        } catch (\Exception $e) {
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
     * 获取文件分类树（用于左侧树形筛选）
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

            return json_success('获取文件分类树成功', $tree);

        } catch (\Exception $e) {
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
     * 获取文件大类选项（基于项目类型）
     */
    public function getFileCategoryMajor(Request $request)
    {
        try {
            $query = FileDescriptions::query()
                ->select('file_category_major')
                ->distinct();

            // 根据项目类型筛选
            if ($request->has('caseType') && !empty($request->caseType)) {
                $query->where('case_type', $request->caseType);
            }

            $data = $query->orderBy('file_category_major')
                ->get()
                ->map(function ($item) {
                    return [
                        'value' => $item->file_category_major,
                        'label' => $item->file_category_major
                    ];
                });

            return json_success('获取文件大类选项成功', $data);

        } catch (\Exception $e) {
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
     * 获取文件小类选项（基于项目类型和文件大类）
     */
    public function getFileCategoryMinor(Request $request)
    {
        try {
            $query = FileCategories::query()
                ->select('sub_category')
                ->distinct();

            // // 根据项目类型筛选
            // if ($request->has('caseType') && !empty($request->caseType)) {
            //     $query->where('case_type', $request->caseType);
            // }

            // 根据文件大类筛选
            if ($request->has('fileCategoryMajor') && !empty($request->fileCategoryMajor)) {
                $query->where('main_category', $request->fileCategoryMajor);
            }


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

            return json_success('获取文件小类选项成功', $data);

        } catch (\Exception $e) {
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