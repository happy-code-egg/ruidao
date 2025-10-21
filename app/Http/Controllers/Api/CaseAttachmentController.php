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
    public function index($caseId)
    {
        $attachments = CaseAttachment::where('case_id', $caseId)->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $attachments]);
    }

    /**
     * 上传项目附件
     */
    public function upload(Request $request, $caseId)
    {
        try {
            // 验证用户认证
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => '用户未认证，请先登录'
                ], 401);
            }

            // 验证项目是否存在
            $case = Cases::findOrFail($caseId);

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:20480', // 最大20MB
                'file_type' => 'required|string|max:100',
                'file_sub_type' => 'required|string|max:100',
                'file_desc' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('cases/' . $caseId, $fileName, 'public');

            $attachment = CaseAttachment::create([
                'case_id' => $caseId,
                'file_type' => $request->file_type,
                'file_sub_type' => $request->file_sub_type,
                'file_desc' => $request->file_desc,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '上传成功',
                'data' => $attachment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ], 500);
        }
    }

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

    public function destroy($id)
    {
        $att = CaseAttachment::findOrFail($id);
        $att->delete();
        return response()->json(['success' => true]);
    }
}


