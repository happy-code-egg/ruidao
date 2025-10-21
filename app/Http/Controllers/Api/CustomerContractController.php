<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerContract;
use App\Models\Customer;
use App\Models\BusinessOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerContractController extends Controller
{
    /**
     * 获取合同列表
     */
    public function index(Request $request)
    {
        try {
            $query = CustomerContract::with([
                'customer',
                'businessOpportunity',
                'businessPerson',
                'creator',
                'updater'
            ]);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('business_opportunity_id')) {
                $query->where('business_opportunity_id', $request->business_opportunity_id);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('contract_no')) {
                $query->where('contract_no', 'like', '%' . $request->contract_no . '%');
            }

            if ($request->filled('contract_name')) {
                $query->where('contract_name', 'like', '%' . $request->contract_name . '%');
            }

            if ($request->filled('contract_type')) {
                $query->where('contract_type', $request->contract_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('business_person_id')) {
                $query->where('business_person_id', $request->business_person_id);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            // 合同金额筛选
            if ($request->filled('amount_min') && $request->filled('amount_max')) {
                $query->whereBetween('contract_amount', [$request->amount_min, $request->amount_max]);
            }

            // 签约时间筛选
            if ($request->filled('sign_date_start') && $request->filled('sign_date_end')) {
                $query->whereBetween('sign_date', [$request->sign_date_start, $request->sign_date_end]);
            }

            // 合同期间筛选
            if ($request->filled('start_date_start') && $request->filled('start_date_end')) {
                $query->whereBetween('start_date', [$request->start_date_start, $request->start_date_end]);
            }

            if ($request->filled('end_date_start') && $request->filled('end_date_end')) {
                $query->whereBetween('end_date', [$request->end_date_start, $request->end_date_end]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $contracts = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据
            $contracts->getCollection()->transform(function ($contract) {
                return [
                    'id' => $contract->id,
                    'customer_id' => $contract->customer_id,
                    'customer_name' => $contract->customer->customer_name ?? '',
                    'customerName' => $contract->customer->customer_name ?? '',
                    'business_opportunity_id' => $contract->business_opportunity_id,
                    'businessOpportunityId' => $contract->business_opportunity_id,
                    'business_name' => $contract->businessOpportunity->name ?? '',
                    'businessName' => $contract->businessOpportunity->name ?? '',
                    'contract_no' => $contract->contract_no,
                    'contractNo' => $contract->contract_no,
                    'contract_name' => $contract->contract_name,
                    'contractName' => $contract->contract_name,
                    'contract_amount' => $contract->contract_amount,
                    'contractAmount' => $contract->contract_amount,
                    'sign_date' => $contract->sign_date ? $contract->sign_date->format('Y-m-d') : '',
                    'signDate' => $contract->sign_date ? $contract->sign_date->format('Y-m-d') : '',
                    'start_date' => $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                    'startDate' => $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                    'end_date' => $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
                    'endDate' => $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
                    'contract_type' => $contract->contract_type,
                    'contractType' => $contract->contract_type,
                    'status' => $contract->status,
                    'business_person' => $contract->businessPerson->name ?? '',
                    'businessPerson' => $contract->businessPerson->name ?? '',
                    'business_person_id' => $contract->business_person_id,
                    'businessPersonId' => $contract->business_person_id,
                    'contract_content' => $contract->contract_content,
                    'contractContent' => $contract->contract_content,
                    'payment_method' => $contract->payment_method,
                    'paymentMethod' => $contract->payment_method,
                    'paid_amount' => $contract->paid_amount,
                    'paidAmount' => $contract->paid_amount,
                    'unpaid_amount' => $contract->unpaid_amount,
                    'unpaidAmount' => $contract->unpaid_amount,
                    'business_staff' => $contract->businessPerson->name ?? '',
                    'businessStaff' => $contract->businessPerson->name ?? '',
                    'remark' => $contract->remark,
                    'create_user' => $contract->creator->name ?? '',
                    'createUser' => $contract->creator->name ?? '',
                    'created_at' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $contract->created_at ? $contract->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $contract->updater->name ?? '',
                    'updateUser' => $contract->updater->name ?? '',
                    'updated_at' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $contract->updated_at ? $contract->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $contracts->items(),
                    'total' => $contracts->total(),
                    'per_page' => $contracts->perPage(),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
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
     * 创建合同
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'contract_name' => 'required|string|max:200',
                'contract_amount' => 'required|numeric|min:0',
                'contract_type' => 'required|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'sign_date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'payment_method' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 生成合同编号
            $data['contract_no'] = CustomerContract::generateContractNo();
            
            // 计算未付金额
            $data['unpaid_amount'] = $data['contract_amount'] - ($data['paid_amount'] ?? 0);
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $contract = CustomerContract::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取合同详情
     */
    public function show($id)
    {
        try {
            $contract = CustomerContract::with([
                'customer',
                'businessOpportunity',
                'businessPerson',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新合同
     */
    public function update(Request $request, $id)
    {
        try {
            $contract = CustomerContract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'contract_name' => 'required|string|max:200',
                'contract_amount' => 'required|numeric|min:0',
                'contract_type' => 'required|string|max:50',
                'business_person_id' => 'required|integer|exists:users,id',
                'sign_date' => 'nullable|date',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'payment_method' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 计算未付金额
            $data['unpaid_amount'] = $data['contract_amount'] - ($data['paid_amount'] ?? 0);
            
            $data['updated_by'] = auth()->id();

            $contract->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $contract
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除合同
     */
    public function destroy($id)
    {
        try {
            $contract = CustomerContract::findOrFail($id);
            $contract->delete();

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
