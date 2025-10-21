<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewControllerFixed extends Controller
{
    /**
     * 获取待提交（草稿）列表
     * 状态0：文件还在撰写中，未提交核稿
     */
    public function getDraftList(Request $request)
    {
        return $this->getProcessList($request, 0, '草稿');
    }

    /**
     * 获取待开始列表
     * 状态1：已提交，等待开始核稿
     */
    public function getToBeStartList(Request $request)
    {
        return $this->getProcessList($request, 1, '待开始');
    }

    /**
     * 获取待处理列表（兼容旧接口，实际使用待开始）
     * 已废弃，请使用 getToBeStartList
     */
    public function getPendingList(Request $request)
    {
        return $this->getToBeStartList($request);
    }

    /**
     * 获取审核中列表
     * 状态2：正在进行核稿审核
     */
    public function getInReviewList(Request $request)
    {
        return $this->getProcessList($request, 2, '审核中');
    }

    /**
     * 获取审核完成列表
     * 状态3：核稿审核已完成
     */
    public function getCompletedList(Request $request)
    {
        return $this->getProcessList($request, 3, '审核完成');
    }

    /**
     * 通用的获取处理事项列表方法
     */
    private function getProcessList(Request $request, $status, $statusName)
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = DB::table('case_processes as cp')
                ->leftJoin('cases as c', 'cp.case_id', '=', 'c.id')
                ->leftJoin('customers as cust', 'c.customer_id', '=', 'cust.id')
                ->leftJoin('users as u1', 'cp.assigned_to', '=', 'u1.id')
                ->leftJoin('users as u2', 'cp.reviewer', '=', 'u2.id')
                ->where('cp.process_status', $status)
                ->select(
                    'cp.id',
                    'cp.process_name',
                    'cp.created_at',
                    'cp.updated_at',
                    'cp.internal_deadline',
                    'cp.official_deadline',
                    'cp.due_date',
                    'cp.completion_date',
                    'c.case_code',
                    'c.case_name',
                    'c.application_no',
                    'c.application_type',
                    'cust.customer_name',
                    'u1.real_name as processor_name',
                    'u2.real_name as reviewer_name'
                )
                ->orderBy('cp.created_at', 'desc');

            // 计算总数
            $total = $query->count();
            
            // 分页
            $offset = ($page - 1) * $limit;
            $records = $query->offset($offset)->limit($limit)->get();

            $data = [];
            foreach ($records as $index => $record) {
                $data[] = [
                    'id' => $record->id,
                    'serialNo' => $offset + $index + 1,
                    'projectNumber' => $record->case_code ?? '',
                    'caseName' => $record->case_name ?? '',
                    'applicationNo' => $record->application_no ?? '',
                    'customerName' => $record->customer_name ?? '',
                    'techLead' => $record->processor_name ?? '',
                    'processItem' => $record->process_name ?? '',
                    'applicationType' => $record->application_type ?? '',
                    'assignDate' => $record->created_at ? date('Y-m-d', strtotime($record->created_at)) : '',
                    'internalDeadline' => $record->internal_deadline ?? '',
                    'officialDeadline' => $record->official_deadline ?? '',
                    'customerDeadline' => $record->due_date ?? '',
                    'processor' => $record->processor_name ?? '',
                    'reviewer' => $record->reviewer_name ?? '',
                    'statusTime' => $record->updated_at ? date('Y-m-d H:i', strtotime($record->updated_at)) : '',
                    'completionDate' => $record->completion_date ?? ''
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $data,
                    'total' => $total,
                    'currentPage' => $page,
                    'pageSize' => $limit,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "查询{$statusName}数据失败：" . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取核稿详情
     */
    public function getReviewDetail($id)
    {
        try {
            $record = DB::table('case_processes as cp')
                ->leftJoin('cases as c', 'cp.case_id', '=', 'c.id')
                ->leftJoin('customers as cust', 'c.customer_id', '=', 'cust.id')
                ->leftJoin('users as u1', 'cp.assigned_to', '=', 'u1.id')
                ->leftJoin('users as u2', 'cp.reviewer', '=', 'u2.id')
                ->where('cp.id', $id)
                ->select(
                    'cp.*',
                    'c.*',
                    'cust.customer_name',
                    'u1.real_name as processor_name',
                    'u2.real_name as reviewer_name'
                )
                ->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            $data = [
                'id' => $record->id,
                'caseNumber' => $record->case_code ?? '',
                'proposalName' => $record->proposal_name ?? '',
                'clientName' => $record->customer_name ?? '',
                'caseName' => $record->case_name ?? '',
                'applicationType' => $record->application_type ?? '',
                'businessStaff' => $record->business_person_name ?? '',
                'applicationNo' => $record->application_no ?? '',
                'registrationNo' => $record->registration_no ?? '',
                'customerDocNo' => $record->customer_doc_no ?? '',
                'trademarkCategory' => $record->trademark_category ?? '',
                'processItem' => $record->process_name ?? '',
                'processor' => $record->processor_name ?? '',
                'reviewer' => $record->reviewer_name ?? '',
                'internalDeadline' => $record->internal_deadline ?? '',
                'officialDeadline' => $record->official_deadline ?? '',
                'customerDeadline' => $record->due_date ?? '',
                'processStatus' => $record->process_status,
                'statusText' => $this->getStatusText($record->process_status),
                'caseNotes' => $record->case_notes ?? '',
                'processDescription' => $record->process_description ?? '',
                'createdAt' => $record->created_at ?? '',
                'updatedAt' => $record->updated_at ?? '',
                'completionDate' => $record->completion_date ?? ''
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
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $statusMap = [
            0 => '待提交（草稿）',
            1 => '待开始',
            2 => '审核中',
            3 => '审核完成'
        ];

        return $statusMap[$status] ?? '未知状态';
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

            DB::table('case_processes')
                ->where('id', $processId)
                ->update([
                    'process_status' => $nextStatus,
                    'process_description' => $notes,
                    'updated_at' => now(),
                    'completion_date' => $nextStatus == 3 ? now() : null
                ]);

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

            DB::table('case_processes')
                ->where('id', $processId)
                ->update([
                    'process_status' => 0, // 退回到草稿状态
                    'process_description' => "退回原因：" . $reason,
                    'updated_at' => now()
                ]);

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
