<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaseFeeController extends Controller
{
    public function index($caseId)
    {
        $fees = CaseFee::where('case_id', $caseId)->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $fees]);
    }

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

    public function destroy($id)
    {
        $fee = CaseFee::findOrFail($id);
        $fee->delete();
        return response()->json(['success' => true]);
    }
}


