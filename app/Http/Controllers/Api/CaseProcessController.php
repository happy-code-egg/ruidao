<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseProcess;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CaseProcessController extends Controller
{
    /**
     * 获取处理事项列表
     */
    public function index(Request $request)
    {
        try {
            $query = CaseProcess::query();

            // 按项目ID筛选
            if ($request->filled('case_id')) {
                $query->byCase($request->case_id);
            }

            // 按状态筛选
            if ($request->filled('process_status')) {
                $query->byStatus($request->process_status);
            }

            // 按负责人筛选
            if ($request->filled('assigned_to')) {
                $query->byAssignedTo($request->assigned_to);
            }

            // 按优先级筛选
            if ($request->filled('priority_level')) {
                $query->byPriority($request->priority_level);
            }

            // 关键字搜索
            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('process_name', 'like', "%{$keyword}%")
                      ->orWhere('process_description', 'like', "%{$keyword}%")
                      ->orWhere('process_remark', 'like', "%{$keyword}%");
                });
            }

            // 排序
            $query->ordered();

            // 分页参数
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 15)));

            // 获取总数
            $total = $query->count();

            // 获取数据
            $list = $query->with([
                'case:id,case_name,case_code',
                'assignedUser:id,real_name',
                'assigneeUser:id,real_name',
                'reviewerUser:id,real_name',
                'creator:id,real_name',
                'updater:id,real_name'
            ])
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'case_id' => $item->case_id,
                    'case_name' => $item->case->case_name ?? '',
                    'case_code' => $item->case->case_code ?? '',
                    'process_code' => $item->process_code,
                    'process_name' => $item->process_name,
                    'process_type' => $item->process_type,
                    'process_status' => $item->process_status,
                    'process_status_text' => $item->status_text,
                    'priority_level' => $item->priority_level,
                    'priority_text' => $item->priority_text,
                    'assigned_to' => $item->assigned_to,
                    'assigned_user_name' => $item->assignedUser->real_name ?? '',
                    'assignee' => $item->assignee,
                    'assignee_user_name' => $item->assigneeUser->real_name ?? '',
                    'is_assign' => $item->is_assign,
                    'due_date' => $item->due_date,
                    'internal_deadline' => $item->internal_deadline,
                    'official_deadline' => $item->official_deadline,
                    'customer_deadline' => $item->customer_deadline,
                    'expected_complete_date' => $item->expected_complete_date,
                    'completion_date' => $item->completion_date,
                    'process_coefficient' => $item->process_coefficient,
                    'process_description' => $item->process_description,
                    'process_result' => $item->process_result,
                    'process_remark' => $item->process_remark,
                    'is_overdue' => $item->isOverdue(),
                    'created_by' => $item->creator->real_name ?? '',
                    'updated_by' => $item->updater->real_name ?? '',
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                ];
            });

            return json_success('获取列表成功', [
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取列表失败');
        }
    }

    /**
     * 创建处理事项
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'case_id' => 'required|integer|exists:cases,id',
                'process_name' => 'required|string|max:200',
                'process_type' => 'nullable|string|max:100',
                'process_status' => 'nullable|integer|in:1,2,3,4',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'assignee' => 'nullable|integer|exists:users,id',
                'reviewer' => 'nullable|integer|exists:users,id',
                'is_assign' => 'nullable|boolean',
                'due_date' => 'nullable|date',
                'internal_deadline' => 'nullable|date',
                'official_deadline' => 'nullable|date',
                'customer_deadline' => 'nullable|date',
                'expected_complete_date' => 'nullable|date',
                'issue_date' => 'nullable|date',
                'case_stage' => 'nullable|string|max:50',
                'contract_code' => 'nullable|string|max:100',
                'process_coefficient' => 'nullable|string|max:100',
                'process_description' => 'nullable|string',
                'process_remark' => 'nullable|string',
                'service_fees' => 'nullable|array',
                'official_fees' => 'nullable|array',
                'attachments' => 'nullable|array',
                'parent_process_id' => 'nullable|integer|exists:case_processes,id',
            ], [
                'case_id.required' => '项目ID不能为空',
                'case_id.exists' => '项目不存在',
                'process_name.required' => '处理事项名称不能为空',
                'process_name.max' => '处理事项名称不能超过200个字符',
                'assigned_to.exists' => '负责人不存在',
                'assignee.exists' => '配案人不存在',
                'parent_process_id.exists' => '父处理事项不存在',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            // 生成处理事项编码
            if (empty($data['process_code'])) {
                $data['process_code'] = $this->generateProcessCode($data['case_id']);
            }

            $processItem = CaseProcess::create($data);

            return json_success('创建成功', $processItem);

        } catch (\Exception $e) {
            log_exception($e, '创建处理事项失败');
            return json_fail('创建失败');
        }
    }

    /**
     * 获取处理事项详情
     */
    public function show($id)
    {
        try {
            $processItem = CaseProcess::with([
                'case:id,case_name,case_code',
                'assignedUser:id,real_name',
                'assigneeUser:id,real_name',
                'reviewerUser:id,real_name',
                'creator:id,real_name',
                'updater:id,real_name'
            ])->find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            $result = [
                'id' => $processItem->id,
                'case_id' => $processItem->case_id,
                'case_name' => $processItem->case->case_name ?? '',
                'case_code' => $processItem->case->case_code ?? '',
                'process_code' => $processItem->process_code,
                'process_name' => $processItem->process_name,
                'process_type' => $processItem->process_type,
                'process_status' => $processItem->process_status,
                'process_status_text' => $processItem->status_text,
                'priority_level' => $processItem->priority_level,
                'priority_text' => $processItem->priority_text,
                'assigned_to' => $processItem->assigned_to,
                'assigned_user_name' => $processItem->assignedUser->real_name ?? '',
                'assignee' => $processItem->assignee,
                'assignee_user_name' => $processItem->assigneeUser->real_name ?? '',
                'reviewer' => $processItem->reviewer,
                'reviewer_user_name' => $processItem->reviewerUser->real_name ?? '',
                'is_assign' => $processItem->is_assign,
                'due_date' => $processItem->due_date,
                'internal_deadline' => $processItem->internal_deadline,
                'official_deadline' => $processItem->official_deadline,
                'customer_deadline' => $processItem->customer_deadline,
                'expected_complete_date' => $processItem->expected_complete_date,
                'completion_date' => $processItem->completion_date,
                'issue_date' => $processItem->issue_date,
                'case_stage' => $processItem->case_stage,
                'contract_code' => $processItem->contract_code,
                'estimated_hours' => $processItem->estimated_hours,
                'actual_hours' => $processItem->actual_hours,
                'process_coefficient' => $processItem->process_coefficient,
                'process_description' => $processItem->process_description,
                'process_result' => $processItem->process_result,
                'process_remark' => $processItem->process_remark,
                'service_fees' => $processItem->service_fees,
                'official_fees' => $processItem->official_fees,
                'attachments' => $processItem->attachments,
                'parent_process_id' => $processItem->parent_process_id,
                'is_overdue' => $processItem->isOverdue(),
                'created_by' => $processItem->creator->real_name ?? '',
                'updated_by' => $processItem->updater->real_name ?? '',
                'created_at' => $processItem->created_at ? $processItem->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $processItem->updated_at ? $processItem->updated_at->format('Y-m-d H:i:s') : '',
                'completed_time' => $processItem->completed_time ? $processItem->completed_time->format('Y-m-d H:i:s') : '',
            ];

            return json_success('获取详情成功', $result);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项详情失败');
            return json_fail('获取详情失败');
        }
    }

    /**
     * 更新处理事项
     */
    public function update(Request $request, $id)
    {
        try {
            $processItem = CaseProcess::find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            $validator = Validator::make($request->all(), [
                'process_name' => 'required|string|max:200',
                'process_type' => 'nullable|string|max:100',
                'process_status' => 'nullable|integer|in:1,2,3,4',
                'priority_level' => 'nullable|integer|in:1,2,3',
                'assigned_to' => 'nullable|integer|exists:users,id',
                'assignee' => 'nullable|integer|exists:users,id',
                'reviewer' => 'nullable|integer|exists:users,id',
                'is_assign' => 'nullable|boolean',
                'due_date' => 'nullable|date',
                'internal_deadline' => 'nullable|date',
                'official_deadline' => 'nullable|date',
                'customer_deadline' => 'nullable|date',
                'expected_complete_date' => 'nullable|date',
                'completion_date' => 'nullable|date',
                'issue_date' => 'nullable|date',
                'case_stage' => 'nullable|string|max:50',
                'contract_code' => 'nullable|string|max:100',
                'process_coefficient' => 'nullable|string|max:100',
                'process_description' => 'nullable|string',
                'process_result' => 'nullable|string',
                'process_remark' => 'nullable|string',
                'service_fees' => 'nullable|array',
                'official_fees' => 'nullable|array',
                'attachments' => 'nullable|array',
            ], [
                'process_name.required' => '处理事项名称不能为空',
                'process_name.max' => '处理事项名称不能超过200个字符',
                'assigned_to.exists' => '负责人不存在',
                'assignee.exists' => '配案人不存在',
            ]);

            if ($validator->fails()) {
                return json_fail($validator->errors()->first());
            }

            $data = $request->all();
            $data['updated_by'] = Auth::id();

            // 如果状态变为已完成，设置完成时间
            if (isset($data['process_status']) && $data['process_status'] == CaseProcess::STATUS_COMPLETED && !$processItem->isCompleted()) {
                $data['completed_time'] = now();
                if (!isset($data['completion_date'])) {
                    $data['completion_date'] = now()->toDateString();
                }
            }

            $processItem->update($data);

            return json_success('更新成功', $processItem);

        } catch (\Exception $e) {
            log_exception($e, '更新处理事项失败');
            return json_fail('更新失败');
        }
    }

    /**
     * 删除处理事项
     */
    public function destroy($id)
    {
        try {
            $processItem = CaseProcess::find($id);

            if (!$processItem) {
                return json_fail('处理事项不存在');
            }

            // 检查是否有子处理事项
            $hasChildren = CaseProcess::where('parent_process_id', $id)->exists();
            if ($hasChildren) {
                return json_fail('该处理事项下还有子事项，无法删除');
            }

            $processItem->delete();

            return json_success('删除成功');

        } catch (\Exception $e) {
            log_exception($e, '删除处理事项失败');
            return json_fail('删除失败');
        }
    }

    /**
     * 根据项目ID获取处理事项列表
     */
    public function getByCaseId($caseId)
    {
        try {
            $processItems = CaseProcess::byCase($caseId)
                ->with([
                    'assignedUser:id,real_name',
                    'assigneeUser:id,real_name',
                    'reviewerUser:id,real_name'
                ])
                ->ordered()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'process_name' => $item->process_name,
                        'process_type' => $item->process_type,
                        'process_status' => $item->process_status,
                        'process_status_text' => $item->status_text,
                        'assigned_to' => $item->assigned_to,
                        'assigned_user' => $item->assignedUser,
                        'assigned_user_name' => $item->assignedUser->real_name ?? '',
                        'assignee' => $item->assignee,
                        'assignee_user' => $item->assigneeUser,
                        'assignee_user_name' => $item->assigneeUser->real_name ?? '',
                        'reviewer' => $item->reviewer,
                        'reviewer_user' => $item->reviewerUser,
                        'reviewer_user_name' => $item->reviewerUser->real_name ?? '',
                        'is_assign' => $item->is_assign,
                        'due_date' => $item->due_date,
                        'internal_deadline' => $item->internal_deadline,
                        'official_deadline' => $item->official_deadline,
                        'customer_deadline' => $item->customer_deadline,
                        'expected_complete_date' => $item->expected_complete_date,
                        'issue_date' => $item->issue_date,
                        'case_stage' => $item->case_stage,
                        'contract_code' => $item->contract_code,
                        'process_coefficient' => $item->process_coefficient,
                        'process_description' => $item->process_description,
                        'process_remark' => $item->process_remark,
                        'service_fees' => $item->service_fees,
                        'official_fees' => $item->official_fees,
                        'attachments' => $item->attachments,
                        'is_overdue' => $item->isOverdue(),
                    ];
                });

            return json_success('获取处理事项列表成功', $processItems);

        } catch (\Exception $e) {
            log_exception($e, '获取处理事项列表失败');
            return json_fail('获取处理事项列表失败');
        }
    }

    /**
     * 获取需要更新处理事项的项目列表
     */
    public function getUpdateList(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $ourRefNumber = $request->get('ourRefNumber');
            $applicationNo = $request->get('applicationNo');
            $clientName = $request->get('clientName');
            $caseName = $request->get('caseName');
            $updateStatus = $request->get('updateStatus');

            // 构建查询 - 获取有处理事项需要更新的项目
            $query = \DB::table('contract_cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->leftJoin('case_processes as cp', 'cc.id', '=', 'cp.case_id')
                ->leftJoin('users as creator', 'cp.created_by', '=', 'creator.id')
                ->leftJoin('users as processor', 'cp.assigned_to', '=', 'processor.id')
                ->select([
                    'cc.id',
                    'cc.our_ref_number',
                    'cc.application_no',
                    'cc.case_name',
                    'c.customer_name as client_name',
                    \DB::raw('COUNT(cp.id) as process_count'),
                    \DB::raw('SUM(CASE WHEN cp.process_status = 2 THEN 1 ELSE 0 END) as processing_count'),
                    \DB::raw('MAX(cp.updated_at) as update_time'),
                    \DB::raw('GROUP_CONCAT(DISTINCT creator.name) as creator_name'),
                    \DB::raw('GROUP_CONCAT(DISTINCT processor.name) as processor_name')
                ])
                ->whereNotNull('cp.id')
                ->groupBy('cc.id', 'cc.our_ref_number', 'cc.application_no', 'cc.case_name', 'c.customer_name');

            // 添加搜索条件
            if ($ourRefNumber) {
                $query->where('cc.our_ref_number', 'like', "%{$ourRefNumber}%");
            }
            if ($applicationNo) {
                $query->where('cc.application_no', 'like', "%{$applicationNo}%");
            }
            if ($clientName) {
                $query->where('c.customer_name', 'like', "%{$clientName}%");
            }
            if ($caseName) {
                $query->where('cc.case_name', 'like', "%{$caseName}%");
            }

            // 获取总数
            $total = $query->get()->count();

            // 分页查询
            $list = $query->orderBy('update_time', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get()
                ->map(function ($item, $index) use ($page, $limit) {
                    return [
                        'id' => $item->id,
                        'serialNo' => ($page - 1) * $limit + $index + 1,
                        'ourRefNumber' => $item->our_ref_number,
                        'applicationNo' => $item->application_no,
                        'clientName' => $item->client_name,
                        'caseName' => $item->case_name,
                        'updateType' => '更新处理事项',
                        'updateStatus' => $item->processing_count > 0 ? 'processing' : 'pending',
                        'createTime' => $item->update_time,
                        'creator' => $item->creator_name,
                        'updateTime' => $item->update_time,
                        'processor' => $item->processor_name
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'currentPage' => (int)$page,
                    'pageSize' => (int)$limit
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('获取项目更新列表失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目处理详情
     */
    public function getCaseDetail($caseId)
    {
        try {
            // 获取项目基本信息
            $case = \DB::table('contract_cases as cc')
                ->leftJoin('customers as c', 'cc.customer_id', '=', 'c.id')
                ->where('cc.id', $caseId)
                ->select([
                    'cc.*',
                    'c.customer_name as client_name'
                ])
                ->first();

            if (!$case) {
                return response()->json([
                    'success' => false,
                    'message' => '项目不存在'
                ], 404);
            }

            // 获取处理事项列表
            $processItems = \DB::table('case_processes as cp')
                ->leftJoin('users as assigned', 'cp.assigned_to', '=', 'assigned.id')
                ->leftJoin('users as reviewer', 'cp.reviewer', '=', 'reviewer.id')
                ->where('cp.case_id', $caseId)
                ->select([
                    'cp.*',
                    'assigned.name as processor',
                    'reviewer.name as reviewer_name'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => [
                    'case' => $case,
                    'process_items' => $processItems
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('获取项目处理详情失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取项目的处理事项列表
     */
    public function getCaseProcesses($caseId)
    {
        try {
            $processItems = \DB::table('case_processes as cp')
                ->leftJoin('users as assigned', 'cp.assigned_to', '=', 'assigned.id')
                ->leftJoin('users as reviewer', 'cp.reviewer', '=', 'reviewer.id')
                ->where('cp.case_id', $caseId)
                ->select([
                    'cp.*',
                    'assigned.name as processor',
                    'reviewer.name as reviewer_name'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => '查询成功',
                'data' => $processItems
            ]);

        } catch (\Exception $e) {
            \Log::error('获取处理事项列表失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '查询失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新项目处理事项
     */
    public function updateCaseProcesses($caseId, Request $request)
    {
        try {
            \DB::beginTransaction();

            $processForm = $request->get('processForm', []);
            $selectedProcessItems = $request->get('selectedProcessItems', []);

            // 如果有选中的处理事项，更新它们
            if (!empty($selectedProcessItems)) {
                foreach ($selectedProcessItems as $processId) {
                    \DB::table('case_processes')
                        ->where('id', $processId)
                        ->where('case_id', $caseId)
                        ->update([
                            'process_status' => $processForm['processStatus'] ?? null,
                            'completion_date' => $processForm['completionDate'] ?? null,
                            'customer_deadline' => $processForm['clientDeadline'] ?? null,
                            'expected_complete_date' => $processForm['expectedCompletionDate'] ?? null,
                            'issue_date' => $processForm['topicDate'] ?? null,
                            'contract_code' => $processForm['contractCode'] ?? null,
                            'process_coefficient' => $processForm['processQuantity'] ?? null,
                            'internal_deadline' => $processForm['contentDeadline'] ?? null,
                            'process_remark' => $processForm['updateReason'] ?? null,
                            'updated_at' => now(),
                            'updated_by' => auth()->id() ?? 1
                        ]);
                }
            } else {
                // 如果没有选中特定处理事项，更新该项目的所有处理事项
                \DB::table('case_processes')
                    ->where('case_id', $caseId)
                    ->update([
                        'process_status' => $processForm['processStatus'] ?? null,
                        'completion_date' => $processForm['completionDate'] ?? null,
                        'customer_deadline' => $processForm['clientDeadline'] ?? null,
                        'expected_complete_date' => $processForm['expectedCompletionDate'] ?? null,
                        'issue_date' => $processForm['topicDate'] ?? null,
                        'contract_code' => $processForm['contractCode'] ?? null,
                        'process_coefficient' => $processForm['processQuantity'] ?? null,
                        'internal_deadline' => $processForm['contentDeadline'] ?? null,
                        'process_remark' => $processForm['updateReason'] ?? null,
                        'updated_at' => now(),
                        'updated_by' => auth()->id() ?? 1
                    ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新成功'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('更新处理事项失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量更新处理事项
     */
    public function batchUpdate(Request $request)
    {
        try {
            \DB::beginTransaction();

            $updates = $request->get('updates', []);

            foreach ($updates as $update) {
                if (isset($update['id']) && isset($update['data'])) {
                    \DB::table('case_processes')
                        ->where('id', $update['id'])
                        ->update(array_merge($update['data'], [
                            'updated_at' => now(),
                            'updated_by' => auth()->id() ?? 1
                        ]));
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => '批量更新成功'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('批量更新处理事项失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '批量更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成处理事项编码
     */
    private function generateProcessCode($caseId)
    {
        $case = Cases::find($caseId);
        if (!$case) {
            return 'PROC' . date('YmdHis') . rand(100, 999);
        }

        $caseCode = $case->case_code;
        $count = CaseProcess::where('case_id', $caseId)->count() + 1;

        return $caseCode . '-P' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
