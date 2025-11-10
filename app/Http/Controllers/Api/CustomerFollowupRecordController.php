<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerFollowupRecord;
use App\Models\Customer;
use App\Models\BusinessOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 客户跟进记录控制器
 * 
 * 负责处理客户跟进记录的增删改查操作，包括：
 * - 跟进记录列表查询（支持多种搜索条件和分页）
 * - 创建新的跟进记录
 * - 查看跟进记录详情
 * - 更新跟进记录信息
 * - 删除跟进记录
 * 
 * 支持的跟进记录信息包括：
 * - 客户信息关联
 * - 商机信息关联
 * - 跟进类型、时间、地点
 * - 联系人信息
 * - 跟进内容和结果
 * - 下次跟进时间安排
 * 
 * @package App\Http\Controllers\Api
 * @author EMA Team
 * @version 1.0.0
 */
class CustomerFollowupRecordController extends Controller
{
    /**
     * 获取客户跟进记录列表
     * 
     * 支持多种搜索条件的跟进记录查询，包括客户筛选、商机筛选、跟进类型筛选、
     * 联系人信息筛选、跟进人员筛选、时间范围筛选等，并提供分页功能。
     * 
     * @param Request $request HTTP请求对象，包含以下可选参数：
     *   - customer_id: int 客户ID
     *   - business_opportunity_id: int 商机ID
     *   - customer_name: string 客户名称（模糊搜索）
     *   - followup_type: string 跟进类型
     *   - contact_person: string 联系人姓名（模糊搜索）
     *   - contact_phone: string 联系电话（模糊搜索）
     *   - followup_person_id: int 跟进人员ID
     *   - location: string 跟进地点（模糊搜索）
     *   - result: string 跟进结果（模糊搜索）
     *   - followup_time_start: string 跟进时间开始（Y-m-d H:i:s格式）
     *   - followup_time_end: string 跟进时间结束（Y-m-d H:i:s格式）
     *   - next_followup_time_start: string 下次跟进时间开始（Y-m-d H:i:s格式）
     *   - next_followup_time_end: string 下次跟进时间结束（Y-m-d H:i:s格式）
     *   - page_size: int 每页记录数，默认10
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     *   成功时返回：
     *   {
     *     "success": true,
     *     "message": "获取成功",
     *     "data": {
     *       "list": [
     *         {
     *           "id": 1,
     *           "customer_id": 1,
     *           "customer_name": "客户名称",
     *           "business_opportunity_id": 1,
     *           "business_name": "商机名称",
     *           "followup_type": "电话跟进",
     *           "location": "跟进地点",
     *           "contact_person": "联系人",
     *           "contact_phone": "联系电话",
     *           "content": "跟进内容",
     *           "followup_time": "2024-01-01 10:00:00",
     *           "next_followup_time": "2024-01-08 10:00:00",
     *           "result": "跟进结果",
     *           "followup_person_id": 1,
     *           "followup_person": "跟进人员",
     *           "remark": "备注",
     *           "create_user": "创建人",
     *           "created_at": "2024-01-01 09:00:00",
     *           "update_user": "更新人",
     *           "updated_at": "2024-01-01 09:00:00"
     *         }
     *       ],
     *       "total": 100,
     *       "per_page": 10,
     *       "current_page": 1,
     *       "last_page": 10
     *     }
     *   }
     *   失败时返回：
     *   {
     *     "success": false,
     *     "message": "获取失败：错误信息"
     *   }
     * 
     * @throws \Exception 当数据库查询失败或其他系统错误时抛出异常
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

            // 分页处理
            $pageSize = $request->get('page_size', 10);
            $records = $query->orderBy('followup_time', 'desc')->paginate($pageSize);

            // 格式化数据 - 为前端提供多种字段命名格式的兼容性
            $records->getCollection()->transform(function ($record) {
                return [
                    // 基础字段
                    'id' => $record->id,
                    'customer_id' => $record->customer_id,
                    // 客户名称 - 提供下划线和驼峰两种格式
                    'customer_name' => $record->customer->customer_name ?? '',
                    'customerName' => $record->customer->customer_name ?? '',
                    // 商机信息 - 提供下划线和驼峰两种格式
                    'business_opportunity_id' => $record->business_opportunity_id,
                    'businessOpportunityId' => $record->business_opportunity_id,
                    'business_name' => $record->businessOpportunity->name ?? '',
                    'businessName' => $record->businessOpportunity->name ?? '',
                    // 跟进类型 - 提供下划线和驼峰两种格式
                    'followup_type' => $record->followup_type,
                    'followupType' => $record->followup_type,
                    // 跟进地点
                    'location' => $record->location,
                    // 联系人信息 - 提供下划线和驼峰两种格式
                    'contact_person' => $record->contact_person,
                    'contactPerson' => $record->contact_person,
                    'contact_phone' => $record->contact_phone,
                    'contactPhone' => $record->contact_phone,
                    // 跟进内容
                    'content' => $record->content,
                    // 跟进时间 - 格式化为标准日期时间字符串，提供下划线和驼峰两种格式
                    'followup_time' => $record->followup_time ? $record->followup_time->format('Y-m-d H:i:s') : '',
                    'followupTime' => $record->followup_time ? $record->followup_time->format('Y-m-d H:i:s') : '',
                    // 下次跟进时间 - 格式化为标准日期时间字符串，提供下划线和驼峰两种格式
                    'next_followup_time' => $record->next_followup_time ? $record->next_followup_time->format('Y-m-d H:i:s') : '',
                    'nextFollowupTime' => $record->next_followup_time ? $record->next_followup_time->format('Y-m-d H:i:s') : '',
                    // 跟进结果
                    'result' => $record->result,
                    // 跟进人员信息 - 提供下划线和驼峰两种格式
                    'followup_person_id' => $record->followup_person_id,
                    'followupPersonId' => $record->followup_person_id,
                    'followup_person' => $record->followupPerson->name ?? '',
                    'followupPerson' => $record->followupPerson->name ?? '',
                    // 备注
                    'remark' => $record->remark,
                    // 创建人信息 - 提供多种字段名格式
                    'create_user' => $record->creator->name ?? '',
                    'createUser' => $record->creator->name ?? '',
                    // 创建时间 - 格式化为标准日期时间字符串，提供多种字段名格式
                    'created_at' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $record->created_at ? $record->created_at->format('Y-m-d H:i:s') : '',
                    // 更新人信息 - 提供下划线和驼峰两种格式
                    'update_user' => $record->updater->name ?? '',
                    'updateUser' => $record->updater->name ?? '',
                    // 更新时间 - 格式化为标准日期时间字符串，提供多种字段名格式
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
     * 创建新的客户跟进记录
     * 
     * 创建一条新的客户跟进记录，需要验证必填字段和数据格式，
     * 自动设置创建人和更新人为当前登录用户。
     * 
     * @param Request $request HTTP请求对象，包含以下参数：
     *   - customer_id: int 必填，客户ID，必须存在于customers表中
     *   - business_opportunity_id: int 可选，商机ID，必须存在于business_opportunities表中
     *   - followup_type: string 必填，跟进类型，最大长度50字符
     *   - followup_time: string 必填，跟进时间，日期格式
     *   - followup_person_id: int 必填，跟进人员ID，必须存在于users表中
     *   - content: string 必填，跟进内容
     *   - contact_person: string 可选，联系人姓名，最大长度100字符
     *   - contact_phone: string 可选，联系电话，最大长度50字符
     *   - location: string 可选，跟进地点，最大长度200字符
     *   - next_followup_time: string 可选，下次跟进时间，日期格式
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     *   成功时返回：
     *   {
     *     "success": true,
     *     "message": "创建成功",
     *     "data": {
     *       "id": 1,
     *       "customer_id": 1,
     *       "business_opportunity_id": 1,
     *       "followup_type": "电话跟进",
     *       "followup_time": "2024-01-01 10:00:00",
     *       "followup_person_id": 1,
     *       "content": "跟进内容",
     *       "contact_person": "联系人",
     *       "contact_phone": "联系电话",
     *       "location": "跟进地点",
     *       "next_followup_time": "2024-01-08 10:00:00",
     *       "created_by": 1,
     *       "updated_by": 1,
     *       "created_at": "2024-01-01 09:00:00",
     *       "updated_at": "2024-01-01 09:00:00"
     *     }
     *   }
     *   验证失败时返回（422状态码）：
     *   {
     *     "success": false,
     *     "message": "验证失败",
     *     "errors": {
     *       "customer_id": ["客户ID字段是必填的"],
     *       "followup_time": ["跟进时间必须是有效的日期"]
     *     }
     *   }
     *   系统错误时返回（500状态码）：
     *   {
     *     "success": false,
     *     "message": "创建失败：错误信息"
     *   }
     * 
     * @throws \Exception 当数据库操作失败或其他系统错误时抛出异常
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

            // 获取请求数据
            $data = $request->all();
            
            // 自动设置创建人和更新人为当前登录用户
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            // 创建跟进记录
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
     * 获取客户跟进记录详情
     * 
     * 根据跟进记录ID获取单条跟进记录的详细信息，包括关联的客户信息、
     * 商机信息、跟进人员信息、创建人和更新人信息。
     * 
     * @param int $id 跟进记录ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     *   成功时返回：
     *   {
     *     "success": true,
     *     "message": "获取成功",
     *     "data": {
     *       "id": 1,
     *       "customer_id": 1,
     *       "business_opportunity_id": 1,
     *       "followup_type": "电话跟进",
     *       "location": "跟进地点",
     *       "contact_person": "联系人",
     *       "contact_phone": "联系电话",
     *       "content": "跟进内容",
     *       "followup_time": "2024-01-01T10:00:00.000000Z",
     *       "next_followup_time": "2024-01-08T10:00:00.000000Z",
     *       "result": "跟进结果",
     *       "followup_person_id": 1,
     *       "remark": "备注",
     *       "created_by": 1,
     *       "updated_by": 1,
     *       "created_at": "2024-01-01T09:00:00.000000Z",
     *       "updated_at": "2024-01-01T09:00:00.000000Z",
     *       "customer": {
     *         "id": 1,
     *         "customer_name": "客户名称",
     *         "customer_code": "CUS001"
     *       },
     *       "business_opportunity": {
     *         "id": 1,
     *         "name": "商机名称"
     *       },
     *       "followup_person": {
     *         "id": 1,
     *         "name": "跟进人员"
     *       },
     *       "creator": {
     *         "id": 1,
     *         "name": "创建人"
     *       },
     *       "updater": {
     *         "id": 1,
     *         "name": "更新人"
     *       }
     *     }
     *   }
     *   记录不存在时返回（404状态码）：
     *   {
     *     "success": false,
     *     "message": "获取失败：错误信息"
     *   }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当指定ID的跟进记录不存在时抛出异常
     * @throws \Exception 当数据库查询失败或其他系统错误时抛出异常
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
     * 更新客户跟进记录
     * 
     * 根据跟进记录ID更新跟进记录信息，需要验证必填字段和数据格式，
     * 自动设置更新人为当前登录用户。
     * 
     * @param Request $request HTTP请求对象，包含以下参数：
     *   - customer_id: int 必填，客户ID，必须存在于customers表中
     *   - business_opportunity_id: int 可选，商机ID，必须存在于business_opportunities表中
     *   - followup_type: string 必填，跟进类型，最大长度50字符
     *   - followup_time: string 必填，跟进时间，日期格式
     *   - followup_person_id: int 必填，跟进人员ID，必须存在于users表中
     *   - content: string 必填，跟进内容
     *   - contact_person: string 可选，联系人姓名，最大长度100字符
     *   - contact_phone: string 可选，联系电话，最大长度50字符
     *   - location: string 可选，跟进地点，最大长度200字符
     *   - next_followup_time: string 可选，下次跟进时间，日期格式
     * @param int $id 跟进记录ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     *   成功时返回：
     *   {
     *     "success": true,
     *     "message": "更新成功",
     *     "data": {
     *       "id": 1,
     *       "customer_id": 1,
     *       "business_opportunity_id": 1,
     *       "followup_type": "电话跟进",
     *       "followup_time": "2024-01-01 10:00:00",
     *       "followup_person_id": 1,
     *       "content": "更新后的跟进内容",
     *       "contact_person": "联系人",
     *       "contact_phone": "联系电话",
     *       "location": "跟进地点",
     *       "next_followup_time": "2024-01-08 10:00:00",
     *       "updated_by": 1,
     *       "created_at": "2024-01-01 09:00:00",
     *       "updated_at": "2024-01-01 10:00:00"
     *     }
     *   }
     *   验证失败时返回（422状态码）：
     *   {
     *     "success": false,
     *     "message": "验证失败",
     *     "errors": {
     *       "customer_id": ["客户ID字段是必填的"],
     *       "followup_time": ["跟进时间必须是有效的日期"]
     *     }
     *   }
     *   记录不存在或系统错误时返回（500状态码）：
     *   {
     *     "success": false,
     *     "message": "更新失败：错误信息"
     *   }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当指定ID的跟进记录不存在时抛出异常
     * @throws \Exception 当数据库操作失败或其他系统错误时抛出异常
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

            // 获取请求数据并设置更新人
            $data = $request->all();
            $data['updated_by'] = auth()->id(); // 自动设置更新人为当前登录用户

            // 更新跟进记录
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
     * 删除客户跟进记录
     * 
     * 根据跟进记录ID删除指定的跟进记录。删除操作是物理删除，
     * 记录将从数据库中永久移除。
     * 
     * @param int $id 跟进记录ID
     * 
     * @return \Illuminate\Http\JsonResponse 返回JSON响应
     *   成功时返回：
     *   {
     *     "success": true,
     *     "message": "删除成功"
     *   }
     *   记录不存在或系统错误时返回（500状态码）：
     *   {
     *     "success": false,
     *     "message": "删除失败：错误信息"
     *   }
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 当指定ID的跟进记录不存在时抛出异常
     * @throws \Exception 当数据库操作失败或其他系统错误时抛出异常
     */
    public function destroy($id)
    {
        try {
            // 查找指定ID的跟进记录，如果不存在则抛出异常
            $record = CustomerFollowupRecord::findOrFail($id);
            // 执行物理删除操作
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
