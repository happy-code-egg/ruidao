<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
   /**
 * 通用文件上传接口 upload
 *
 * 功能描述：提供通用的文件上传功能，支持多种文件格式，最大50M文件大小限制
 *
 * 传入参数：
 * - file (file): 要上传的文件，必填
 *
 * 输出参数：
 * - success (bool): 操作是否成功
 * - message (string): 操作结果消息
 * - data (object): 文件信息（仅在成功时返回）
 *   - file_path (string): 文件存储路径
 *   - path (string): 文件存储路径（同file_path）
 *   - file_name (string): 原始文件名
 *   - file_size (int): 文件大小（字节）
 *   - file_extension (string): 文件扩展名
 *   - mime_type (string): 文件MIME类型
 *   - url (string): 文件访问URL
 *
 * 错误响应：
 * - 401: 用户未认证
 * - 422: 文件验证失败或上传失败
 * - 500: 服务器内部错误
 */
public function upload(Request $request)
{
    try {
        // 检查用户认证状态
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => '用户未认证，请先登录'
            ], 401);
        }

        // 验证上传文件参数
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200|mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z', // 最大50M
        ]);

        // 处理验证失败情况
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '文件验证失败：' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // 获取上传文件并验证有效性
        $uploadedFile = $request->file('file');
        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return response()->json([
                'success' => false,
                'message' => '文件上传失败'
            ], 422);
        }

        // 生成唯一文件名，避免文件名冲突
        $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

        // 按日期创建目录结构存储文件
        $dateFolder = date('Y/m/d');
        $filePath = $uploadedFile->storeAs('uploads/' . $dateFolder, $fileName, 'public');

        // 返回成功响应和文件信息
        return response()->json([
            'success' => true,
            'message' => '上传成功',
            'data' => [
                'file_path' => $filePath,                           // 文件存储路径
                'path' => $filePath,                                // 文件存储路径（别名）
                'file_name' => $uploadedFile->getClientOriginalName(), // 原始文件名
                'file_size' => $uploadedFile->getSize(),            // 文件大小
                'file_extension' => $uploadedFile->getClientOriginalExtension(), // 文件扩展名
                'mime_type' => $uploadedFile->getMimeType(),        // 文件MIME类型
                'url' => Storage::url($filePath)                    // 文件访问URL
            ]
        ]);

    } catch (\Exception $e) {
        // 处理异常情况
        return response()->json([
            'success' => false,
            'message' => '上传失败：' . $e->getMessage()
        ], 500);
    }
}

}
