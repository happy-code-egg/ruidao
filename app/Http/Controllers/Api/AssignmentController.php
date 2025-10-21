<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
     * 获取新申请待分配列表
     */
    public function newApplications(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);
            $showFrozen = $request->input('show_frozen', false);

            // 查询新申请类型的处理事项（未分配的）
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) {
                // 只查询专利、商标、版权类型的案例
                $caseQuery->whereIn('case_type', [
                    Cases::TYPE_PATENT,
                    Cases::TYPE_TRADEMARK, 
                    Cases::TYPE_COPYRIGHT
                ]);
            })
            ->where('process_name', 'like', '%新申请%') // 新申请类型的处理事项
            ->whereNull('assigned_to'); // 未分配的

            // 是否显示冻结事项 (暂时跳过，因为表中没有is_frozen字段)
            // if (!$showFrozen) {
            //     $query->where('is_frozen', '!=', 1);
            // }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'caseNumber' => $case->case_code ?? '',
                    'caseTitle' => $case->case_name ?? '',
                    'caseType' => $case->type_text,
                    'customerName' => $customer ? $customer->customer_name : '',
                    'applyType' => $case->application_type ?? '',
                    'processItem' => $process->process_name ?? '',
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'contractNo' => $case->contract_number ?? '',
                    'createDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'support' => $case->presale_support ?? '',
                    'frozen' => '否', // 暂时硬编码，因为表中没有is_frozen字段
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
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
     * 获取中间案待分配列表
     */
    public function middleCases(Request $request)
    {
        try {
            // 获取查询参数
            $clientName = $request->input('client_name');
            $caseName = $request->input('case_name');
            $projectName = $request->input('project_name');
            $ourRefNumber = $request->input('our_ref_number');
            $applicationNo = $request->input('application_no');
            $officialDeadlineRange = $request->input('official_deadline_range', []);
            $processingCreateDateRange = $request->input('processing_create_date_range', []);
            $internalDeadlineRange = $request->input('internal_deadline_range', []);
            $applicationType = $request->input('application_type');
            $processingItem = $request->input('processing_item');
            $caseType = $request->input('case_type');
            $businessType = $request->input('business_type');
            $registrationNo = $request->input('registration_no');
            $applicant = $request->input('applicant');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询中间案（非新申请）的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson', 'techLeader']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) use (
                $clientName, $caseName, $ourRefNumber, $applicationNo,
                $applicationType, $caseType, $registrationNo, $applicant
            ) {
                // 只查询专利、商标、版权类型的案例
                $caseQuery->whereIn('case_type', [
                    Cases::TYPE_PATENT,
                    Cases::TYPE_TRADEMARK, 
                    Cases::TYPE_COPYRIGHT
                ]);

                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($applicationNo) {
                    $caseQuery->where('application_no', 'like', "%{$applicationNo}%");
                }
                if ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                }
                if ($caseType) {
                    $typeMap = [
                        'patent' => Cases::TYPE_PATENT,
                        'trademark' => Cases::TYPE_TRADEMARK,
                        'copyright' => Cases::TYPE_COPYRIGHT
                    ];
                    if (isset($typeMap[$caseType])) {
                        $caseQuery->where('case_type', $typeMap[$caseType]);
                    }
                }
                if ($registrationNo) {
                    $caseQuery->where('registration_no', 'like', "%{$registrationNo}%");
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
            })
            ->where('process_name', 'not like', '%新申请%') // 排除新申请类型
            ->whereNull('assigned_to'); // 未分配的

            // 处理事项筛选
            if ($processingItem) {
                $query->where('process_name', 'like', "%{$processingItem}%");
            }

            // 业务类型筛选
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }

            // 日期范围筛选
            if (!empty($officialDeadlineRange) && count($officialDeadlineRange) == 2) {
                $query->whereBetween('official_deadline', $officialDeadlineRange);
            }
            if (!empty($processingCreateDateRange) && count($processingCreateDateRange) == 2) {
                $query->whereBetween('created_at', $processingCreateDateRange);
            }
            if (!empty($internalDeadlineRange) && count($internalDeadlineRange) == 2) {
                $query->whereBetween('internal_deadline', $internalDeadlineRange);
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'caseNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'customerName' => $customer ? $customer->customer_name : '',
                    'applicationType' => $case->application_type ?? '',
                    'processingItem' => $process->process_name ?? '',
                    'caseType' => $case->type_text,
                    'businessType' => $process->process_type ?? '',
                    'applicationNo' => $case->application_no ?? '',
                    'registrationNo' => $case->registration_no ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'officialDeadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                    'internalDeadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                    'processingCreateDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
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
     * 获取科服待分配列表
     */
    public function techServiceCases(Request $request)
    {
        try {
            // 获取查询参数
            $ourRefNumber = $request->input('our_ref_number');
            $caseName = $request->input('case_name');
            $clientName = $request->input('client_name');
            $businessType = $request->input('business_type');
            $applicationType = $request->input('application_type');
            $applicant = $request->input('applicant');
            $projectProcessor = $request->input('project_processor');
            $receivingDateRange = $request->input('receiving_date_range', []);
            $caseBusinessStaff = $request->input('case_business_staff');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询科技服务类型的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereHas('case', function($caseQuery) use (
                $ourRefNumber, $caseName, $clientName, $applicant, $caseBusinessStaff
            ) {
                // 只查询科技服务类型的案例
                $caseQuery->where('case_type', Cases::TYPE_TECH_SERVICE);

                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
                if ($caseBusinessStaff) {
                    $caseQuery->whereHas('businessPerson', function($businessQuery) use ($caseBusinessStaff) {
                        $businessQuery->where('name', 'like', "%{$caseBusinessStaff}%");
                    });
                }
            })
            ->whereNull('assigned_to'); // 未分配的

            // 业务类型筛选
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }

            // 申请类型筛选
            if ($applicationType) {
                $query->whereHas('case', function($caseQuery) use ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                });
            }

            // 项目处理人筛选
            if ($projectProcessor) {
                $query->where('assigned_to', $projectProcessor);
            }

            // 收单日期范围筛选
            if (!empty($receivingDateRange) && count($receivingDateRange) == 2) {
                $query->whereBetween('created_at', $receivingDateRange);
            }

            // 排序
            $query->orderBy('created_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'ourRefNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'clientName' => $customer ? $customer->customer_name : '',
                    'businessType' => $process->process_type ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'projectProcessor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'receivingDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'caseBusinessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
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
     * 批量分配处理事项
     */
    public function batchAssign(Request $request)
    {
        try {
            $processIds = $request->input('process_ids', []);
            $assignedTo = $request->input('assigned_to');
            $reviewer = $request->input('reviewer');

            if (empty($processIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要分配的事项'
                ], 400);
            }

            if (!$assignedTo) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择处理人'
                ], 400);
            }

            DB::beginTransaction();

            // 更新处理事项的分配信息
            CaseProcess::whereIn('id', $processIds)->update([
                'assigned_to' => $assignedTo,
                'reviewer' => $reviewer,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_at' => now(),
                'updated_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '分配成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '分配失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 直接分配（单个分配）
     */
    public function directAssign(Request $request)
    {
        try {
            $processId = $request->input('process_id');
            $assignedTo = $request->input('assigned_to');
            $reviewer = $request->input('reviewer');

            if (!$processId || !$assignedTo) {
                return response()->json([
                    'success' => false,
                    'message' => '参数不完整'
                ], 400);
            }

            $process = CaseProcess::find($processId);
            if (!$process) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            // 更新分配信息
            $process->update([
                'assigned_to' => $assignedTo,
                'reviewer' => $reviewer,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_by' => auth()->id() ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => '分配成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '分配失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 撤回分配
     */
    public function withdrawAssignment(Request $request)
    {
        try {
            $processIds = $request->input('process_ids', []);

            if (empty($processIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '请选择要撤回的事项'
                ], 400);
            }

            DB::beginTransaction();

            // 清除分配信息
            CaseProcess::whereIn('id', $processIds)->update([
                'assigned_to' => null,
                'reviewer' => null,
                'process_status' => CaseProcess::STATUS_PENDING,
                'updated_at' => now(),
                'updated_by' => auth()->id() ?? 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '撤回成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '撤回失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取已分配列表
     */
    public function assignedCases(Request $request)
    {
        try {
            // 获取查询参数
            $ourRefNumber = $request->input('our_ref_number');
            $caseName = $request->input('case_name');
            $clientName = $request->input('client_name');
            $businessType = $request->input('business_type');
            $applicationType = $request->input('application_type');
            $applicant = $request->input('applicant');
            $projectProcessor = $request->input('project_processor');
            $receivingDateRange = $request->input('receiving_date_range', []);
            $caseBusinessStaff = $request->input('case_business_staff');

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);

            // 查询已分配的处理事项
            $query = CaseProcess::with([
                'case' => function($q) {
                    $q->with(['customer', 'businessPerson']);
                },
                'assignedUser',
                'reviewerUser'
            ])
            ->whereNotNull('assigned_to'); // 已分配的

            // 应用筛选条件
            $query->whereHas('case', function($caseQuery) use (
                $ourRefNumber, $caseName, $clientName, $applicant, $caseBusinessStaff
            ) {
                if ($ourRefNumber) {
                    $caseQuery->where('case_code', 'like', "%{$ourRefNumber}%");
                }
                if ($caseName) {
                    $caseQuery->where('case_name', 'like', "%{$caseName}%");
                }
                if ($clientName) {
                    $caseQuery->whereHas('customer', function($customerQuery) use ($clientName) {
                        $customerQuery->where('customer_name', 'like', "%{$clientName}%");
                    });
                }
                if ($applicant) {
                    $caseQuery->whereJsonContains('applicant_info', ['name' => $applicant])
                             ->orWhere('applicant_info', 'like', "%{$applicant}%");
                }
                if ($caseBusinessStaff) {
                    $caseQuery->whereHas('businessPerson', function($businessQuery) use ($caseBusinessStaff) {
                        $businessQuery->where('name', 'like', "%{$caseBusinessStaff}%");
                    });
                }
            });

            // 其他筛选条件
            if ($businessType) {
                $query->where('process_type', 'like', "%{$businessType}%");
            }
            if ($applicationType) {
                $query->whereHas('case', function($caseQuery) use ($applicationType) {
                    $caseQuery->where('application_type', 'like', "%{$applicationType}%");
                });
            }
            if ($projectProcessor) {
                $query->where('assigned_to', $projectProcessor);
            }
            if (!empty($receivingDateRange) && count($receivingDateRange) == 2) {
                $query->whereBetween('created_at', $receivingDateRange);
            }

            // 排序
            $query->orderBy('updated_at', 'desc');

            // 分页
            $processes = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($processes->items() as $process) {
                $case = $process->case;
                $customer = $case->customer;

                $tableData[] = [
                    'id' => $process->id,
                    'ourRefNumber' => $case->case_code ?? '',
                    'caseName' => $case->case_name ?? '',
                    'clientName' => $customer ? $customer->customer_name : '',
                    'businessType' => $process->process_type ?? '',
                    'applicationType' => $case->application_type ?? '',
                    'applicant' => $this->getApplicantName($case->applicant_info),
                    'projectProcessor' => $process->assignedUser ? $process->assignedUser->name : '',
                    'reviewer' => $process->reviewerUser ? $process->reviewerUser->name : '',
                    'receivingDate' => $process->created_at ? $process->created_at->format('Y-m-d') : '',
                    'assignmentDate' => $process->updated_at ? $process->updated_at->format('Y-m-d') : '',
                    'caseBusinessStaff' => $case->businessPerson ? $case->businessPerson->name : '',
                    'assignmentStatus' => $this->getAssignmentStatus($process),
                    'processStatus' => $process->status_text,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $tableData,
                    'total' => $processes->total(),
                    'current_page' => $processes->currentPage(),
                    'per_page' => $processes->perPage(),
                    'last_page' => $processes->lastPage(),
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
     * 获取处理事项详情
     */
    public function getProcessDetail($id)
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
                'process_code' => $process->process_code,
                'process_name' => $process->process_name,
                'process_type' => $process->process_type,
                'process_status' => $process->process_status,
                'priority_level' => $process->priority_level,
                'case_stage' => $process->case_stage,
                'due_date' => $process->due_date ? $process->due_date->format('Y-m-d') : '',
                'internal_deadline' => $process->internal_deadline ? $process->internal_deadline->format('Y-m-d') : '',
                'official_deadline' => $process->official_deadline ? $process->official_deadline->format('Y-m-d') : '',
                'completion_date' => $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
                'issue_date' => $process->issue_date ? $process->issue_date->format('Y-m-d') : '',
                'assigned_to' => $process->assigned_to,
                'assignee' => $process->assignee,
                'reviewer' => $process->reviewer,
                'processor' => $process->assignedUser ? $process->assignedUser->name : '',
                'reviewer_name' => $process->reviewerUser ? $process->reviewerUser->name : '',
                'process_coefficient' => $process->process_coefficient,
                'process_description' => $process->process_description,
                'created_at' => $process->created_at ? $process->created_at->format('Y-m-d H:i:s') : '',
                'updated_at' => $process->updated_at ? $process->updated_at->format('Y-m-d H:i:s') : '',
                // 案件信息
                'case' => [
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_name' => $case->case_name,
                    'case_type' => $case->case_type,
                    'type_text' => $case->type_text,
                    'case_status' => $case->case_status,
                    'application_type' => $case->application_type,
                    'application_no' => $case->application_no,
                    'application_date' => $case->application_date ? $case->application_date->format('Y-m-d') : '',
                    'country_code' => $case->country_code,
                    'case_phase' => $case->case_phase,
                    'applicant_info' => $case->applicant_info ? json_decode($case->applicant_info, true) : null,
                    'contract_number' => $case->contract_number,
                    'presale_support' => $case->presale_support,
                    'business_person_id' => $case->business_person_id,
                    'tech_leader' => $case->tech_leader
                ],
                // 客户信息
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'customer_name' => $customer->customer_name,
                    'customer_code' => $customer->customer_code,
                    'contact_person' => $customer->contact_person,
                    'contact_phone' => $customer->contact_phone,
                    'contact_email' => $customer->contact_email,
                    'address' => $customer->address
                ] : null,
                'assignment_status' => $this->getAssignmentStatus($process)
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
     * 获取可分配的用户列表
     */
    public function getAssignableUsers(Request $request)
    {
        try {
            $users = User::where('status', 1)
                        ->select('id', 'real_name', 'email', 'department_id')
                        ->orderBy('real_name')
                        ->get();

        // 转换为前端需要的格式
        $users = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->real_name,
                'email' => $user->email,
                'department' => $user->department_id
            ];
        });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取用户列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取分配状态
     */
    private function getAssignmentStatus($process)
    {
        if (is_null($process->assigned_to)) {
            return '未分配';
        } elseif ($process->process_status == CaseProcess::STATUS_PENDING) {
            return '已分配';
        } elseif ($process->process_status == CaseProcess::STATUS_PROCESSING) {
            return '处理中';
        } elseif ($process->process_status == CaseProcess::STATUS_COMPLETED) {
            return '已完成';
        } else {
            return '未知状态';
        }
    }

    /**
     * 获取申请人名称
     */
    private function getApplicantName($applicantInfo)
    {
        if (is_string($applicantInfo)) {
            return $applicantInfo;
        }
        
        if (is_array($applicantInfo) && isset($applicantInfo['name'])) {
            return $applicantInfo['name'];
        }
        
        if (is_array($applicantInfo) && !empty($applicantInfo)) {
            $first = reset($applicantInfo);
            if (is_array($first) && isset($first['name'])) {
                return $first['name'];
            }
        }
        
        return '';
    }
}
