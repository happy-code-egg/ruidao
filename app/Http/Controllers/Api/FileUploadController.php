<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * 通用文件上传接口
     */
    public function upload(Request $request)
    {
        try {
            // 检查用户认证
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未认证，请先登录'
                ], 401);
            }

            // 验证文件
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:51200|mimes:jpg,jpeg,png,gif,bmp,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z', // 最大50M
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '文件验证失败：' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedFile = $request->file('file');
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => '文件上传失败'
                ], 422);
            }
            
            // 生成唯一文件名
            $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            
            // 按日期分目录存储文件
            $dateFolder = date('Y/m/d');
            $filePath = $uploadedFile->storeAs('uploads/' . $dateFolder, $fileName, 'public');
            
            // 返回文件信息
            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => [
                    'file_path' => $filePath,
                    'path' => $filePath,
                    'file_name' => $uploadedFile->getClientOriginalName(),
                    'file_size' => $uploadedFile->getSize(),
                    'file_extension' => $uploadedFile->getClientOriginalExtension(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'url' => Storage::url($filePath)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ], 500);
        }
    }
}
