<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PersonalCaseController extends Controller
{
    /**
     * 获取个人项目首页数据
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            
            // 获取查询参数
            $caseType = $request->input('case_type', '专利');
            $internalDeadline = $request->input('internal_deadline');
            $officialDeadline = $request->input('official_deadline');
            $processStatus = $request->input('process_status');
            $processItem = $request->input('process_item');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询
            $query = Cases::with(['processes' => function($q) use ($userId) {
                $q->where('assigned_to', $userId);
            }])->whereHas('processes', function($q) use ($userId) {
                $q->where('assigned_to', $userId);
            });

            // 根据项目类型筛选
            if ($caseType) {
                $typeMap = [
                    '专利' => Cases::TYPE_PATENT,
                    '商标' => Cases::TYPE_TRADEMARK, 
                    '版权' => Cases::TYPE_COPYRIGHT,
                    '科服' => Cases::TYPE_TECH_SERVICE
                ];
                if (isset($typeMap[$caseType])) {
                    $query->where('case_type', $typeMap[$caseType]);
                }
            }

            // 获取分页数据
            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $process = $case->processes->first();
                $tableData[] = [
                    'index' => ($page - 1) * $limit + $index + 1,
                    'id' => $case->id,
                    'officialNo' => $case->official_number ?: 'GF' . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'caseName' => $case->case_name,
                    'applicationNo' => $case->application_number ?: 'SQ' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'applicationType' => $case->getTypeTextAttribute(),
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'processStatus' => $process ? $process->process_status : '待处理',
                    'processDeadline' => $process && $process->expected_complete_date ? Carbon::parse($process->expected_complete_date)->format('Y-m-d') : '',
                    'officialDeadline' => $case->official_deadline ? Carbon::parse($case->official_deadline)->format('Y-m-d') : '',
                    'internalDeadline' => $case->internal_deadline ? Carbon::parse($case->internal_deadline)->format('Y-m-d') : '',
                    'planFinishDate' => $process && $process->expected_complete_date ? Carbon::parse($process->expected_complete_date)->format('Y-m-d') : '',
                    'processRemark' => $process ? $process->process_remark : '',
                    'caseRemark' => $case->remark
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'mock_data' => [
                        'tableData' => $tableData,
                        'total' => $cases->total()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取待处理项目列表（商标专利版权）
     */
    public function pending(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $caseTypes = $request->input('case_type', '专利');
            $internalDeadlines = $request->input('internal_deadline');
            $officialDeadlines = $request->input('official_deadline');
            $processStatus = $request->input('process_status');
            $processItems = $request->input('process_items');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询 - 获取当前用户自己的项目（待处理状态）
            $query = Cases::where('created_by', $userId)
                          ->whereNotIn('case_status', [
                              Cases::STATUS_COMPLETED, // 已完成
                              Cases::STATUS_AUTHORIZED, // 已授权（相当于已结案）
                              Cases::STATUS_ARCHIVED   // 已归档
                          ]);

            // 限制为商标专利版权类型
            $query->whereIn('case_type', [
                Cases::TYPE_PATENT,
                Cases::TYPE_TRADEMARK,
                Cases::TYPE_COPYRIGHT
            ]);

            // 根据选择的类型筛选
            if ($caseTypes) {
                $typeMap = [
                    '专利' => Cases::TYPE_PATENT,
                    '商标' => Cases::TYPE_TRADEMARK,
                    '版权' => Cases::TYPE_COPYRIGHT
                ];
                if (isset($typeMap[$caseTypes])) {
                    $query->where('case_type', $typeMap[$caseTypes]);
                }
            }

            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $data = [
                    'id' => $case->id,
                    'index' => ($page - 1) * $limit + $index + 1,
                    'ourRefNumber' => $case->case_code ?: 'ZCN' . str_pad($case->id, 8, '0', STR_PAD_LEFT),
                    'caseName' => $case->case_name ?: '未命名项目',
                    'applicationNo' => $case->application_no ?: 'SQ' . date('Y') . str_pad($case->id, 8, '0', STR_PAD_LEFT),
                    'applicationType' => $case->getTypeTextAttribute(),
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'processStatus' => $case->case_status ?: '待处理',
                    'processItem' => '新申请', // 默认处理事项
                    'technicalManager' => '李工程师', // 默认技术负责人
                    'estimatedCompleteDate' => $case->estimated_completion_date ? Carbon::parse($case->estimated_completion_date)->format('Y-m-d') : null,
                    'processRemark' => $case->remarks ?: '',
                    'officialDeadline' => $case->deadline_date ? Carbon::parse($case->deadline_date)->format('Y-m-d') : '',
                    'internalDeadline' => $case->internal_deadline ? Carbon::parse($case->internal_deadline)->format('Y-m-d') : ''
                ];

                // 根据项目类型添加特定字段
                if ($caseTypes === '专利') {
                    $data = array_merge($data, [
                        'inventorName' => $case->inventor_name ?: '发明人' . $case->id,
                        'patentType' => $case->patent_type ?: '发明专利',
                        'applicationDate' => $case->application_date ? Carbon::parse($case->application_date)->format('Y-m-d') : '',
                        'publicationNumber' => $case->publication_number ?: 'CN' . date('Y') . 'A',
                        'publicationDate' => $case->publication_date ? Carbon::parse($case->publication_date)->format('Y-m-d') : ''
                    ]);
                } elseif ($caseTypes === '商标') {
                    $data = array_merge($data, [
                        'trademarkName' => $case->trademark_name ?: $case->case_name,
                        'trademarkClass' => $case->trademark_class ?: '35',
                        'registrationNumber' => $case->registration_number ?: 'TM' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                        'trademarkStatus' => $case->trademark_status ?: '申请中'
                    ]);
                } elseif ($caseTypes === '版权') {
                    $data = array_merge($data, [
                        'workName' => $case->work_name ?: $case->case_name,
                        'workType' => $case->work_type ?: '文字作品',
                        'author' => $case->author ?: '作者' . $case->id,
                        'copyrightNumber' => $case->copyright_number ?: 'CR' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT)
                    ]);
                }

                $tableData[] = $data;
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $tableData,
                    'total' => $cases->total(),
                    'current_page' => $cases->currentPage(),
                    'per_page' => $cases->perPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取待处理项目列表（科技服务）
     */
    public function pendingProject(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $ourRefNumber = $request->input('our_ref_number');
            $customerName = $request->input('customer_name');
            $applicationBatch = $request->input('application_batch');
            $applicationType = $request->input('application_type');
            $businessType = $request->input('business_type');
            $projectYear = $request->input('project_year');
            $managementArea = $request->input('management_area');
            $caseStatus = $request->input('case_status');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询 - 获取当前用户自己的科技服务项目（待处理状态）
            $query = Cases::where('created_by', $userId)
                          ->where('case_type', Cases::TYPE_TECH_SERVICE)
                          ->whereNotIn('case_status', [
                              Cases::STATUS_COMPLETED, // 已完成
                              Cases::STATUS_AUTHORIZED, // 已授权（相当于已结案）
                              Cases::STATUS_ARCHIVED   // 已归档
                          ]);

            // 应用筛选条件
            if ($ourRefNumber) {
                $query->where('our_ref_number', 'like', "%{$ourRefNumber}%");
            }
            if ($customerName) {
                $query->where('customer_name', 'like', "%{$customerName}%");
            }
            if ($applicationBatch) {
                $query->where('application_batch', 'like', "%{$applicationBatch}%");
            }
            if ($applicationType) {
                $query->where('application_type', $applicationType);
            }
            if ($businessType) {
                $query->where('business_type', 'like', "%{$businessType}%");
            }
            if ($managementArea) {
                $query->where('management_area', 'like', "%{$managementArea}%");
            }
            if ($caseStatus) {
                // 将中文状态名映射为数字状态码
                $statusMap = [
                    '草稿' => Cases::STATUS_DRAFT,
                    '待立项' => Cases::STATUS_TO_BE_FILED,
                    '已提交' => Cases::STATUS_SUBMITTED,
                    '处理中' => Cases::STATUS_PROCESSING,
                    '已授权' => Cases::STATUS_AUTHORIZED,
                    '已驳回' => Cases::STATUS_REJECTED,
                    '已完成' => Cases::STATUS_COMPLETED,
                    '已归档' => Cases::STATUS_ARCHIVED
                ];
                if (isset($statusMap[$caseStatus])) {
                    $query->where('case_status', $statusMap[$caseStatus]);
                } else {
                    // 如果传入的是数字，直接使用
                    if (is_numeric($caseStatus)) {
                        $query->where('case_status', intval($caseStatus));
                    }
                }
            }

            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $currentProcess = $case->processes->first();
                
                $tableData[] = [
                    'id' => $case->id,
                    'index' => ($page - 1) * $limit + $index + 1,
                    'ourRefNumber' => $case->our_ref_number ?: 'KF' . date('Y') . str_pad($case->id, 4, '0', STR_PAD_LEFT),
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'applicationBatch' => $case->application_batch ?: date('Y') . '批次' . ($case->id % 10 + 1),
                    'applicationType' => $case->application_type ?: '项目申报',
                    'businessType' => $case->business_type ?: '科技服务',
                    'projectYear' => $case->project_year ?: date('Y'),
                    'managementArea' => $case->management_area ?: '本地',
                    'caseStatus' => $case->case_status ?: '进行中',
                    'processItem' => $currentProcess ? $currentProcess->process_item : '项目启动',
                    'itemHandler' => $currentProcess && $currentProcess->assignedUser ? $currentProcess->assignedUser->real_name : '李明',
                    'previewDeadline' => $currentProcess && $currentProcess->expected_complete_date ? Carbon::parse($currentProcess->expected_complete_date)->format('Y-m-d') : null,
                    'processRemark' => $currentProcess ? $currentProcess->process_remark : ''
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $tableData,
                    'total' => $cases->total(),
                    'current_page' => $cases->currentPage(),
                    'per_page' => $cases->perPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取已完成项目列表（商标专利版权）
     */
    public function completed(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $caseTypes = $request->input('case_type', '专利');
            $internalDeadlines = $request->input('internal_deadline');
            $officialDeadlines = $request->input('official_deadline');
            $processStatus = $request->input('process_status');
            $processItems = $request->input('process_items');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询 - 获取当前用户自己的已完成项目
            $query = Cases::where('created_by', $userId)
                          ->whereIn('case_status', [
                              Cases::STATUS_COMPLETED, // 已完成
                              Cases::STATUS_AUTHORIZED, // 已授权（相当于已结案）
                              Cases::STATUS_ARCHIVED   // 已归档
                          ]);

            // 限制为商标专利版权类型
            $query->whereIn('case_type', [
                Cases::TYPE_PATENT,
                Cases::TYPE_TRADEMARK,
                Cases::TYPE_COPYRIGHT
            ]);

            // 根据选择的类型筛选
            if ($caseTypes) {
                $typeMap = [
                    '专利' => Cases::TYPE_PATENT,
                    '商标' => Cases::TYPE_TRADEMARK,
                    '版权' => Cases::TYPE_COPYRIGHT
                ];
                if (isset($typeMap[$caseTypes])) {
                    $query->where('case_type', $typeMap[$caseTypes]);
                }
            }

            // 日期范围筛选
            if ($startDate && $endDate) {
                $query->whereHas('processes', function($q) use ($startDate, $endDate, $userId) {
                    $q->where('assigned_to', $userId)
                      ->where('process_status', CaseProcess::STATUS_COMPLETED)
                      ->whereBetween('completion_date', [$startDate, $endDate]);
                });
            }

            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $completedProcess = $case->processes->first();
                
                $data = [
                    'id' => $case->id,
                    'index' => ($page - 1) * $limit + $index + 1,
                    'officialNo' => $case->official_number ?: 'GF' . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'caseName' => $case->case_name,
                    'applicationNo' => $case->application_number ?: 'SQ' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'applicationType' => $case->getTypeTextAttribute(),
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'processStatus' => '已完成',
                    'processItem' => $completedProcess ? $completedProcess->process_item : '新申请',
                    'itemHandler' => $completedProcess && $completedProcess->assignedUser ? $completedProcess->assignedUser->real_name : '钱七',
                    'completionDate' => $completedProcess && $completedProcess->completion_date ? Carbon::parse($completedProcess->completion_date)->format('Y-m-d') : '',
                    'planFinishDate' => $completedProcess && $completedProcess->expected_complete_date ? Carbon::parse($completedProcess->expected_complete_date)->format('Y-m-d') : null,
                    'processRemark' => $completedProcess ? $completedProcess->process_remark : ''
                ];

                // 根据项目类型添加特定字段
                if ($caseTypes === '专利') {
                    $data = array_merge($data, [
                        'inventorName' => $case->inventor_name ?: '发明人' . $case->id,
                        'patentType' => $case->patent_type ?: '发明专利',
                        'applicationDate' => $case->application_date ? Carbon::parse($case->application_date)->format('Y-m-d') : '',
                        'publicationNumber' => $case->publication_number ?: 'CN' . date('Y') . 'A',
                        'publicationDate' => $case->publication_date ? Carbon::parse($case->publication_date)->format('Y-m-d') : ''
                    ]);
                } elseif ($caseTypes === '商标') {
                    $data = array_merge($data, [
                        'trademarkName' => $case->trademark_name ?: $case->case_name,
                        'trademarkClass' => $case->trademark_class ?: '35',
                        'registrationNumber' => $case->registration_number ?: 'TM' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                        'trademarkStatus' => $case->trademark_status ?: '已注册'
                    ]);
                } elseif ($caseTypes === '版权') {
                    $data = array_merge($data, [
                        'workName' => $case->work_name ?: $case->case_name,
                        'workType' => $case->work_type ?: '文字作品',
                        'author' => $case->author ?: '作者' . $case->id,
                        'copyrightNumber' => $case->copyright_number ?: 'CR' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT)
                    ]);
                }

                $tableData[] = $data;
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $tableData,
                    'total' => $cases->total(),
                    'current_page' => $cases->currentPage(),
                    'per_page' => $cases->perPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取已完成项目列表（科技服务）
     */
    public function completedProject(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $ourRefNumber = $request->input('our_ref_number');
            $customerName = $request->input('customer_name');
            $applicationBatch = $request->input('application_batch');
            $applicationType = $request->input('application_type');
            $businessType = $request->input('business_type');
            $projectYear = $request->input('project_year');
            $managementArea = $request->input('management_area');
            $caseStatus = $request->input('case_status');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询
            $query = Cases::with(['processes' => function($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->where('process_status', CaseProcess::STATUS_COMPLETED);
            }])->whereHas('processes', function($q) use ($userId) {
                $q->where('assigned_to', $userId)
                  ->where('process_status', CaseProcess::STATUS_COMPLETED);
            });

            // 限制为科技服务类型
            $query->where('case_type', Cases::TYPE_TECH_SERVICE);

            // 应用筛选条件
            if ($ourRefNumber) {
                $query->where('our_ref_number', 'like', "%{$ourRefNumber}%");
            }
            if ($customerName) {
                $query->where('customer_name', 'like', "%{$customerName}%");
            }
            if ($applicationBatch) {
                $query->where('application_batch', 'like', "%{$applicationBatch}%");
            }
            if ($applicationType) {
                $query->where('application_type', $applicationType);
            }
            if ($businessType) {
                $query->where('business_type', 'like', "%{$businessType}%");
            }
            if ($managementArea) {
                $query->where('management_area', 'like', "%{$managementArea}%");
            }
            if ($caseStatus) {
                // 将中文状态名映射为数字状态码
                $statusMap = [
                    '草稿' => Cases::STATUS_DRAFT,
                    '待立项' => Cases::STATUS_TO_BE_FILED,
                    '已提交' => Cases::STATUS_SUBMITTED,
                    '处理中' => Cases::STATUS_PROCESSING,
                    '已授权' => Cases::STATUS_AUTHORIZED,
                    '已驳回' => Cases::STATUS_REJECTED,
                    '已完成' => Cases::STATUS_COMPLETED,
                    '已归档' => Cases::STATUS_ARCHIVED
                ];
                if (isset($statusMap[$caseStatus])) {
                    $query->where('case_status', $statusMap[$caseStatus]);
                } else {
                    // 如果传入的是数字，直接使用
                    if (is_numeric($caseStatus)) {
                        $query->where('case_status', intval($caseStatus));
                    }
                }
            }

            // 日期范围筛选
            if ($startDate && $endDate) {
                $query->whereHas('processes', function($q) use ($startDate, $endDate, $userId) {
                    $q->where('assigned_to', $userId)
                      ->where('process_status', CaseProcess::STATUS_COMPLETED)
                      ->whereBetween('completion_date', [$startDate, $endDate]);
                });
            }

            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $completedProcess = $case->processes->first();
                
                $tableData[] = [
                    'id' => $case->id,
                    'index' => ($page - 1) * $limit + $index + 1,
                    'ourRefNumber' => $case->our_ref_number ?: 'KF' . date('Y') . str_pad($case->id, 4, '0', STR_PAD_LEFT),
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'applicationBatch' => $case->application_batch ?: date('Y') . '批次' . ($case->id % 10 + 1),
                    'applicationType' => $case->application_type ?: '项目申报',
                    'businessType' => $case->business_type ?: '科技服务',
                    'projectYear' => $case->project_year ?: date('Y'),
                    'managementArea' => $case->management_area ?: '本地',
                    'caseStatus' => '已完成',
                    'processItem' => $completedProcess ? $completedProcess->process_item : '项目验收',
                    'itemHandler' => $completedProcess && $completedProcess->assignedUser ? $completedProcess->assignedUser->real_name : '钱七',
                    'completionDate' => $completedProcess && $completedProcess->completion_date ? Carbon::parse($completedProcess->completion_date)->format('Y-m-d') : '',
                    'previewDeadline' => $completedProcess && $completedProcess->expected_complete_date ? Carbon::parse($completedProcess->expected_complete_date)->format('Y-m-d') : null,
                    'processRemark' => $completedProcess ? $completedProcess->process_remark : ''
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $tableData,
                    'total' => $cases->total(),
                    'current_page' => $cases->currentPage(),
                    'per_page' => $cases->perPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取部门项目列表
     */
    public function department(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::find($userId);
            $departmentId = $user->department_id;
            
            $caseType = $request->input('case_type');
            $applicationType = $request->input('application_type');
            $caseStatus = $request->input('case_status');
            $customerName = $request->input('customer_name');
            $applicant = $request->input('applicant');
            $department = $request->input('department');
            $processItemType = $request->input('process_item_type');
            $processItem = $request->input('process_item');
            $itemStatus = $request->input('item_status');
            $businessType = $request->input('business_type');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // 构建查询 - 获取同部门的所有项目（通过业务人员）
            $query = Cases::with(['processes', 'businessPerson'])->whereHas('businessPerson', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });

            // 应用筛选条件
            if ($caseType) {
                $typeMap = [
                    '专利' => Cases::TYPE_PATENT,
                    '商标' => Cases::TYPE_TRADEMARK,
                    '版权' => Cases::TYPE_COPYRIGHT,
                    '科服' => Cases::TYPE_TECH_SERVICE
                ];
                if (isset($typeMap[$caseType])) {
                    $query->where('case_type', $typeMap[$caseType]);
                }
            }
            if ($applicationType) {
                $query->where('application_type', 'like', "%{$applicationType}%");
            }
            if ($caseStatus) {
                // 将中文状态名映射为数字状态码
                $statusMap = [
                    '草稿' => Cases::STATUS_DRAFT,
                    '待立项' => Cases::STATUS_TO_BE_FILED,
                    '已提交' => Cases::STATUS_SUBMITTED,
                    '处理中' => Cases::STATUS_PROCESSING,
                    '已授权' => Cases::STATUS_AUTHORIZED,
                    '已驳回' => Cases::STATUS_REJECTED,
                    '已完成' => Cases::STATUS_COMPLETED,
                    '已归档' => Cases::STATUS_ARCHIVED
                ];
                if (isset($statusMap[$caseStatus])) {
                    $query->where('case_status', $statusMap[$caseStatus]);
                } else {
                    // 如果传入的是数字，直接使用
                    if (is_numeric($caseStatus)) {
                        $query->where('case_status', intval($caseStatus));
                    }
                }
            }
            if ($customerName) {
                $query->where('customer_name', 'like', "%{$customerName}%");
            }
            if ($applicant) {
                $query->where('applicant_name', 'like', "%{$applicant}%");
            }

            $cases = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $tableData = [];
            foreach ($cases->items() as $index => $case) {
                $currentProcess = $case->processes->first();
                
                $tableData[] = [
                    'id' => $case->id,
                    'index' => ($page - 1) * $limit + $index + 1,
                    'caseType' => $case->getTypeTextAttribute(),
                    'officialNo' => $case->official_number ?: 'GF' . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'caseName' => $case->case_name,
                    'applicationNo' => $case->application_number ?: 'SQ' . date('Y') . str_pad($case->id, 6, '0', STR_PAD_LEFT),
                    'applicationType' => $case->application_type ?: '发明专利',
                    'caseStatus' => $this->getCaseStatusText($case->case_status) ?: '进行中',
                    'customerName' => $case->customer_name ?: '客户' . $case->id,
                    'applicant' => $case->applicant_name ?: '申请人' . $case->id,
                    'department' => $user->department_name ?: '技术部',
                    'processItemType' => $currentProcess ? $currentProcess->process_item_type : '审查',
                    'processItem' => $currentProcess ? $currentProcess->process_item : '新申请',
                    'itemStatus' => $currentProcess ? $this->getProcessStatusText($currentProcess->process_status) : '进行中',
                    'businessType' => $case->business_type ?: '知识产权',
                    'handler' => $case->businessPerson ? $case->businessPerson->name : '李明',
                    'expectedCompleteDate' => $currentProcess && $currentProcess->expected_complete_date ? Carbon::parse($currentProcess->expected_complete_date)->format('Y-m-d') : '',
                    'processRemark' => $currentProcess ? $currentProcess->process_remark : ''
                ];
            }

            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'list' => $tableData,
                    'total' => $cases->total(),
                    'current_page' => $cases->currentPage(),
                    'per_page' => $cases->perPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取数据失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 修改预计完成时间
     */
    public function modifyEstimatedTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'selected_date' => 'required|date',
            'remark' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;
            $selectedDate = $request->selected_date;
            $remark = $request->remark;

            // 批量更新预计完成时间
            $updated = CaseProcess::whereIn('case_id', $caseIds)
                ->where('assigned_to', $userId)
                ->where('process_status', '!=', CaseProcess::STATUS_COMPLETED)
                ->update([
                    'expected_complete_date' => $selectedDate,
                    'process_remark' => $remark,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "成功修改 {$updated} 个项目的预计完成时间"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '修改失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加完成
     */
    public function addComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'selected_date' => 'required|date',
            'process_status' => 'required|string',
            'remark' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;
            $selectedDate = $request->selected_date;
            $processStatus = $request->process_status;
            $remark = $request->remark;

            // 批量标记完成
            $updated = CaseProcess::whereIn('case_id', $caseIds)
                ->where('assigned_to', $userId)
                ->where('process_status', '!=', CaseProcess::STATUS_COMPLETED)
                ->update([
                    'process_status' => CaseProcess::STATUS_COMPLETED,
                    'completion_date' => $selectedDate,
                    'process_remark' => $remark,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "成功标记 {$updated} 个项目为完成状态"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加处理事项备注
     */
    public function addProcessNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'remark' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;
            $remark = $request->remark;

            // 批量添加备注
            $updated = CaseProcess::whereIn('case_id', $caseIds)
                ->where('assigned_to', $userId)
                ->update([
                    'process_remark' => $remark,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "成功为 {$updated} 个项目添加处理备注"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动递交
     */
    public function startDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;

            // 批量启动递交流程
            $updated = 0;
            foreach ($caseIds as $caseId) {
                $process = CaseProcess::where('case_id', $caseId)
                    ->where('assigned_to', $userId)
                    ->first();
                
                if ($process) {
                    $process->process_status = CaseProcess::STATUS_IN_PROGRESS;
                    $process->process_item = '递交申请';
                    $process->updated_at = now();
                    $process->save();
                    $updated++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "成功启动 {$updated} 个项目的递交流程"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 启动核稿
     */
    public function startSupplement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;

            // 批量启动核稿流程
            $updated = 0;
            foreach ($caseIds as $caseId) {
                $process = CaseProcess::where('case_id', $caseId)
                    ->where('assigned_to', $userId)
                    ->first();
                
                if ($process) {
                    $process->process_status = CaseProcess::STATUS_IN_PROGRESS;
                    $process->process_item = '核稿审查';
                    $process->updated_at = now();
                    $process->save();
                    $updated++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "成功启动 {$updated} 个项目的核稿流程"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 新增流程跟进
     */
    public function addProcessFollow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'process_item' => 'required|string|max:255',
            'remark' => 'nullable|string|max:1000',
            'expected_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $caseIds = $request->case_ids;
            $processItem = $request->process_item;
            $remark = $request->remark;
            $expectedDate = $request->expected_date;

            $created = 0;
            foreach ($caseIds as $caseId) {
                CaseProcess::create([
                    'case_id' => $caseId,
                    'process_item' => $processItem,
                    'process_status' => CaseProcess::STATUS_PENDING,
                    'assigned_to' => $userId,
                    'process_remark' => $remark,
                    'expected_complete_date' => $expectedDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $created++;
            }

            return response()->json([
                'success' => true,
                'message' => "成功为 {$created} 个项目添加流程跟进"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加收文日期
     */
    public function addReceivedTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'received_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $caseIds = $request->case_ids;
            $receivedDate = $request->received_date;

            // 批量更新收文日期
            $updated = Cases::whereIn('id', $caseIds)->update([
                'received_date' => $receivedDate,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "成功为 {$updated} 个项目添加收文日期"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加办结日期
     */
    public function addDeadline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'deadline_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $caseIds = $request->case_ids;
            $deadlineDate = $request->deadline_date;

            // 批量更新办结日期
            $updated = Cases::whereIn('id', $caseIds)->update([
                'deadline_date' => $deadlineDate,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "成功为 {$updated} 个项目添加办结日期"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 添加备注
     */
    public function addRemark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_ids' => 'required|array',
            'case_ids.*' => 'integer|exists:cases,id',
            'remark' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $caseIds = $request->case_ids;
            $remark = $request->remark;

            // 批量添加备注
            $updated = Cases::whereIn('id', $caseIds)->update([
                'remark' => $remark,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "成功为 {$updated} 个项目添加备注"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 导出数据
     */
    public function export(Request $request)
    {
        try {
            $type = $request->input('type');
            $searchParams = $request->input('search_params', []);
            $selectedIds = $request->input('selected_ids', []);
            $visibleColumns = $request->input('visible_columns', []);

            // 根据导出类型构建不同的查询
            switch ($type) {
                case 'completed_project':
                    return $this->exportCompletedProject($searchParams, $selectedIds);
                case 'department_cases':
                    return $this->exportDepartmentCases($searchParams, $selectedIds, $visibleColumns);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => '不支持的导出类型'
                    ], 400)->header('Content-Type', 'application/json');
            }

        } catch (\Exception $e) {
            // 返回JSON错误响应，前端可以通过content-type判断
            return response()->json([
                'success' => false,
                'message' => '导出失败: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * 导出已完成科技服务项目
     */
    private function exportCompletedProject($searchParams, $selectedIds)
    {
        // 构建查询
        $query = Cases::with(['processes'])
            ->where('case_type', Cases::TYPE_TECH_SERVICE)
            ->whereIn('id', $selectedIds);

        $cases = $query->get();

        // 准备导出数据
        $exportData = [];
        foreach ($cases as $case) {
            $currentProcess = $case->processes->first();
            
            $exportData[] = [
                '我方文号' => $case->our_ref_number ?: 'KF' . date('Y') . str_pad($case->id, 4, '0', STR_PAD_LEFT),
                '提案名称' => $case->case_name ?: '科技服务项目',
                '科技服务名称' => $case->business_type ?: '科技服务',
                '业务类型' => $case->application_type ?: '项目申报',
                '申请类型' => $case->application_type ?: '项目申报',
                '项目年份' => $case->project_year ?: date('Y'),
                '申报批次' => $case->application_batch ?: date('Y') . '批次',
                '客户名称' => $case->customer_name ?: '客户' . $case->id,
                '项目状态' => $case->case_status ?: '已完成',
                '完成日期' => $currentProcess && $currentProcess->completed_date ? Carbon::parse($currentProcess->completed_date)->format('Y-m-d') : '',
                '处理人员' => $currentProcess && $currentProcess->assignedUser ? $currentProcess->assignedUser->real_name : '李明',
                '备注' => $currentProcess ? $currentProcess->process_remark : ''
            ];
        }

        return $this->generateExcelResponse($exportData, '已完成科技服务项目');
    }

    /**
     * 导出部门客户项目
     */
    private function exportDepartmentCases($searchParams, $selectedIds, $visibleColumns)
    {
        // 构建查询
        $query = Cases::with(['processes', 'businessPerson'])
            ->whereIn('id', $selectedIds);

        $cases = $query->get();

        // 准备导出数据
        $exportData = [];
        foreach ($cases as $case) {
            $currentProcess = $case->processes->first();
            
            $row = [];
            
            // 根据可见列配置导出数据
            if (!$visibleColumns || isset($visibleColumns['caseName'])) {
                $row['项目名称'] = $case->case_name;
            }
            if (!$visibleColumns || isset($visibleColumns['applicationType'])) {
                $row['申请类型'] = $case->application_type ?: '发明专利';
            }
            if (!$visibleColumns || isset($visibleColumns['processItem'])) {
                $row['处理事项'] = $currentProcess ? $currentProcess->process_item : '新申请';
            }
            if (!$visibleColumns || isset($visibleColumns['processItemState'])) {
                $row['处理事项状态'] = $currentProcess ? $currentProcess->process_status : '进行中';
            }
            if (!$visibleColumns || isset($visibleColumns['handler'])) {
                $row['处理人'] = $case->businessPerson ? $case->businessPerson->name : '李明';
            }
            if (!$visibleColumns || isset($visibleColumns['customerName'])) {
                $row['客户名称'] = $case->customer_name ?: '客户' . $case->id;
            }
            if (!$visibleColumns || isset($visibleColumns['processItemRemark'])) {
                $row['处理事项备注'] = $currentProcess ? $currentProcess->process_remark : '';
            }
            
            $exportData[] = $row;
        }

        return $this->generateExcelResponse($exportData, '部门客户项目');
    }

    /**
     * 生成Excel响应
     */
    private function generateExcelResponse($data, $filename)
    {
        // 这里应该使用Excel库生成文件，比如 PhpSpreadsheet
        // 为了简化，我们返回CSV格式的数据
        
        $output = fopen('php://temp', 'w');
        
        // 写入BOM以支持中文
        fwrite($output, "\xEF\xBB\xBF");
        
        if (!empty($data)) {
            // 写入表头
            fputcsv($output, array_keys($data[0]));
            
            // 写入数据
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * 修改处理事项
     */
    public function modifyProcessItem(Request $request)
    {
        try {
            $itemId = $request->input('item_id');
            $expectedStartDate = $request->input('expected_start_date');
            $completedPoints = $request->input('completed_points');
            $totalCompletedPoints = $request->input('total_completed_points');
            $expectedFinishDate = $request->input('expected_finish_date');
            $completionRate = $request->input('completion_rate');
            $totalCompletionRate = $request->input('total_completion_rate');
            $partialCompleteDate = $request->input('partial_complete_date');
            $completeDate = $request->input('complete_date');
            $processStatus = $request->input('process_status');

            // 查找处理事项
            $processItem = CaseProcess::find($itemId);
            if (!$processItem) {
                return response()->json([
                    'success' => false,
                    'message' => '处理事项不存在'
                ], 404);
            }

            // 更新处理事项
            $updateData = [];
            if ($expectedStartDate) $updateData['expected_start_date'] = $expectedStartDate;
            if ($completedPoints !== null) $updateData['completed_points'] = $completedPoints;
            if ($totalCompletedPoints !== null) $updateData['total_completed_points'] = $totalCompletedPoints;
            if ($expectedFinishDate) $updateData['expected_complete_date'] = $expectedFinishDate;
            if ($completionRate !== null) $updateData['completion_rate'] = $completionRate;
            if ($totalCompletionRate !== null) $updateData['total_completion_rate'] = $totalCompletionRate;
            if ($partialCompleteDate) $updateData['partial_complete_date'] = $partialCompleteDate;
            if ($completeDate) $updateData['completed_date'] = $completeDate;
            if ($processStatus) $updateData['process_status'] = $processStatus;

            $processItem->update($updateData);

            return response()->json([
                'success' => true,
                'message' => '修改成功',
                'data' => $processItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '修改失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取案例状态文本
     */
    private function getCaseStatusText($status)
    {
        $statusMap = [
            Cases::STATUS_DRAFT => '草稿',
            Cases::STATUS_TO_BE_FILED => '待立项',
            Cases::STATUS_SUBMITTED => '已提交',
            Cases::STATUS_PROCESSING => '处理中',
            Cases::STATUS_AUTHORIZED => '已授权',
            Cases::STATUS_REJECTED => '已驳回',
            Cases::STATUS_COMPLETED => '已完成',
            Cases::STATUS_ARCHIVED => '已归档',
        ];

        return $statusMap[$status] ?? '未知';
    }

    /**
     * 获取处理状态文本
     */
    private function getProcessStatusText($status)
    {
        $statusMap = [
            CaseProcess::STATUS_DRAFT => '待提交',
            CaseProcess::STATUS_PENDING => '待处理',
            CaseProcess::STATUS_IN_PROGRESS => '处理中',
            CaseProcess::STATUS_COMPLETED => '已完成',
            CaseProcess::STATUS_ASSIGNED => '已分配',
            CaseProcess::STATUS_NOT_STARTED => '未开始',
            CaseProcess::STATUS_CANCELLED => '已取消',
        ];

        return $statusMap[$status] ?? '未知';
    }
}
