<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerFile;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * 客户文件管理控制器
 * 
 * 负责处理客户文件的上传、下载、查看、更新和删除等操作
 * 支持文件权限管理（公开、部门、私有）和文件分类管理
 * 
 * @package App\Http\Controllers\Api
 * @author EMA System
 * @version 1.0
 */
class CustomerFileController extends Controller
{
    /**
     * 获取客户文件列表
     * 
     * 支持多种搜索条件和分页功能，返回格式化的文件信息
     * 包含文件基本信息、权限信息、关联客户信息等
     * 
     * @param Request $request HTTP请求对象
     * 
     * 请求参数：
     * - customer_id (int, optional): 客户ID，精确匹配
     * - customer_name (string, optional): 客户名称，模糊搜索
     * - file_name (string, optional): 文件名，模糊搜索
     * - file_original_name (string, optional): 原始文件名，模糊搜索
     * - file_type (string, optional): 文件类型（扩展名），精确匹配
     * - file_category (string, optional): 文件分类，精确匹配
     * - is_private (boolean, optional): 是否私有文件
     * - uploaded_by (int, optional): 上传者用户ID
     * - mime_type (string, optional): MIME类型，模糊搜索
     * - file_size_min (int, optional): 最小文件大小（字节）
     * - file_size_max (int, optional): 最大文件大小（字节）
     * - page_size (int, optional): 每页数量，默认10
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     "list": [文件列表],
     *     "total": 总数量,
     *     "per_page": 每页数量,
     *     "current_page": 当前页码,
     *     "last_page": 最后页码
     *   }
     * }
     * 
     * 文件对象包含字段：
     * - id: 文件ID
     * - customer_id/customer_name: 客户信息
     * - file_name/file_original_name: 文件名信息
     * - file_path/file_type/file_category: 文件基本信息
     * - file_size/file_size_formatted: 文件大小信息
     * - mime_type: MIME类型
     * - file_description: 文件描述
     * - is_private/permission_type/permission: 权限信息
     * - allowed_departments/allowed_users: 权限范围
     * - uploaded_by/uploader: 上传者信息
     * - download_url/can_download: 下载相关
     * - business_staff: 业务员信息
     * - create_date/create_time/created_at: 创建时间
     * - update_time/updated_at: 更新时间
     * - create_user/update_user: 创建/更新用户
     * - remark: 备注
     * 
     * @throws \Exception 当数据库查询或数据处理出错时抛出异常
     */
    public function index(Request $request)
    {
        try {
            $query = CustomerFile::with(['customer', 'uploader', 'creator', 'updater']);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('file_name')) {
                $query->where('file_name', 'like', '%' . $request->file_name . '%');
            }

            if ($request->filled('file_original_name')) {
                $query->where('file_original_name', 'like', '%' . $request->file_original_name . '%');
            }

            if ($request->filled('file_type')) {
                $query->where('file_type', $request->file_type);
            }

            if ($request->filled('file_category')) {
                $query->where('file_category', $request->file_category);
            }

            if ($request->filled('is_private')) {
                $query->where('is_private', $request->is_private);
            }

            if ($request->filled('uploaded_by')) {
                $query->where('uploaded_by', $request->uploaded_by);
            }

            if ($request->filled('mime_type')) {
                $query->where('mime_type', 'like', '%' . $request->mime_type . '%');
            }

            // 文件大小筛选
            if ($request->filled('file_size_min') && $request->filled('file_size_max')) {
                $query->whereBetween('file_size', [$request->file_size_min, $request->file_size_max]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $files = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据
            $files->getCollection()->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'customer_id' => $file->customer_id,
                    'customer_name' => $file->customer->customer_name ?? '',
                    'customerName' => $file->customer->customer_name ?? '',
                    'file_name' => $file->file_name,
                    'fileName' => $file->file_name,
                    'file_original_name' => $file->file_original_name,
                    'fileOriginalName' => $file->file_original_name,
                    'file_path' => $file->file_path,
                    'filePath' => $file->file_path,
                    'file_type' => $file->file_type,
                    'fileType' => $file->file_type,
                    'file_category' => $file->file_category,
                    'fileCategory' => $file->file_category,
                    'file_size' => $file->file_size,
                    'fileSize' => $file->file_size,
                    'file_size_formatted' => $file->getFileSizeFormatted(),
                    'fileSizeFormatted' => $file->getFileSizeFormatted(),
                    'mime_type' => $file->mime_type,
                    'mimeType' => $file->mime_type,
                    'file_description' => $file->file_description,
                    'fileDescription' => $file->file_description,
                    'is_private' => $file->is_private,
                    'isPrivate' => $file->is_private,
                    'permission_type' => $file->permission_type ?? 'public',
                    'permissionType' => $file->permission_type ?? 'public',
                    'permission' => $this->getPermissionLabel($file),
                    'allowed_departments' => $file->allowed_departments,
                    'allowedDepartments' => $file->allowed_departments,
                    'allowed_users' => $file->allowed_users,
                    'allowedUsers' => $file->allowed_users,
                    'uploaded_by' => $file->uploaded_by,
                    'uploadedBy' => $file->uploaded_by,
                    'uploader' => $file->uploader->name ?? '',
                    'download_url' => $file->getDownloadUrl(),
                    'downloadUrl' => $file->getDownloadUrl(),
                    'can_download' => $file->canDownload(),
                    'canDownload' => $file->canDownload(),
                    'business_staff' => $file->customer->businessPerson->name ?? '',
                    'businessStaff' => $file->customer->businessPerson->name ?? '',
                    'create_date' => $file->created_at ? $file->created_at->format('Y-m-d') : '',
                    'createDate' => $file->created_at ? $file->created_at->format('Y-m-d') : '',
                    'remark' => $file->remark,
                    'create_user' => $file->creator->real_name ?? '',
                    'createUser' => $file->creator->real_name ?? '',
                    'created_at' => $file->created_at ? $file->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $file->created_at ? $file->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $file->created_at ? $file->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $file->updater->real_name ?? '',
                    'updateUser' => $file->updater->real_name ?? '',
                    'updated_at' => $file->updated_at ? $file->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $file->updated_at ? $file->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $file->updated_at ? $file->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $files->items(),
                    'total' => $files->total(),
                    'per_page' => $files->perPage(),
                    'current_page' => $files->currentPage(),
                    'last_page' => $files->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传客户文件
     * 
     * 处理文件上传，包括用户认证验证、文件格式验证、大小限制等
     * 支持多种文件类型，自动生成唯一文件名并存储到指定目录
     * 
     * @param Request $request HTTP请求对象
     * 
     * 请求参数：
     * - customer_id (int, required): 客户ID，必须存在于customers表中
     * - file (file, required): 上传的文件
     *   - 最大大小：20MB (20480KB)
     *   - 支持格式：jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z
     * - file_category (string, optional): 文件分类，最大100字符
     * - file_description (string, optional): 文件描述
     * - is_private (boolean, optional): 是否私有文件，默认false
     *   - 前端可能发送字符串'0'/'1'或'true'/'false'，会自动转换为布尔值
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "上传成功",
     *   "data": {文件对象}
     * }
     * 
     * 失败响应格式：
     * {
     *   "success": false,
     *   "message": "错误信息",
     *   "errors": {验证错误详情}
     * }
     * 
     * 文件存储规则：
     * - 存储路径：storage/app/public/customer_files/{customer_id}/
     * - 文件命名：{timestamp}_{uniqid}.{extension}
     * - 自动记录上传者、创建者、更新者信息
     * 
     * 验证规则：
     * - 用户必须已认证登录
     * - 客户ID必须有效
     * - 文件必须符合大小和格式限制
     * - 文件上传必须成功
     * 
     * @throws \Exception 当文件上传、存储或数据库操作失败时抛出异常
     */
    public function upload(Request $request)
    {
        try {
            // 检查用户认证
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未认证，请先登录',
                    'errors' => ['auth' => ['User not authenticated']]
                ], 401);
            }

            // 数据预处理
            $data = $request->all();

            // 处理is_private字段（前端可能发送字符串'0'或'1'）
            // 兼容多种前端传值方式：字符串'1'/'0'、布尔值true/false、字符串'true'/'false'
            if (isset($data['is_private'])) {
                $data['is_private'] = $data['is_private'] === '1' || $data['is_private'] === 'true' || $data['is_private'] === true;
            }

            $validator = Validator::make($data, [
                'customer_id' => 'required|integer|exists:customers,id',
                'file' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z', // 最大20M
                'file_category' => 'nullable|string|max:100',
                'file_description' => 'nullable|string',
                'is_private' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '文件上传验证失败：' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedFile = $request->file('file');
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => '文件上传验证失败：The file failed to upload.',
                    'errors' => ['file' => ['The file failed to upload.']]
                ], 422);
            }
            
            // 生成唯一文件名：时间戳 + 唯一ID + 原始扩展名
            // 格式：{timestamp}_{uniqid}.{extension}，确保文件名唯一性
            $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            
            // 存储文件到指定目录：storage/app/public/customer_files/{customer_id}/
            $filePath = $uploadedFile->storeAs('customer_files/' . $request->customer_id, $fileName, 'public');

            $userId = auth()->id();
            // 构建文件记录数据，包含所有必要的文件信息和用户关联
            $data = [
                'customer_id' => $request->customer_id,
                'file_name' => $fileName,                                    // 系统生成的唯一文件名
                'file_original_name' => $uploadedFile->getClientOriginalName(), // 用户上传时的原始文件名
                'file_path' => $filePath,                                   // 文件存储路径
                'file_type' => $uploadedFile->getClientOriginalExtension(), // 文件扩展名
                'file_category' => $request->file_category,                 // 文件分类
                'file_size' => $uploadedFile->getSize(),                    // 文件大小（字节）
                'mime_type' => $uploadedFile->getMimeType(),                // MIME类型
                'file_description' => $request->file_description,           // 文件描述
                'is_private' => $request->get('is_private', false),         // 是否私有，默认false
                'uploaded_by' => $userId,                                   // 上传者ID
                'created_by' => $userId,                                    // 创建者ID
                'updated_by' => $userId,                                    // 更新者ID
            ];

            $file = CustomerFile::create($data);

            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => $file
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取文件详情
     * 
     * 根据文件ID获取单个文件的详细信息
     * 包含文件基本信息和关联的客户、上传者等信息
     * 
     * @param int $id 文件ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": {
     *     文件完整信息对象，包含：
     *     - 文件基本信息（id, file_name, file_path等）
     *     - 关联客户信息（customer对象）
     *     - 上传者信息（uploader对象）
     *     - 创建者和更新者信息（creator, updater对象）
     *   }
     * }
     * 
     * 失败响应格式：
     * {
     *   "success": false,
     *   "message": "获取失败：错误信息"
     * }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当文件不存在时抛出异常
     * @throws \Exception 当数据库查询出错时抛出异常
     */
    public function show($id)
    {
        try {
            $file = CustomerFile::with(['customer', 'uploader', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $file
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新文件信息
     * 
     * 更新文件的元数据信息，包括分类、描述、权限设置等
     * 支持三种权限模式：公开、部门、私有
     * 
     * @param Request $request HTTP请求对象
     * @param int $id 文件ID
     * 
     * 请求参数：
     * - file_category (string, optional): 文件分类，最大100字符
     * - file_description (string, optional): 文件描述
     * - is_private (boolean, optional): 是否私有文件
     * - permission (string, optional): 权限类型，可选值：'公开', '部门', '私有'
     * - departments (array, optional): 当权限为'部门'时，允许访问的部门ID数组
     * - users (array, optional): 当权限为'私有'时，允许访问的用户ID数组
     * - remark (string, optional): 备注信息
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 权限设置规则：
     * - 公开：permission_type='public', is_private=false, 清空部门和用户限制
     * - 部门：permission_type='department', is_private=false, 设置allowed_departments
     * - 私有：permission_type='private', is_private=true, 设置allowed_users
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "更新成功",
     *   "data": {更新后的文件对象}
     * }
     * 
     * 失败响应格式：
     * {
     *   "success": false,
     *   "message": "错误信息",
     *   "errors": {验证错误详情}
     * }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当文件不存在时抛出异常
     * @throws \Exception 当数据库更新操作失败时抛出异常
     */
    public function update(Request $request, $id)
    {
        try {
            $file = CustomerFile::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'file_category' => 'nullable|string|max:100',
                'file_description' => 'nullable|string',
                'is_private' => 'nullable|boolean',
                'permission' => 'nullable|string|in:公开,部门,私有',
                'departments' => 'nullable|array',
                'users' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['file_category', 'file_description', 'is_private', 'remark']);

            // 处理权限设置 - 根据前端传入的权限类型设置相应的数据库字段
            if ($request->has('permission')) {
                $permission = $request->input('permission');
                switch ($permission) {
                    case '公开':
                        // 公开权限：所有用户可访问，清空部门和用户限制
                        $data['permission_type'] = 'public';
                        $data['is_private'] = false;
                        $data['allowed_departments'] = null;
                        $data['allowed_users'] = null;
                        break;
                    case '部门':
                        // 部门权限：指定部门用户可访问，清空用户限制
                        $data['permission_type'] = 'department';
                        $data['is_private'] = false;
                        $data['allowed_departments'] = $request->input('departments', []);
                        $data['allowed_users'] = null;
                        break;
                    case '私有':
                        // 私有权限：仅指定用户可访问，清空部门限制
                        $data['permission_type'] = 'private';
                        $data['is_private'] = true;
                        $data['allowed_departments'] = null;
                        $data['allowed_users'] = $request->input('users', []);
                        break;
                }
            }

            $data['updated_by'] = auth()->id();

            $file->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $file
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下载文件
     * 
     * 提供文件下载功能，包含权限验证和文件存在性检查
     * 下载时使用原始文件名，确保用户体验
     * 
     * @param int $id 文件ID
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     *         成功时返回文件下载响应，失败时返回JSON错误响应
     * 
     * 下载流程：
     * 1. 根据ID查找文件记录
     * 2. 检查当前用户是否有下载权限（调用canDownload方法）
     * 3. 验证物理文件是否存在于存储系统中
     * 4. 返回文件下载响应，使用原始文件名
     * 
     * 权限验证：
     * - 公开文件：所有用户可下载
     * - 部门文件：同部门用户可下载
     * - 私有文件：指定用户或文件上传者可下载
     * 
     * 错误响应格式：
     * {
     *   "success": false,
     *   "message": "错误信息"
     * }
     * 
     * 可能的错误：
     * - 403: 没有权限下载此文件
     * - 404: 文件不存在（数据库记录不存在或物理文件丢失）
     * - 500: 服务器内部错误
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当文件记录不存在时抛出异常
     * @throws \Exception 当文件下载过程中出现错误时抛出异常
     */
    public function download($id)
    {
        try {
            $file = CustomerFile::findOrFail($id);

            // 检查文件权限
            if (!$file->canDownload()) {
                return response()->json([
                    'success' => false,
                    'message' => '没有权限下载此文件'
                ], 403);
            }

            // 检查文件是否存在
            if (!Storage::disk('public')->exists($file->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在'
                ], 404);
            }

            return Storage::disk('public')->download($file->file_path, $file->file_original_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '下载失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除文件
     * 
     * 删除文件记录和对应的物理文件
     * 采用安全删除策略，即使物理文件不存在也会删除数据库记录
     * 
     * @param int $id 文件ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 删除流程：
     * 1. 根据ID查找文件记录
     * 2. 检查物理文件是否存在，存在则删除
     * 3. 删除数据库中的文件记录
     * 
     * 安全策略：
     * - 即使物理文件已经不存在，仍会删除数据库记录
     * - 避免因文件丢失导致的数据不一致问题
     * 
     * 成功响应格式：
     * {
     *   "success": true,
     *   "message": "删除成功"
     * }
     * 
     * 失败响应格式：
     * {
     *   "success": false,
     *   "message": "删除失败：错误信息"
     * }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当文件记录不存在时抛出异常
     * @throws \Exception 当删除操作失败时抛出异常
     */
    public function destroy($id)
    {
        try {
            $file = CustomerFile::findOrFail($id);
            
            // 删除物理文件
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            
            // 删除数据库记录
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => '删除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取文件分类列表
     * 
     * 返回系统预定义的文件分类选项
     * 用于前端下拉选择和文件分类管理
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     * 
     * 响应格式：
     * {
     *   "success": true,
     *   "message": "获取成功",
     *   "data": [
     *     {
     *       "value": "分类常量值",
     *       "label": "分类显示名称"
     *     }
     *   ]
     * }
     * 
     * 预定义分类：
     * - CATEGORY_CONTRACT: 合同文件
     * - CATEGORY_CERTIFICATE: 证件资料
     * - CATEGORY_TECH_DOC: 技术资料
     * - CATEGORY_BUSINESS_DOC: 商务资料
     * - CATEGORY_FINANCE_DOC: 财务资料
     * - CATEGORY_OTHER: 其他文件
     * 
     * 注意：分类常量定义在CustomerFile模型中
     */
    public function getFileCategories()
    {
        $categories = [
            ['value' => CustomerFile::CATEGORY_CONTRACT, 'label' => '合同文件'],
            ['value' => CustomerFile::CATEGORY_CERTIFICATE, 'label' => '证件资料'],
            ['value' => CustomerFile::CATEGORY_TECH_DOC, 'label' => '技术资料'],
            ['value' => CustomerFile::CATEGORY_BUSINESS_DOC, 'label' => '商务资料'],
            ['value' => CustomerFile::CATEGORY_FINANCE_DOC, 'label' => '财务资料'],
            ['value' => CustomerFile::CATEGORY_OTHER, 'label' => '其他文件'],
        ];

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $categories
        ]);
    }

    /**
     * 获取权限标签
     * 
     * 根据文件的权限设置返回对应的中文标签
     * 支持新的permission_type字段和旧的is_private字段兼容
     * 
     * @param CustomerFile $file 文件对象
     * 
     * @return string 权限标签
     * 
     * 权限类型映射：
     * - 'public' -> '公开'
     * - 'department' -> '部门'
     * - 'private' -> '私有'
     * - 兼容模式：根据is_private字段判断（true='私有', false='公开'）
     * 
     * 兼容性说明：
     * 当permission_type字段为空时，会回退到使用is_private字段
     * 确保与旧版本数据的兼容性
     */
    private function getPermissionLabel($file)
    {
        switch ($file->permission_type) {
            case 'public':
                return '公开';
            case 'department':
                return '部门';
            case 'private':
                return '私有';
            default:
                // 兼容旧的 is_private 字段
                return $file->is_private ? '私有' : '公开';
        }
    }
}
