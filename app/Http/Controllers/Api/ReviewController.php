<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseProcess;
use App\Models\Cases;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReviewController extends Controller
{
    /**
     * 获取待提交（草稿）列表
     */
    public function getDraftList(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $projectNumber = $request->input('projectNumber');
            $caseName = $request->input('caseName');
            $applicationNo = $request->input('applicationNo');
            $applicationType = $request->input('applicationType');
            $processItem = $request->input('processItem');

            $query = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->whereHas('case', function ($caseQuery) use ($projectNumber, $caseName, $applicationNo, $applicationType) {
                    if ($projectNumber) {
                        $caseQuery->where('case_code', 'like', "%{$projectNumber}%");
                    }
                    if ($caseName) {
                        $caseQuery->where('case_name', 'like', "%{$caseName}%");
                    }
                    if ($applicationNo) {
                        $caseQuery->where('application_no', 'like', "%{$applicationNo}%");
                    }
                    if ($applicationType) {
                        $caseQuery->where('application_type', $applicationType);
                    }
                })
                ->where('process_status', CaseProcess::STATUS_DRAFT) // 草稿状态
                ->whereNotNull('assigned_to'); // 已分配的

            if ($processItem) {
                $query->where('process_name', 'like', "%{$processItem}%");
            }

            $query->orderBy('created_at', 'desc');
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            $data = $processes->map(function ($process, $index) use ($page, $limit) {
                $case = $process->case;
                $customer = $case->customer;
                return [
                    'id' => $process->id,
                    'serialNo' => ($page - 1) * $limit + $index + 1,
                    'projectNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'techLead' => $process->assignedUser ? $process->assignedUser->name : '',
                    'processItem' => $process->process_name ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'assignDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'statusTime' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i') : '',
                    'caseNote' => $case->case_notes ?? '',
                    'registrationNo' => $case->registration_no ?? '',
                    'trademarkCategory' => $case->trademark_category ?? '',
                    'customerDocNo' => $case->customer_doc_no ?? ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $processes->total(),
                    'currentPage' => $processes->currentPage(),
                    'pageSize' => $processes->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取待处理列表（草稿已提交，等待分配核稿人）
     */
    public function getPendingList(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->where('process_status', CaseProcess::STATUS_ASSIGNED) // 已分配状态，等待开始
                ->whereNotNull('assigned_to')
                ->whereNull('reviewer'); // 未指定核稿人

            $query->orderBy('created_at', 'desc');
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            $data = $processes->map(function ($process, $index) use ($page, $limit) {
                $case = $process->case;
                $customer = $case->customer;
                return [
                    'id' => $process->id,
                    'serialNo' => ($page - 1) * $limit + $index + 1,
                    'projectNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'techLead' => $process->assignedUser ? $process->assignedUser->name : '',
                    'processItem' => $process->process_name ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'assignDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'statusTime' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i') : ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $processes->total(),
                    'currentPage' => $processes->currentPage(),
                    'pageSize' => $processes->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取待开始列表（已提交，等待开始核稿）
     */
    public function getToBeStartList(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = CaseProcess::leftJoin('cases', 'case_processes.case_id', '=', 'cases.id')
                ->leftJoin('customers', 'cases.customer_id', '=', 'customers.id')
                ->leftJoin('users as assigned_users', 'case_processes.assigned_to', '=', 'assigned_users.id')
                ->leftJoin('users as reviewer_users', 'case_processes.reviewer', '=', 'reviewer_users.id')
                ->where('case_processes.process_status', CaseProcess::STATUS_PENDING) // 待开始状态
                ->whereNotNull('case_processes.assigned_to')
                ->whereNotNull('case_processes.reviewer') // 已指定核稿人，等待开始
                ->select(
                    'case_processes.*',
                    'cases.case_code',
                    'cases.case_name',
                    'cases.application_no',
                    'cases.application_type',
                    'customers.customer_name',
                    'assigned_users.real_name as assigned_user_name',
                    'reviewer_users.real_name as reviewer_user_name'
                );

            $query->orderBy('created_at', 'desc');
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            $data = $processes->map(function ($process, $index) use ($page, $limit) {
                $case = $process->case;
                $customer = $case->customer;
                return [
                    'id' => $process->id,
                    'serialNo' => ($page - 1) * $limit + $index + 1,
                    'projectNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'techLead' => $process->assignedUser ? $process->assignedUser->name : '',
                    'processItem' => $process->process_name ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'assignDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'statusTime' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i') : ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $processes->total(),
                    'currentPage' => $processes->currentPage(),
                    'pageSize' => $processes->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核中列表（正在进行核稿审核）
     */
    public function getInReviewList(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->where('process_status', CaseProcess::STATUS_IN_PROGRESS) // 审核中状态
                ->whereNotNull('assigned_to')
                ->whereNotNull('reviewer');

            $query->orderBy('created_at', 'desc');
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            $data = $processes->map(function ($process, $index) use ($page, $limit) {
                $case = $process->case;
                $customer = $case->customer;
                return [
                    'id' => $process->id,
                    'serialNo' => ($page - 1) * $limit + $index + 1,
                    'projectNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'techLead' => $process->assignedUser ? $process->assignedUser->name : '',
                    'processItem' => $process->process_name ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'assignDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'statusTime' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i') : ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $processes->total(),
                    'currentPage' => $processes->currentPage(),
                    'pageSize' => $processes->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核完成列表（核稿审核已完成）
     */
    public function getCompletedList(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->where('process_status', CaseProcess::STATUS_COMPLETED) // 审核完成状态
                ->whereNotNull('assigned_to')
                ->whereNotNull('reviewer');

            $query->orderBy('completion_date', 'desc');
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            $data = $processes->map(function ($process, $index) use ($page, $limit) {
                $case = $process->case;
                $customer = $case->customer;
                return [
                    'id' => $process->id,
                    'serialNo' => ($page - 1) * $limit + $index + 1,
                    'projectNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'techLead' => $process->assignedUser ? $process->assignedUser->name : '',
                    'processItem' => $process->process_name ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'assignDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'completionDate' => $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'statusTime' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i') : ''
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $processes->total(),
                    'currentPage' => $processes->currentPage(),
                    'pageSize' => $processes->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取核稿详情
     */
    public function getReviewDetail($id)
    {
        try {
            $process = CaseProcess::with(['case.customer', 'assignedUser', 'reviewerUser'])
                ->find($id);

            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            $case = $process->case;
            $customer = $case->customer;

            $data = [
                'id' => $process->id,
                'caseNumber' => $case->case_code ?? '',
                'proposalName' => $case->proposal_name ?? '',
                'clientName' => $customer ? $customer->customer_name : '',
                'caseName' => $case->case_name ?? '',
                'applicationType' => $case->application_type ?? '',
                'businessStaff' => $case->business_person_name ?? '',
                'applicationNo' => $case->application_no ?? '',
                'registrationNo' => $case->registration_no ?? '',
                'customerDocNo' => $case->customer_doc_no ?? '',
                'trademarkCategory' => $case->trademark_category ?? '',
                'processItem' => $process->process_name ?? '',
                'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                'customerDeadline' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                'processStatus' => $process->process_status,
                'statusText' => $process->status_text,
                'caseNotes' => $case->case_notes ?? '',
                'processDescription' => $process->process_description ?? '',
                'createdAt' => $process->created_at ? $process->created_at->format('Y-m-d H:i:s') : '',
                'updatedAt' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i:s') : '',
                'completionDate' => $process->completion_date ? $process->completion_date->format('Y-m-d H:i:s') : ''
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 流转处理事项
     */
    public function transferProcess(Request $request)
    {
        try {
            $processId = $request->input('process_id');
            $nextStatus = $request->input('next_status');
            $notes = $request->input('notes', '');

            $process = CaseProcess::find($processId);
            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            // 更新状态
            $process->process_status = $nextStatus;
            $process->process_description = $notes;
            $process->updated_at = now();

            // 如果是完成状态，记录完成时间
            if ($nextStatus == CaseProcess::STATUS_COMPLETED) {
                $process->completion_date = now();
            }

            $process->save();

            return response()->json([
                'success' => true,
                'message' => '流转成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '流转失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 退回处理事项
     */
    public function returnProcess(Request $request)
    {
        try {
            $processId = $request->input('process_id');
            $reason = $request->input('reason', '');

            $process = CaseProcess::find($processId);
            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            // 退回到草稿状态
            $process->process_status = CaseProcess::STATUS_DRAFT;
            $process->process_description = "退回原因：" . $reason;
            $process->updated_at = now();
            $process->save();

            return response()->json([
                'success' => true,
                'message' => '退回成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '退回失败：' . $e->getMessage()
            ], 500);
        }
    }
}
