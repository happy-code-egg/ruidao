<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerFollowupRecord;
use App\Models\Customer;
use App\Models\BusinessOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerFollowupRecordController extends Controller
{
    /**
     * 获取跟进记录列表
     */
    public function index(Request $request)
    {
        try {
            $query = CustomerFollowupRecord::with([
                'customer',
                'businessOpportunity',
                'followupPerson',
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

            if ($request->filled('followup_type')) {
                $query->where('followup_type', $request->followup_type);
            }

            if ($request->filled('contact_person')) {
                $query->where('contact_person', 'like', '%' . $request->contact_person . '%');
            }

            if ($request->filled('contact_phone')) {
                $query->where('contact_phone', 'like', '%' . $request->contact_phone . '%');
            }

            if ($request->filled('followup_person_id')) {
                $query->where('followup_person_id', $request->followup_person_id);
            }

            if ($request->filled('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            if ($request->filled('result')) {
                $query->where('result', 'like', '%' . $request->result . '%');
            }

            // 跟进时间筛选
            if ($request->filled('followup_time_start') && $request->filled('followup_time_end')) {
                $query->whereBetween('followup_time', [$request->followup_time_start, $request->followup_time_end]);
            }

            // 下次跟进时间筛选
            if ($request->filled('next_followup_time_start') && $request->filled('next_followup_time_end')) {
                $query->whereBetween('next_followup_time', [$request->next_followup_time_start, $request->next_followup_time_end]);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $records = $query->orderBy('followup_time', 'desc')->paginate($pageSize);

            // 格式化数据
            $records->getCollection()->transform(function ($record) {
                return [
                    'id' => $record->id,
                    'customer_id' => $record->customer_id,
                    'customer_name' => $record->customer->customer_name ?? '',
                    'customerName' => $record->customer->customer_name ?? '',
                    'business_opportunity_id' => $record->business_opportunity_id,
                    'businessOpportunityId' => $record->business_opportunity_id,
                    'business_name' => $record->businessOpportunity->name ?? '',
                    'businessName' => $record->businessOpportunity->name ?? '',
                    'followup_type' => $record->followup_type,
                    'followupType' => $record->followup_type,
                    'location' => $record->location,
                    'contact_person' => $record->contact_person,
                    'contactPerson' => $record->contact_person,
                    'contact_phone' => $record->contact_phone,
                    'contactPhone' => $record->contact_phone,
                    'content' => $record->content,
                    'followup_time' => $record->followup_time ? $record->followup_time->format('Y-m-d H:i:s') : '',
                    'followupTime' => $record->followup_time ? $record->followup_time->format('Y-m-d H:i:s') : '',
                    'next_followup_time' => $record->next_followup_time ? $record->next_followup_time->format('Y-m-d H:i:s') : '',
                    'nextFollowupTime' => $record->next_followup_time ? $record->next_followup_time->format('Y-m-d H:i:s') : '',
                    'result' => $record->result,
                    'followup_person_id' => $record->followup_person_id,
                    'followupPersonId' => $record->followup_person_id,
                    'followup_person' => $record->followupPerson->name ?? '',
                    'followupPerson' => $record->followupPerson->name ?? '',
                    'remark' => $record->remark,
                    'create_user' => $record->creator->name ?? '',
                    'createUser' => $record->creator->name ?? '',
                    'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $record->updater->name ?? '',
                    'updateUser' => $record->updater->name ?? '',
                    'updated_at' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $records->items(),
                    'total' => $records->total(),
                    'per_page' => $records->perPage(),
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
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
     * 创建跟进记录
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'followup_type' => 'required|string|max:50',
                'followup_time' => 'required|date',
                'followup_person_id' => 'required|integer|exists:users,id',
                'content' => 'required|string',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:200',
                'next_followup_time' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $record = CustomerFollowupRecord::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取跟进记录详情
     */
    public function show($id)
    {
        try {
            $record = CustomerFollowupRecord::with([
                'customer',
                'businessOpportunity',
                'followupPerson',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新跟进记录
     */
    public function update(Request $request, $id)
    {
        try {
            $record = CustomerFollowupRecord::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_opportunity_id' => 'nullable|integer|exists:business_opportunities,id',
                'followup_type' => 'required|string|max:50',
                'followup_time' => 'required|date',
                'followup_person_id' => 'required|integer|exists:users,id',
                'content' => 'required|string',
                'contact_person' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:200',
                'next_followup_time' => 'nullable|date',
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

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除跟进记录
     */
    public function destroy($id)
    {
        try {
            $record = CustomerFollowupRecord::findOrFail($id);
            $record->delete();

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
