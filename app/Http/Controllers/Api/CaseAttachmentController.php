<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseAttachment;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CaseAttachmentController extends Controller
{
    /**
    获取项目附件列表
    功能说明：
    根据项目 ID（caseId）查询该项目关联的所有附件，按附件 ID 倒序返回完整数据
    无异常处理逻辑，直接返回查询结果，适配简单附件列表展示场景
    请求参数：
    caseId（项目 ID）：必填，整数，通过 URL 路径传入，指定附件所属的项目唯一标识
    核心逻辑：
    条件筛选：通过 case_id 字段精准匹配指定项目的附件记录
    排序规则：按附件 ID（id）降序排列，最新创建的附件排在前面
    数据返回：查询所有匹配的附件记录，直接以 JSON 格式返回
    返回参数：
    success：布尔值，固定为 true（无失败分支处理）
    data：数组，项目附件列表，每个元素为 CaseAttachment 模型的完整字段（如 id、case_id、file_name、file_path 等）
    依赖说明：
    依赖 CaseAttachment 模型，需确保模型与附件表（case_attachments）字段匹配
    关联字段为 case_id，需与项目表的主键 ID 保持关联一致性
    @param int $caseId 项目 ID
    @return \Illuminate\Http\JsonResponse 项目附件列表响应
     */
    public function index($caseId)
    {
        $attachments = CaseAttachment::where('case_id', $caseId)->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $attachments]);
    }

    /**
    上传项目附件
    请求参数：
    路径参数：caseId（项目 ID）：必填，整数，项目的唯一标识 ID，用于关联上传的附件
    请求体参数：
    file（附件文件）：必填，文件，最大 20MB，需上传的项目附件文件
    file_type（文件类型）：必填，字符串，最大长度 100 字符，附件的一级分类类型（如 "合同"、"资料" 等）
    file_sub_type（文件子类型）：必填，字符串，最大长度 100 字符，附件的二级分类类型（如 "采购合同"、"身份证明" 等）
    file_desc（文件描述）：必填，字符串，最大长度 500 字符，对附件内容的说明描述
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，上传结果的描述信息（如 "上传成功"、"用户未认证" 等）
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    data（附件信息）：对象，上传成功时返回新创建的附件记录详情，包含以下字段：
    id（主键 ID）：整数，附件记录的自增主键
    case_id（项目 ID）：整数，关联的项目 ID（与路径参数 caseId 一致）
    file_type（文件类型）：字符串，附件的一级分类类型
    file_sub_type（文件子类型）：字符串，附件的二级分类类型
    file_desc（文件描述）：字符串，附件的描述信息
    file_name（文件原名）：字符串，上传文件的原始名称
    file_path（文件路径）：字符串，文件存储的相对路径（基于 public 磁盘）
    file_size（文件大小）：整数，文件的大小（单位：字节）
    时间字段：如 created_at（创建时间）等（根据 CaseAttachment 模型实际字段而定）
    错误状态码：
    401：用户未认证（未登录）
    422：请求参数验证失败
    500：服务器内部错误（如文件存储失败、数据库操作异常等）
    @param Request $request 请求对象，包含上传的文件及相关参数
    @param int $caseId 项目 ID，用于关联附件
    @return \Illuminate\Http\JsonResponse JSON 响应，包含附件上传结果信息
     */
    public function upload(Request $request, $caseId)
    {
        try {
            // 验证用户认证 - 检查用户是否已登录
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未认证，请先登录'
                ], 401);
            }

            // 验证项目是否存在 - 确保关联的项目记录存在
            $case = Cases::findOrFail($caseId);

            // 验证请求参数 - 对上传文件和相关字段进行验证
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:20480', // 最大20MB
                'file_type' => 'required|string|max:100',
                'file_sub_type' => 'required|string|max:100',
                'file_desc' => 'required|string|max:500',
            ]);

            // 如果验证失败，返回错误信息
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 处理文件上传 - 获取上传的文件对象
            $file = $request->file('file');

            // 生成唯一的文件名 - 使用时间戳+原文件名避免重复
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

            // 检查存储目录是否存在，不存在则创建
            $directory = 'cases/' . $caseId;
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // 存储文件到指定目录 - 按项目ID分目录存储，使用public磁盘
            $filePath = $file->storeAs($directory, $fileName, 'public');

            // 验证文件是否成功存储
            if (!Storage::disk('public')->exists($filePath)) {
                throw new \Exception('文件存储失败，请检查存储权限和磁盘空间');
            }

            // 获取文件的可访问URL
            $fileUrl = Storage::disk('public')->url($filePath);

            // 创建附件记录 - 将文件信息保存到数据库
            $attachment = CaseAttachment::create([
                'case_id' => $caseId,                    // 关联的项目ID
                'file_type' => $request->file_type,      // 文件类型
                'file_sub_type' => $request->file_sub_type, // 文件子类型
                'file_desc' => $request->file_desc,      // 文件描述
                'file_name' => $file->getClientOriginalName(), // 原始文件名
                'file_path' => $filePath,                // 文件存储路径
                'file_size' => $file->getSize(),         // 文件大小（字节）
            ]);

            // 返回上传成功的响应 - 包含文件访问URL
            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => array_merge($attachment->toArray(), [
                    'file_url' => $fileUrl,
                    'storage_path' => storage_path('app/public/' . $filePath),
                    'public_path' => public_path('storage/' . $filePath)
                ])
            ]);

        } catch (\Exception $e) {
            // 异常处理 - 捕获并返回上传过程中的错误
            \Log::error('文件上传失败', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage(),
                'debug_info' => [
                    'storage_linked' => file_exists(public_path('storage')),
                    'storage_path' => storage_path('app/public'),
                    'public_path' => public_path('storage')
                ]
            ], 500);
        }
    }

    /**
    创建项目附件记录
    请求参数：
    路径参数：caseId（项目 ID）：必填，整数，项目的唯一标识 ID，用于关联新创建的附件记录
    请求体参数：
    file_type（文件类型）：可选，字符串，最大长度 100 字符，附件的一级分类类型（如 "合同"、"资料" 等）
    file_sub_type（文件子类型）：可选，字符串，最大长度 100 字符，附件的二级分类类型（如 "采购合同" 等）
    file_desc（文件描述）：可选，字符串，无长度限制，对附件内容的说明描述
    file_name（文件名称）：必填，字符串，最大长度 255 字符，附件的文件名称
    file_path（文件路径）：必填，字符串，最大长度 500 字符，附件的存储路径
    file_size（文件大小）：可选，整数，附件的大小（单位：字节）
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，验证失败时返回错误描述
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    data（附件记录）：对象，创建成功时返回新创建的附件记录详情，包含以下字段：
    id（主键 ID）：整数，附件记录的自增主键
    case_id（项目 ID）：整数，关联的项目 ID（与路径参数 caseId 一致）
    file_type（文件类型）：字符串，附件的一级分类类型（可能为 null）
    file_sub_type（文件子类型）：字符串，附件的二级分类类型（可能为 null）
    file_desc（文件描述）：字符串，附件的描述信息（可能为 null）
    file_name（文件名称）：字符串，附件的文件名称
    file_path（文件路径）：字符串，附件的存储路径
    file_size（文件大小）：整数，附件的大小（可能为 null）
    时间字段：如 created_at（创建时间）等（根据 CaseAttachment 模型实际字段而定）
    @param Request $request 请求对象，包含创建附件记录所需的参数
    @param int $caseId 项目 ID，用于关联附件记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含创建结果信息
     */
    public function store(Request $request, $caseId)
    {
        $data = $request->all();
        $data['case_id'] = $caseId;
        $validator = Validator::make($data, [
            'file_type' => 'nullable|string|max:100',
            'file_sub_type' => 'nullable|string|max:100',
            'file_desc' => 'nullable|string',
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string|max:500',
            'file_size' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '验证失败', 'errors' => $validator->errors()], 422);
        }
        $att = CaseAttachment::create($data);
        return response()->json(['success' => true, 'data' => $att]);
    }

    /**
    删除项目附件记录
     */
    public function destroy($id)
    {
        $att = CaseAttachment::findOrFail($id);
        $att->delete();
        return response()->json(['success' => true]);
    }

    /**
    下载项目附件
    功能说明：
    根据项目ID和附件ID下载对应的附件文件
    请求参数：
    caseId（项目ID）：必填，整数，通过URL路径传入，指定附件所属的项目
    attachmentId（附件ID）：必填，整数，通过URL路径传入，指定要下载的附件
    返回参数：
    成功：文件下载响应（二进制文件流）
    失败：JSON错误响应
    @param int $caseId 项目ID
    @param int $attachmentId 附件ID
    @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse 文件下载响应或错误JSON
     */
    public function download($caseId, $attachmentId)
    {
        try {
            // 验证项目是否存在
            $case = Cases::findOrFail($caseId);
            
            // 查找附件记录，确保附件属于指定项目
            $attachment = CaseAttachment::where('case_id', $caseId)
                                        ->where('id', $attachmentId)
                                        ->first();
            
            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => '附件不存在或不属于指定项目'
                ], 500);
            }
            
            // 构建完整的文件路径
            $filePath = storage_path('app/public/' . $attachment->file_path);
            
            // 检查文件是否存在
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在或已被删除'
                ], 500);
            }
            
            // 验证文件可读性
            if (!is_readable($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件无法读取，请检查文件权限'
                ], 500);
            }
            
            // 记录下载日志
            \Log::info('附件下载成功', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'file_name' => $attachment->file_name,
                'file_size' => $attachment->file_size,
                'file_path' => $attachment->file_path
            ]);
            
            // 使用更直接的方式提供文件下载
            // 清除所有已有的输出缓冲
            ob_clean();
            
            // 获取文件扩展名并设置正确的Content-Type
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $contentType = 'application/octet-stream'; // 默认二进制流
            
            // 根据文件扩展名设置具体的Content-Type
            $mimeTypes = [
                'txt' => 'text/plain',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed'
            ];
            
            if (isset($mimeTypes[$fileExtension])) {
                $contentType = $mimeTypes[$fileExtension];
            }
            
            // 设置必要的响应头
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . rawurlencode($attachment->file_name) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private');
            header('Pragma: private');
            header('Expires: 0');
            
            // 设置必要的响应头
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . rawurlencode($attachment->file_name) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private');
            header('Pragma: private');
            header('Expires: 0');
            
            // 输出文件内容
            readfile($filePath);
            
            // 确保脚本终止执行
            exit;
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('项目不存在', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '项目不存在'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('附件下载失败', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '下载失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
    预览项目附件
    功能说明：
    根据项目ID和附件ID提供附件文件的预览功能，支持在浏览器中直接查看文件内容
    支持的文件类型：图片（jpg, jpeg, png, gif）、PDF、文本文件（txt, json, xml）等
    请求参数：
    caseId（项目ID）：必填，整数，通过URL路径传入，指定附件所属的项目
    attachmentId（附件ID）：必填，整数，通过URL路径传入，指定要预览的附件
    返回参数：
    成功：文件预览响应（直接在浏览器中显示文件内容）
    失败：JSON错误响应
    @param int $caseId 项目ID
    @param int $attachmentId 附件ID
    @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse 文件预览响应或错误JSON
     */
    public function preview($caseId, $attachmentId)
    {
        try {
            // 验证项目是否存在
            $case = Cases::findOrFail($caseId);
            
            // 查找附件记录，确保附件属于指定项目
            $attachment = CaseAttachment::where('case_id', $caseId)
                                        ->where('id', $attachmentId)
                                        ->first();
            
            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => '附件不存在或不属于指定项目'
                ], 200);
            }
            
            // 构建完整的文件路径
            $filePath = storage_path('app/public/' . $attachment->file_path);
            
            // 检查文件是否存在
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件不存在或已被删除'
                ], 200);
            }
            
            // 验证文件可读性
            if (!is_readable($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '文件无法读取，请检查文件权限'
                ], 200);
            }
            
            // 获取文件扩展名
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // 定义支持的预览文件类型及对应的MIME类型
            $previewMimeTypes = [
                // 图片文件
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                
                // PDF文件
                'pdf' => 'application/pdf',
                
                // 文本文件
                'txt' => 'text/plain',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'html' => 'text/html',
                'htm' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                
                // Office文档（部分浏览器支持预览）
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            // 检查文件类型是否支持预览
            if (!isset($previewMimeTypes[$fileExtension])) {
                return response()->json([
                    'success' => false,
                    'message' => '该文件类型不支持在线预览，请使用下载功能查看文件',
                    'file_type' => $fileExtension,
                    'supported_types' => array_keys($previewMimeTypes)
                ], 200);
            }
            
            // 获取文件MIME类型
            $contentType = $previewMimeTypes[$fileExtension];
            
            // 记录预览日志
            \Log::info('附件预览成功', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'file_name' => $attachment->file_name,
                'file_size' => $attachment->file_size,
                'file_type' => $fileExtension,
                'file_path' => $attachment->file_path
            ]);
            
            // 清除输出缓冲
            ob_clean();
            
            // 设置预览响应头
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($filePath));
            
            // 对于图片和PDF文件，允许在浏览器中直接显示
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'pdf'])) {
                header('Content-Disposition: inline; filename="' . rawurlencode($attachment->file_name) . '"');
            } else {
                // 对于文本文件等，也使用inline方式让浏览器尝试直接显示
                header('Content-Disposition: inline; filename="' . rawurlencode($attachment->file_name) . '"');
            }
            
            header('Cache-Control: public');
            header('Pragma: public');
            header('Expires: 0');
            
            // 输出文件内容
            readfile($filePath);
            
            // 确保脚本终止执行
            exit;
            
            // 这个return不会被执行，因为我们已经exit了
            return response();
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('项目不存在', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '项目不存在'
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('附件预览失败', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '预览失败：' . $e->getMessage()
            ], 200);
        }
    }

    /**
    删除项目附件
    功能说明：
    根据项目ID和附件ID删除指定的附件文件及其记录
    请求参数：
    caseId（项目ID）：必填，整数，通过URL路径传入，指定附件所属的项目
    attachmentId（附件ID）：必填，整数，通过URL路径传入，指定要删除的附件
    返回参数：
    success（操作状态）：布尔值，true表示删除成功
    message（提示信息）：字符串，删除成功的提示信息
    @param int $caseId 项目ID
    @param int $attachmentId 附件ID
    @return \Illuminate\Http\JsonResponse 删除结果响应
     */
    public function deleteAttachment($caseId, $attachmentId)
    {
        try {
            // 验证项目是否存在
            $case = Cases::findOrFail($caseId);
            
            // 查找附件记录，确保附件属于指定项目
            $attachment = CaseAttachment::where('case_id', $caseId)
                                        ->where('id', $attachmentId)
                                        ->first();
            
            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => '附件不存在或不属于指定项目'
                ], 404);
            }
            
            // 构建完整的文件路径
            $filePath = storage_path('app/public/' . $attachment->file_path);
            
            // 如果文件存在，则删除文件
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // 删除附件记录
            $attachment->delete();
            
            // 记录删除日志
            \Log::info('附件删除成功', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path
            ]);
            
            // 返回删除成功响应
            return response()->json([
                'success' => true,
                'message' => '文件删除成功'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '项目不存在'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('附件删除失败', [
                'case_id' => $caseId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }
}


