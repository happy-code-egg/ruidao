<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerRelatedPerson;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CustomerRelatedPersonController extends Controller
{
    /**
     * 获取相关人员列表
     */
    public function index(Request $request)
    {
        try {
            $query = CustomerRelatedPerson::with(['customer', 'creator', 'updater', 'relatedBusinessPerson']);

            // 搜索条件
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('customer_name')) {
                $query->whereHas('customer', function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->customer_name . '%');
                });
            }

            if ($request->filled('person_name') || $request->filled('name')) {
                $name = $request->person_name ?: $request->name;
                $query->where('person_name', 'like', '%' . $name . '%');
            }

            if ($request->filled('person_type')) {
                $query->where('person_type', $request->person_type);
            }

            if ($request->filled('related_business_person_id')) {
                $query->where('related_business_person_id', $request->related_business_person_id);
            }

            if ($request->filled('phone')) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->filled('position')) {
                $query->where('position', 'like', '%' . $request->position . '%');
            }

            if ($request->filled('department')) {
                $query->where('department', 'like', '%' . $request->department . '%');
            }

            if ($request->filled('relationship')) {
                $query->where('relationship', $request->relationship);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // 分页
            $pageSize = $request->get('page_size', 10);
            $persons = $query->orderBy('id', 'desc')->paginate($pageSize);

            // 格式化数据
            $persons->getCollection()->transform(function ($person) {
                return [
                    'id' => $person->id,
                    'customer_id' => $person->customer_id,
                    'customer_name' => $person->customer->customer_name ?? '',
                    'customerName' => $person->customer->customer_name ?? '',
                    'person_name' => $person->person_name,
                    'personName' => $person->person_name,
                    'person_type' => $person->person_type,
                    'personType' => $person->person_type,
                    'phone' => $person->phone,
                    'email' => $person->email,
                    'position' => $person->position,
                    'department' => $person->department,
                    'related_business_person' => $person->related_business_person_id,
                    'relatedBusinessPerson' => $person->related_business_person_id,
                    'related_business_person_name' => $person->relatedBusinessPerson->real_name ?? '',
                    'relatedBusinessPersonName' => $person->relatedBusinessPerson->real_name ?? '',
                    'relationship' => $person->relationship,
                    'responsibility' => $person->responsibility,
                    'is_active' => $person->is_active,
                    'isActive' => $person->is_active,
                    'business_staff' => $person->customer->businessPerson->name ?? '',
                    'businessStaff' => $person->customer->businessPerson->name ?? '',
                    'create_date' => $person->created_at ? $person->created_at->format('Y-m-d') : '',
                    'createDate' => $person->created_at ? $person->created_at->format('Y-m-d') : '',
                    'remark' => $person->remark,
                    'remarks' => $person->remark,
                    'create_user' => $person->creator->real_name ?? '',
                    'createUser' => $person->creator->real_name ?? '',
                    'creator' => $person->creator->real_name ?? '',
                    'created_at' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                    'create_time' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                    'createTime' => $person->created_at ? $person->created_at->format('Y-m-d H:i:s') : '',
                    'update_user' => $person->updater->real_name ?? '',
                    'updateUser' => $person->updater->real_name ?? '',
                    'updater' => $person->updater->real_name ?? '',
                    'updated_at' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
                    'update_time' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
                    'updateTime' => $person->updated_at ? $person->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $persons->items(),
                    'total' => $persons->total(),
                    'per_page' => $persons->perPage(),
                    'current_page' => $persons->currentPage(),
                    'last_page' => $persons->lastPage(),
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
     * 创建相关人员
     */
    public function store(Request $request)
    {
        try {
            // 数据预处理 - 前端字段映射
            $data = $request->all();
            
            // 字段映射：前端可能发送name而不是person_name
            if (isset($data['name']) && !isset($data['person_name'])) {
                $data['person_name'] = $data['name'];
                unset($data['name']);
            }
            
            // 字段映射：前端可能发送personType而不是person_type
            if (isset($data['personType']) && !isset($data['person_type'])) {
                $data['person_type'] = $data['personType'];
                unset($data['personType']);
            }

            // 字段映射：前端可能发送remarks而不是remark
            if (isset($data['remarks']) && !isset($data['remark'])) {
                $data['remark'] = $data['remarks'];
                unset($data['remarks']);
            }

            // 字段映射：前端可能发送related_business_person而不是related_business_person_id
            if (isset($data['related_business_person']) && !isset($data['related_business_person_id'])) {
                $data['related_business_person_id'] = $data['related_business_person'];
                unset($data['related_business_person']);
            }
            
            $validator = Validator::make($data, [
                'customer_id' => 'required|integer|exists:customers,id',
                'person_name' => 'required|string|max:100',
                'person_type' => 'required|string|max:50',
                'related_business_person_id' => 'nullable|integer|exists:users,id',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'relationship' => 'nullable|string|max:100',
                'responsibility' => 'nullable|string',
                'remark' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 设置创建人
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $person = CustomerRelatedPerson::create($data);

            return response()->json([
                'success' => true,
                'message' => '创建成功',
                'data' => $person
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取相关人员详情
     */
    public function show($id)
    {
        try {
            $person = CustomerRelatedPerson::with(['customer', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $person
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新相关人员
     */
    public function update(Request $request, $id)
    {
        try {
            $person = CustomerRelatedPerson::findOrFail($id);

            // 数据预处理 - 前端字段映射
            $data = $request->all();
            
            // 字段映射：前端可能发送name而不是person_name
            if (isset($data['name']) && !isset($data['person_name'])) {
                $data['person_name'] = $data['name'];
                unset($data['name']);
            }
            
            // 字段映射：前端可能发送personType而不是person_type
            if (isset($data['personType']) && !isset($data['person_type'])) {
                $data['person_type'] = $data['personType'];
                unset($data['personType']);
            }

            // 字段映射：前端可能发送remarks而不是remark
            if (isset($data['remarks']) && !isset($data['remark'])) {
                $data['remark'] = $data['remarks'];
                unset($data['remarks']);
            }

            // 字段映射：前端可能发送related_business_person而不是related_business_person_id
            if (isset($data['related_business_person']) && !isset($data['related_business_person_id'])) {
                $data['related_business_person_id'] = $data['related_business_person'];
                unset($data['related_business_person']);
            }

            $validator = Validator::make($data, [
                'customer_id' => 'required|integer|exists:customers,id',
                'person_name' => 'required|string|max:100',
                'person_type' => 'required|string|max:50',
                'related_business_person_id' => 'nullable|integer|exists:users,id',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:100',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'relationship' => 'nullable|string|max:100',
                'responsibility' => 'nullable|string',
                'remark' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data['updated_by'] = Auth::id();

            $person->update($data);

            return response()->json([
                'success' => true,
                'message' => '更新成功',
                'data' => $person
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除相关人员
     */
    public function destroy($id)
    {
        try {
            $person = CustomerRelatedPerson::findOrFail($id);
            $person->delete();

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

    /**
     * 获取人员类型列表
     */
    public function getPersonTypes()
    {
        $types = [
            ['value' => CustomerRelatedPerson::TYPE_TECH_LEADER, 'label' => '技术负责人'],
            ['value' => CustomerRelatedPerson::TYPE_BUSINESS_LEADER, 'label' => '商务负责人'],
            ['value' => CustomerRelatedPerson::TYPE_FINANCE_LEADER, 'label' => '财务负责人'],
            ['value' => CustomerRelatedPerson::TYPE_PROJECT_MANAGER, 'label' => '项目负责人'],
            ['value' => CustomerRelatedPerson::TYPE_BUSINESS_ASSISTANT, 'label' => '业务助理'],
            ['value' => CustomerRelatedPerson::TYPE_BUSINESS_COLLABORATOR, 'label' => '业务协作人'],
            ['value' => CustomerRelatedPerson::TYPE_OTHER, 'label' => '其他'],
        ];

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $types
        ]);
    }

    /**
     * 获取客户的业务人员列表
     */
    public function getCustomerBusinessPersons(Request $request)
    {
        try {
            $customerId = $request->get('customer_id');
            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => '客户ID不能为空'
                ], 400);
            }

            // 获取客户信息
            $customer = \App\Models\Customer::with([
                'businessPerson',
                'businessAssistant',
                'businessPartner'
            ])->find($customerId);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => '客户不存在'
                ], 404);
            }

            $businessPersons = [];

            // 添加业务人员
            if ($customer->businessPerson) {
                $businessPersons[] = [
                    'id' => $customer->businessPerson->id,
                    'name' => $customer->businessPerson->real_name,
                    'department' => $customer->businessPerson->department->name ?? '',
                    'phone' => $customer->businessPerson->phone,
                    'email' => $customer->businessPerson->email,
                    'type' => '业务人员'
                ];
            }

            // 添加业务助理
            if ($customer->businessAssistant) {
                $businessPersons[] = [
                    'id' => $customer->businessAssistant->id,
                    'name' => $customer->businessAssistant->real_name,
                    'department' => $customer->businessAssistant->department->name ?? '',
                    'phone' => $customer->businessAssistant->phone,
                    'email' => $customer->businessAssistant->email,
                    'type' => '业务助理'
                ];
            }

            // 添加业务协作人
            if ($customer->businessPartner) {
                $businessPersons[] = [
                    'id' => $customer->businessPartner->id,
                    'name' => $customer->businessPartner->real_name,
                    'department' => $customer->businessPartner->department->name ?? '',
                    'phone' => $customer->businessPartner->phone,
                    'email' => $customer->businessPartner->email,
                    'type' => '业务协作人'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => $businessPersons
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 搜索用户（用于添加助理/协作人）
     */
    public function searchUsers(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');
            $limit = $request->get('limit', 20);

            $query = \App\Models\User::where('status', 1); // 只查询启用的用户

            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('real_name', 'like', '%' . $keyword . '%')
                      ->orWhere('username', 'like', '%' . $keyword . '%')
                      ->orWhere('phone', 'like', '%' . $keyword . '%')
                      ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            }

            $users = $query->with('department')
                          ->limit($limit)
                          ->get()
                          ->map(function ($user) {
                              return [
                                  'id' => $user->id,
                                  'name' => $user->real_name ?: $user->username,
                                  'username' => $user->username,
                                  'real_name' => $user->real_name,
                                  'department' => $user->department->department_name ?? '',
                                  'phone' => $user->phone,
                                  'email' => $user->email,
                                  'position' => $user->position
                              ];
                          });

            return response()->json([
                'success' => true,
                'message' => '搜索成功',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '搜索失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加客户业务员
     */
    public function addCustomerBusinessPerson(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_person_ids' => 'required|array',
                'business_person_ids.*' => 'integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customerId = $request->customer_id;
            $businessPersonIds = $request->business_person_ids;

            foreach ($businessPersonIds as $personId) {
                // 检查是否已存在
                $exists = CustomerRelatedPerson::where('customer_id', $customerId)
                    ->where('related_business_person_id', $personId)
                    ->where('person_type', '业务员')
                    ->exists();

                if (!$exists) {
                    // 获取用户信息，包含部门
                    $user = \App\Models\User::with('department')->find($personId);
                    if ($user) {
                        CustomerRelatedPerson::create([
                            'customer_id' => $customerId,
                            'related_business_person_id' => $personId,
                            'person_name' => $user->real_name,
                            'person_type' => '业务员',
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'department' => $user->department->department_name ?? '',
                            'is_active' => true,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => '添加业务员成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '添加业务员失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 移除客户业务员
     */
    public function removeCustomerBusinessPerson(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:customers,id',
                'business_person_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customerId = $request->customer_id;
            $businessPersonId = $request->business_person_id;

            // 删除业务员记录
            CustomerRelatedPerson::where('customer_id', $customerId)
                ->where('related_business_person_id', $businessPersonId)
                ->where('person_type', '业务员')
                ->delete();

            // 同时删除该业务员的助理和协作人
            CustomerRelatedPerson::where('customer_id', $customerId)
                ->where('related_business_person_id', $businessPersonId)
                ->whereIn('person_type', ['业务助理', '业务协作人'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => '移除业务员成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '移除业务员失败：' . $e->getMessage()
            ], 500);
        }
    }
}
