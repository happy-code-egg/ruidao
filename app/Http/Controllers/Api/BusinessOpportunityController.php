<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessOpportunity;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessOpportunityController extends Controller
{
    /**
     * 获取商机列表
     */
    public function index(Request $request)
    {
        try {
            $query = BusinessOpportunity::with([
                'customer',
                'businessPerson',
                'creator', 
                'updater'
            ]);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('business_code')) {
                $query->where('business_code', 'like', '%' . $request->business_code . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('business_type')) {
                $query->where('business_type', $request->business_type);
            }

            if ($request->filled('case_type')) {
                $query->where('case_type', $request->case_type);
            }

            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id);
            }

            if ($request->filled('contact_person')) {
                $query->where('contact_person', 'like', '%' . $request->contact_person . '%');
            }

            if ($request->filled('contact_phone')) {
                $query->where('contact_phone', 'like', '%' . $request->contact_phone . '%');
            }

            if ($request->filled('is_contract')) {
                $query->where('is_contract', $request->is_contract);
            }

            // 预计签约时间筛选
            if ($request->filled('estimated_sign_start_date') && $request->filled('estimated_sign_end_date')) {
                $query->whereBetween('estimated_sign_time', [$request->estimated_sign_start_date, $request->estimated_sign_end_date]);
            }

            // 跟进时间筛选
            if ($request->filled('next_time_start') && $request->filled('next_time_end')) {
                $query->whereBetween('next_time', [$request->next_time_start, $request->next_time_end]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $opportunities = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据
            $opportunities->getCollection()->transform(function ($opportunity) {
                return [
                    'id' => $opportunity->id,
                    'customer_id' => $opportunity->customer_id,
                    'customer_name' => $opportunity->customer->customer_name ?? '',
                    'customerName' => $opportunity->customer->customer_name ?? '',
                    'business_code' => $opportunity->business_code,
                    'businessCode' => $opportunity->business_code,
                    'name' => $opportunity->name,
                    'businessName' => $opportunity->name,
                    'contact_person' => $opportunity->contact_person,
                    'contactPerson' => $opportunity->contact_person,
                    'contact_phone' => $opportunity->contact_phone,
                    'contactPhone' => $opportunity->contact_phone,
                    'business_person' => $opportunity->businessPerson->name ?? '',
                    'businessPerson' => $opportunity->businessPerson->name ?? '',
                    'business_person_id' => $opportunity->business_person_id,
                    'businessPersonId' => $opportunity->business_person_id,
                    'business_staff' => $opportunity->businessPerson->name ?? '',
                    'businessStaff' => $opportunity->businessPerson->name ?? '',
                    'next_time' => $opportunity->next_time ? $opportunity->next_time->format('Y-m-d H:i:s') : '',
                    'nextTime' => $opportunity->next_time ? $opportunity->next_time->format('Y-m-d H:i:s') : '',
                    'second_time' => $opportunity->second_time ? $opportunity->second_time->format('Y-m-d') : '',
                    'secondTime' => $opportunity->second_time ? $opportunity->second_time->format('Y-m-d') : '',
                    'content' => $opportunity->content,
                    'case_type' => $opportunity->case_type,
                    'caseType' => $opportunity->case_type,
                    'business_type' => $opportunity->business_type,
                    'businessType' => $opportunity->business_type,
                    'estimated_amount' => $opportunity->estimated_amount,
                    'estimatedAmount' => $opportunity->estimated_amount,
                    'estimated_sign_time' => $opportunity->estimated_sign_time ? $opportunity->estimated_sign_time->format('Y-m-d') : '',
                    'estimatedSignTime' => $opportunity->estimated_sign_time ? $opportunity->estimated_sign_time->format('Y-m-d') : '',
                    'status' => $opportunity->status,
                    'is_contract' => $opportunity->is_contract,
                    'isContract' => $opportunity->is_contract,
                    'background' => $opportunity->background,
                    'remark' => $opportunity->remark,
                    'create_user' => $opportunity->creator->name ?? '',
                    'createUser' => $opportunity->creator->name ?? '',
                    'created_at' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $opportunity->created_at ? $opportunity->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $opportunity->updater->name ?? '',
                    'updateUser' => $opportunity->updater->name ?? '',
                    'updated_at' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $opportunity->updated_at ? $opportunity->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $opportunities->items(),
                    'total' => $opportunities->total(),
                    'per_page' => $opportunities->perPage(),
                    'current_page' => $opportunities->currentPage(),
                    'last_page' => $opportunities->lastPage(),
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
     * 创建商机
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'name' => 'required|string|max:200',
                'business_type' => 'required|string|max:50',
                'case_type' => 'nullable|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'estimated_amount' => 'nullable|numeric|min:0',
                'estimated_sign_time' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 生成商机编号
            $data['business_code'] = BusinessOpportunity::generateCode();
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $opportunity = BusinessOpportunity::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取商机详情
     */
    public function show($id)
    {
        try {
            $opportunity = BusinessOpportunity::with(['customer', 'businessPerson', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新商机
     */
    public function update(Request $request, $id)
    {
        try {
            $opportunity = BusinessOpportunity::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'name' => 'required|string|max:200',
                'business_type' => 'required|string|max:50',
                'case_type' => 'nullable|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'estimated_amount' => 'nullable|numeric|min:0',
                'estimated_sign_time' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $opportunity->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $opportunity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除商机
     */
    public function destroy($id)
    {
        try {
            $opportunity = BusinessOpportunity::findOrFail($id);
            $opportunity->delete();

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
}
