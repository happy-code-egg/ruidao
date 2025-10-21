<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseProcess;
use App\Models\CaseFee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionController extends Controller
{
    /**
     * 获取提成统计数据
     */
    public function getCommissionStats(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $caseType = $request->input('case_type');

            // 构建基础查询
            $query = CaseProcess::with(['case', 'assignedUser'])
                ->whereNotNull('assigned_to')
                ->where('process_status', CaseProcess::STATUS_COMPLETED);

            // 用户筛选
            if ($userId) {
                $query->where('assigned_to', $userId);
            }

            // 日期筛选
            if ($startDate && $endDate) {
                $query->whereBetween('completion_date', [$startDate, $endDate]);
            }

            // 案例类型筛选
            if ($caseType) {
                $query->whereHas('case', function($caseQuery) use ($caseType) {
                    $typeMap = [
                        'patent' => Cases::TYPE_PATENT,
                        'trademark' => Cases::TYPE_TRADEMARK,
                        'copyright' => Cases::TYPE_COPYRIGHT,
                        'tech_service' => Cases::TYPE_TECH_SERVICE
                    ];
                    if (isset($typeMap[$caseType])) {
                        $caseQuery->where('case_type', $typeMap[$caseType]);
                    }
                });
            }

            $processes = $query->get();

            // 计算提成
            $commissionData = [];
            $totalCommission = 0;
            $totalCases = 0;

            foreach ($processes as $process) {
                $commission = $this->calculateCommission($process);
                
                $commissionData[] = [
                    'id' => $process->id,
                    'case_code' => $process->case->case_code,
                    'case_name' => $process->case->case_name,
                    'case_type' => $process->case->type_text,
                    'process_name' => $process->process_name,
                    'processor' => $process->assignedUser->name,
                    'completion_date' => $process->completion_date ? $process->completion_date->format('Y-m-d') : '',
                    'base_fee' => $commission['base_fee'],
                    'commission_rate' => $commission['commission_rate'],
                    'commission_amount' => $commission['commission_amount'],
                    'bonus' => $commission['bonus'],
                    'total_commission' => $commission['total_commission']
                ];

                $totalCommission += $commission['total_commission'];
                $totalCases++;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'list' => $commissionData,
                    'summary' => [
                        'total_cases' => $totalCases,
                        'total_commission' => $totalCommission,
                        'average_commission' => $totalCases > 0 ? round($totalCommission / $totalCases, 2) : 0
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询提成数据失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取用户提成汇总
     */
    public function getUserCommissionSummary(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $caseType = $request->input('case_type');

            // 获取所有有完成处理事项的用户
            $query = User::whereHas('assignedProcesses', function($processQuery) use ($startDate, $endDate, $caseType) {
                $processQuery->where('process_status', CaseProcess::STATUS_COMPLETED);
                
                if ($startDate && $endDate) {
                    $processQuery->whereBetween('completion_date', [$startDate, $endDate]);
                }
                
                if ($caseType) {
                    $processQuery->whereHas('case', function($caseQuery) use ($caseType) {
                        $typeMap = [
                            'patent' => Cases::TYPE_PATENT,
                            'trademark' => Cases::TYPE_TRADEMARK,
                            'copyright' => Cases::TYPE_COPYRIGHT,
                            'tech_service' => Cases::TYPE_TECH_SERVICE
                        ];
                        if (isset($typeMap[$caseType])) {
                            $caseQuery->where('case_type', $typeMap[$caseType]);
                        }
                    });
                }
            })->with(['assignedProcesses' => function($processQuery) use ($startDate, $endDate, $caseType) {
                $processQuery->where('process_status', CaseProcess::STATUS_COMPLETED)
                            ->with('case');
                
                if ($startDate && $endDate) {
                    $processQuery->whereBetween('completion_date', [$startDate, $endDate]);
                }
                
                if ($caseType) {
                    $processQuery->whereHas('case', function($caseQuery) use ($caseType) {
                        $typeMap = [
                            'patent' => Cases::TYPE_PATENT,
                            'trademark' => Cases::TYPE_TRADEMARK,
                            'copyright' => Cases::TYPE_COPYRIGHT,
                            'tech_service' => Cases::TYPE_TECH_SERVICE
                        ];
                        if (isset($typeMap[$caseType])) {
                            $caseQuery->where('case_type', $typeMap[$caseType]);
                        }
                    });
                }
            }]);

            $users = $query->get();
            $summaryData = [];

            foreach ($users as $user) {
                $totalCommission = 0;
                $totalCases = 0;
                $caseTypes = [];

                foreach ($user->assignedProcesses as $process) {
                    $commission = $this->calculateCommission($process);
                    $totalCommission += $commission['total_commission'];
                    $totalCases++;
                    
                    $caseType = $process->case->type_text;
                    if (!isset($caseTypes[$caseType])) {
                        $caseTypes[$caseType] = 0;
                    }
                    $caseTypes[$caseType]++;
                }

                if ($totalCases > 0) {
                    $summaryData[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->real_name,
                        'department' => $user->department_id,
                        'total_cases' => $totalCases,
                        'total_commission' => $totalCommission,
                        'average_commission' => round($totalCommission / $totalCases, 2),
                        'case_types' => $caseTypes
                    ];
                }
            }

            // 按提成总额排序
            usort($summaryData, function($a, $b) {
                return $b['total_commission'] <=> $a['total_commission'];
            });

            return response()->json([
                'success' => true,
                'data' => $summaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '查询提成汇总失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 计算单个处理事项的提成
     */
    private function calculateCommission($process)
    {
        // 基础提成配置
        $commissionConfig = [
            Cases::TYPE_PATENT => [
                'base_rate' => 0.05,  // 5%
                'base_fee' => 500,    // 基础费用
                'bonus_threshold' => 30, // 天数阈值
                'bonus_rate' => 0.02  // 奖励比例
            ],
            Cases::TYPE_TRADEMARK => [
                'base_rate' => 0.04,
                'base_fee' => 300,
                'bonus_threshold' => 20,
                'bonus_rate' => 0.015
            ],
            Cases::TYPE_COPYRIGHT => [
                'base_rate' => 0.03,
                'base_fee' => 200,
                'bonus_threshold' => 15,
                'bonus_rate' => 0.01
            ],
            Cases::TYPE_TECH_SERVICE => [
                'base_rate' => 0.06,
                'base_fee' => 800,
                'bonus_threshold' => 45,
                'bonus_rate' => 0.025
            ]
        ];

        $caseType = $process->case->case_type;
        $config = $commissionConfig[$caseType] ?? $commissionConfig[Cases::TYPE_PATENT];

        // 基础费用
        $baseFee = $config['base_fee'];
        
        // 提成比例
        $commissionRate = $config['base_rate'];
        
        // 基础提成
        $commissionAmount = $baseFee * $commissionRate;
        
        // 计算奖励
        $bonus = 0;
        if ($process->completion_date && $process->due_date) {
            $completionDate = Carbon::parse($process->completion_date);
            $dueDate = Carbon::parse($process->due_date);
            $daysEarly = $dueDate->diffInDays($completionDate, false);
            
            if ($daysEarly > 0 && $daysEarly >= $config['bonus_threshold']) {
                $bonus = $baseFee * $config['bonus_rate'];
            }
        }
        
        // 难度系数调整
        $difficultyMultiplier = $process->process_coefficient ?? 1.0;
        
        $totalCommission = ($commissionAmount + $bonus) * $difficultyMultiplier;

        return [
            'base_fee' => $baseFee,
            'commission_rate' => $commissionRate,
            'commission_amount' => round($commissionAmount, 2),
            'bonus' => round($bonus, 2),
            'difficulty_multiplier' => $difficultyMultiplier,
            'total_commission' => round($totalCommission, 2)
        ];
    }

    /**
     * 获取提成配置
     */
    public function getCommissionConfig(Request $request)
    {
        try {
            $config = [
                'patent' => [
                    'name' => '专利',
                    'base_rate' => 5,
                    'base_fee' => 500,
                    'bonus_threshold' => 30,
                    'bonus_rate' => 2
                ],
                'trademark' => [
                    'name' => '商标',
                    'base_rate' => 4,
                    'base_fee' => 300,
                    'bonus_threshold' => 20,
                    'bonus_rate' => 1.5
                ],
                'copyright' => [
                    'name' => '版权',
                    'base_rate' => 3,
                    'base_fee' => 200,
                    'bonus_threshold' => 15,
                    'bonus_rate' => 1
                ],
                'tech_service' => [
                    'name' => '科技服务',
                    'base_rate' => 6,
                    'base_fee' => 800,
                    'bonus_threshold' => 45,
                    'bonus_rate' => 2.5
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取配置失败：' . $e->getMessage()
            ], 500);
        }
    }
}
