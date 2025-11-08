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
     * 核稿管理控制器（原版）
     *
     * 功能:
     * - 提供草稿、待开始、审核中、已完成列表查询
     * - 支持核稿详情、流程流转与退回
     * - 统一返回结构，包含分页信息
     *
     * 路由说明:
     * - 当前控制器未在 routes/api.php 中直接绑定
     * - 对外接口使用 ReviewControllerFixed，路径以 `/api/review/*` 暴露
     * - 本注释用于代码维护与后续路由接入参考
     *
     * 内部说明:
     * - 依赖 `case_processes`、`cases`、`customers`、`users` 等表与关联
     * - 使用 CaseProcess 的状态常量：STATUS_DRAFT(草稿)、STATUS_ASSIGNED(已分配/待处理)、STATUS_PENDING(待开始)、STATUS_IN_PROGRESS(审核中)、STATUS_COMPLETED(已完成)
     */
    /**
     * 获取待提交（草稿）列表
     *
     * 接口:
     * - 暂未绑定路由；替代接口: GET `/api/review/draft-list` (ReviewControllerFixed@getDraftList)
     *
     * 请求参数:
     * - `limit` 分页大小，默认 10
     * - `page` 当前页，默认 1
     * - `projectNumber` 项目编号筛选（模糊）
     * - `caseName` 案件名称筛选（模糊）
     * - `applicationNo` 申请/注册号筛选（模糊）
     * - `applicationType` 申请类型筛选（精确）
     * - `processItem` 处理事项名称筛选（模糊）
     *
     * 返回参数:
     * - `success` 布尔
     * - `data.list` 列表项（含 `id`、`serialNo`、`projectNumber`、`caseName`、`applicationNo`、`customerName`、`techLead`、`processItem`、`applicationType`、`assignDate`、`internalDeadline`、`officialDeadline`、`customerDeadline`、`processor`、`reviewer`、`statusTime`、`caseNote`、`registrationNo`、`trademarkCategory`、`customerDocNo`）
     * - `data.total` 总条数
     * - `data.currentPage` 当前页
     * - `data.pageSize` 每页条数
     *
     * 内部说明:
     * - 过滤 `process_status = STATUS_DRAFT` 且 `assigned_to` 非空
     * - 按 `created_at` 倒序，使用 Eloquent 关联映射
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
     *
     * 接口:
     * - 暂未绑定路由；兼容旧接口（建议改用 GET `/api/review/to-be-start-list`）
     *
     * 请求参数:
     * - `limit` 分页大小，默认 10
     * - `page` 当前页，默认 1
     *
     * 返回参数:
     * - `success` 布尔
     * - `data.list` 列表项（含 `id`、`serialNo`、`projectNumber`、`caseName`、`applicationNo`、`customerName`、`techLead`、`processItem`、`applicationType`、`assignDate`、`internalDeadline`、`officialDeadline`、`customerDeadline`、`processor`、`reviewer`、`statusTime`）
     * - `data.total` 总条数、`data.currentPage` 当前页、`data.pageSize` 每页条数
     *
     * 内部说明:
     * - 过滤 `process_status = STATUS_ASSIGNED`，`assigned_to` 非空，`reviewer` 为空
     * - 用于草稿已提交但尚未指定核稿人的场景
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: GET `/api/review/to-be-start-list` (ReviewControllerFixed@getToBeStartList)
     *
     * 请求参数:
     * - `limit` 分页大小，默认 10
     * - `page` 当前页，默认 1
     *
     * 返回参数:
     * - `success` 布尔
     * - `data.list` 列表项（含 `id`、`serialNo`、`projectNumber`、`caseName`、`applicationNo`、`customerName`、`techLead`、`processItem`、`applicationType`、`assignDate`、`internalDeadline`、`officialDeadline`、`customerDeadline`、`processor`、`reviewer`、`statusTime`）
     * - `data.total`、`data.currentPage`、`data.pageSize`
     *
     * 内部说明:
     * - 使用多表 left join 获取所需显示字段
     * - 过滤 `process_status = STATUS_PENDING` 且 `assigned_to`、`reviewer` 非空
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: GET `/api/review/in-review-list` (ReviewControllerFixed@getInReviewList)
     *
     * 请求参数:
     * - `limit` 分页大小，默认 10
     * - `page` 当前页，默认 1
     *
     * 返回参数:
     * - `success` 布尔
     * - `data.list` 列表项（含 `id`、`serialNo`、`projectNumber`、`caseName`、`applicationNo`、`customerName`、`techLead`、`processItem`、`applicationType`、`assignDate`、`internalDeadline`、`officialDeadline`、`customerDeadline`、`processor`、`reviewer`、`statusTime`）
     * - `data.total`、`data.currentPage`、`data.pageSize`
     *
     * 内部说明:
     * - 过滤 `process_status = STATUS_IN_PROGRESS` 且 `assigned_to`、`reviewer` 非空
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: GET `/api/review/completed-list` (ReviewControllerFixed@getCompletedList)
     *
     * 请求参数:
     * - `limit` 分页大小，默认 10
     * - `page` 当前页，默认 1
     *
     * 返回参数:
     * - `success` 布尔
     * - `data.list` 列表项（在常规字段基础上包含 `completionDate` 完成日期）
     * - `data.total`、`data.currentPage`、`data.pageSize`
     *
     * 内部说明:
     * - 过滤 `process_status = STATUS_COMPLETED`，按 `completion_date` 倒序
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: GET `/api/review/detail/{id}` (ReviewControllerFixed@getReviewDetail)
     *
     * 路径参数:
     * - `id` 处理事项 ID
     *
     * 返回参数:
     * - `success` 布尔
     * - `data` 对象，包含案件/客户/处理事项详情，如 `caseNumber`、`proposalName`、`clientName`、`caseName`、`applicationType`、`businessStaff`、`applicationNo`、`registrationNo`、`customerDocNo`、`trademarkCategory`、`processItem`、`processor`、`reviewer`、`internalDeadline`、`officialDeadline`、`customerDeadline`、`processStatus`、`statusText`、`caseNotes`、`processDescription`、`createdAt`、`updatedAt`、`completionDate` 等
     *
     * 内部说明:
     * - 通过 Eloquent 关联获取相关信息，如不存在返回 404
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: POST `/api/review/transfer` (ReviewControllerFixed@transferProcess)
     *
     * 请求参数:
     * - `process_id` 处理事项 ID
     * - `next_status` 下一状态（CaseProcess 状态常量之一）
     * - `notes` 流转备注，默认空
     *
     * 返回参数:
     * - `success` 布尔
     * - `message` 文本（如“流转成功”）
     *
     * 内部说明:
     * - 更新 `process_status`、`process_description` 与 `updated_at`
     * - 若进入 `STATUS_COMPLETED`，记录 `completion_date`
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
     *
     * 接口:
     * - 暂未绑定路由；替代接口: POST `/api/review/return` (ReviewControllerFixed@returnProcess)
     *
     * 请求参数:
     * - `process_id` 处理事项 ID
     * - `reason` 退回原因，可空
     *
     * 返回参数:
     * - `success` 布尔
     * - `message` 文本（如“退回成功”）
     *
     * 内部说明:
     * - 将状态置为 `STATUS_DRAFT` 并记录退回原因到 `process_description`
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
