<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaseFeeController extends Controller
{

    /**
    获取指定案件的费用列表
    请求参数：
    路径参数：caseId（案件 ID）：必填，整数，案件的唯一标识 ID，用于查询该案件关联的所有费用记录
    返回参数：
    success（操作状态）：布尔值，true 表示成功
    data（费用列表）：数组，包含该案件下所有费用记录的详情对象，字段包括：
    id（主键 ID）：整数，费用记录的自增主键
    case_id（案件 ID）：整数，关联的案件 ID（与请求参数 caseId 一致）
    其他费用相关字段：如费用金额、费用类型、支付状态、创建时间等（根据 CaseFee 模型实际字段而定）
    @param int $caseId 案件 ID，用于指定查询的案件
    @return \Illuminate\Http\JsonResponse JSON 响应，包含该案件的费用列表数据
     */
    public function index($caseId)
    {
        $fees = CaseFee::where('case_id', $caseId)->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $fees]);
    }

    /**
    为指定案件创建费用记录
    请求参数：
    路径参数：caseId（案件 ID）：必填，整数，案件的唯一标识 ID，用于关联新创建的费用记录
    请求体参数：
    fee_type（费用类型）：必填，字符串，仅允许值为'service'（服务费）或 'official'（官方费用）
    fee_name（费用名称）：必填，字符串，最大长度 200 字符，费用的具体名称
    fee_description（费用描述）：可选，字符串，无长度限制，费用的详细说明
    amount（金额）：必填，数值，费用的具体金额
    currency（货币类型）：可选，字符串，最大长度 10 字符，如 'CNY'、'USD' 等
    remarks（备注）：可选，字符串，最大长度 500 字符，关于该费用的补充说明
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，验证失败时返回错误描述
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    data（费用记录）：对象，创建成功时返回新创建的费用记录详情，包含以下字段：
    id（主键 ID）：整数，费用记录的自增主键
    case_id（案件 ID）：整数，关联的案件 ID（与路径参数 caseId 一致）
    fee_type（费用类型）：字符串，费用的类型（'service' 或 'official'）
    fee_name（费用名称）：字符串，费用的名称
    fee_description（费用描述）：字符串，费用的描述（可能为 null）
    amount（金额）：数值，费用的金额
    currency（货币类型）：字符串，货币类型（可能为 null）
    remarks（备注）：字符串，备注信息（可能为 null）
    时间字段：如 created_at（创建时间）等（根据 CaseFee 模型实际字段而定）
    @param Request $request 请求对象，包含创建费用所需的参数
    @param int $caseId 案件 ID，用于关联费用记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含创建结果信息
     */
    public function store(Request $request, $caseId)
    {
        $data = $request->all();
        $data['case_id'] = $caseId;
        $validator = Validator::make($data, [
            'fee_type' => 'required|in:service,official',
            'fee_name' => 'required|string|max:200',
            'fee_description' => 'nullable|string',
            'amount' => 'required|numeric',
            'currency' => 'nullable|string|max:10',
            'remarks' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '验证失败', 'errors' => $validator->errors()], 422);
        }
        $fee = CaseFee::create($data);
        return response()->json(['success' => true, 'data' => $fee]);
    }

    /**
    更新指定的案件费用记录
    请求参数：
    路径参数：id（费用记录 ID）：必填，整数，费用记录的唯一标识 ID，用于指定待更新的记录
    请求体参数（均为可选，按需提供）：
    fee_type（费用类型）：字符串，仅允许值为'service'（服务费）或 'official'（官方费用）
    fee_name（费用名称）：字符串，最大长度 200 字符，费用的具体名称
    fee_description（费用描述）：字符串，无长度限制，费用的详细说明
    amount（金额）：数值，费用的具体金额
    currency（货币类型）：字符串，最大长度 10 字符，如 'CNY'、'USD' 等
    remarks（备注）：字符串，最大长度 500 字符，关于该费用的补充说明
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    message（提示信息）：字符串，验证失败时返回错误描述
    errors（错误信息）：对象，验证失败时返回具体字段的错误详情（仅验证失败时存在）
    @param Request $request 请求对象，包含更新费用所需的参数
    @param int $id 费用记录 ID，用于指定待更新的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含更新结果信息
     */
    /**
    删除指定的案件费用记录
    请求参数：
    路径参数：id（费用记录 ID）：必填，整数，费用记录的唯一标识 ID，用于指定待删除的记录
    返回参数：
    success（操作状态）：布尔值，true 表示成功，false 表示失败
    @param int $id 费用记录 ID，用于指定待删除的记录
    @return \Illuminate\Http\JsonResponse JSON 响应，包含删除结果信息
     */
    public function update(Request $request, $id)
    {
        $fee = CaseFee::findOrFail($id);
        $data = $request->all();
        $validator = Validator::make($data, [
            'fee_type' => 'sometimes|in:service,official',
            'fee_name' => 'sometimes|string|max:200',
            'fee_description' => 'nullable|string',
            'amount' => 'sometimes|numeric',
            'currency' => 'nullable|string|max:10',
            'remarks' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '验证失败', 'errors' => $validator->errors()], 422);
        }
        $fee->update($data);
        return response()->json(['success' => true]);
    }
//

    /**
     * 删除指定ID的案件费用记录
     *
     * @param int $id 要删除的案件费用记录ID
     * @return \Illuminate\Http\JsonResponse 返回删除成功的JSON响应
     */
    public function destroy($id)
    {
        $fee = CaseFee::findOrFail($id);
        $fee->delete();
        return response()->json(['success' => true]);
    }
}


