<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerFile;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CustomerFileController extends Controller
{
    /**
     * 获取文件列表
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
     * 上传文件
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
            
            // 生成唯一文件名
            $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            
            // 存储文件
            $filePath = $uploadedFile->storeAs('customer_files/' . $request->customer_id, $fileName, 'public');

            $userId = auth()->id();
            $data = [
                'customer_id' => $request->customer_id,
                'file_name' => $fileName,
                'file_original_name' => $uploadedFile->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $uploadedFile->getClientOriginalExtension(),
                'file_category' => $request->file_category,
                'file_size' => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getMimeType(),
                'file_description' => $request->file_description,
                'is_private' => $request->get('is_private', false),
                'uploaded_by' => $userId,
                'created_by' => $userId,
                'updated_by' => $userId,
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

            // 处理权限设置
            if ($request->has('permission')) {
                $permission = $request->input('permission');
                switch ($permission) {
                    case '公开':
                        $data['permission_type'] = 'public';
                        $data['is_private'] = false;
                        $data['allowed_departments'] = null;
                        $data['allowed_users'] = null;
                        break;
                    case '部门':
                        $data['permission_type'] = 'department';
                        $data['is_private'] = false;
                        $data['allowed_departments'] = $request->input('departments', []);
                        $data['allowed_users'] = null;
                        break;
                    case '私有':
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
