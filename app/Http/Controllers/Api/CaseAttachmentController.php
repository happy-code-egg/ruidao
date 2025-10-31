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
}


