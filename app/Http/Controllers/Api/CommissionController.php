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
     * 
     * 功能说明：
     * - 根据筛选条件查询已完成的案例流程
     * - 计算每个流程的提成金额
     * - 返回提成明细列表和汇总统计信息
     * - 支持按用户、日期范围、案例类型进行筛选
     * 
     * 请求参数：
     * - user_id (integer, optional): 用户ID，筛选指定用户的提成数据
     * - start_date (string, optional): 开始日期，格式：Y-m-d
     * - end_date (string, optional): 结束日期，格式：Y-m-d
     * - case_type (string, optional): 案例类型，可选值：patent(专利)、trademark(商标)、copyright(版权)、tech_service(技术服务)
     * 
     * 响应参数：
     * - success (boolean): 请求是否成功
     * - data (object): 响应数据
     *   - list (array): 提成明细列表
     *     - id (integer): 流程ID
     *     - case_code (string): 案例编号
     *     - case_name (string): 案例名称
     *     - case_type (string): 案例类型文本
     *     - process_name (string): 流程名称
     *     - processor (string): 处理人姓名
     *     - completion_date (string): 完成日期，格式：Y-m-d
     *     - base_fee (decimal): 基础费用
     *     - commission_rate (decimal): 提成比例
     *     - commission_amount (decimal): 提成金额
     *     - bonus (decimal): 奖金
     *     - total_commission (decimal): 总提成
     *   - summary (object): 汇总统计
     *     - total_cases (integer): 总案例数
     *     - total_commission (decimal): 总提成金额
     *     - average_commission (decimal): 平均提成金额
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function getCommissionStats(Request $request)
    {
        try {
            // 获取请求参数
            $userId = $request->input('user_id');           // 用户ID筛选
            $startDate = $request->input('start_date');     // 开始日期
            $endDate = $request->input('end_date');         // 结束日期
            $caseType = $request->input('case_type');       // 案例类型

            // 构建基础查询：查询已完成且已分配的案例流程
            $query = CaseProcess::with(['case', 'assignedUser'])
                ->whereNotNull('assigned_to')                           // 必须已分配处理人
                ->where('process_status', CaseProcess::STATUS_COMPLETED); // 必须已完成

            // 用户筛选：如果指定了用户ID，只查询该用户的数据
            if ($userId) {
                $query->where('assigned_to', $userId);
            }

            // 日期筛选：如果指定了日期范围，按完成日期筛选
            if ($startDate && $endDate) {
                $query->whereBetween('completion_date', [$startDate, $endDate]);
            }

            // 案例类型筛选：根据案例类型筛选相关流程
            if ($caseType) {
                $query->whereHas('case', function($caseQuery) use ($caseType) {
                    // 案例类型映射：前端传入的字符串映射到数据库常量
                    $typeMap = [
                        'patent' => Cases::TYPE_PATENT,           // 专利
                        'trademark' => Cases::TYPE_TRADEMARK,     // 商标
                        'copyright' => Cases::TYPE_COPYRIGHT,     // 版权
                        'tech_service' => Cases::TYPE_TECH_SERVICE // 技术服务
                    ];
                    if (isset($typeMap[$caseType])) {
                        $caseQuery->where('case_type', $typeMap[$caseType]);
                    }
                });
            }

            // 执行查询获取流程数据
            $processes = $query->get();

            // 初始化提成计算变量
            $commissionData = [];    // 提成明细数组
            $totalCommission = 0;    // 总提成金额
            $totalCases = 0;         // 总案例数

            // 遍历每个流程计算提成
            foreach ($processes as $process) {
                // 调用提成计算方法
                $commission = $this->calculateCommission($process);
                
                // 构建提成明细数据
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

                // 累计统计数据
                $totalCommission += $commission['total_commission'];
                $totalCases++;
            }

            // 返回成功响应
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
            // 异常处理：返回错误响应
            return response()->json([
                'success' => false,
                'message' => '查询提成数据失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取用户提成汇总
     * 
     * 功能说明：
     * - 按用户维度统计提成数据
     * - 计算每个用户的总提成、案例数量和平均提成
     * - 支持按日期范围和案例类型筛选
     * - 按提成总额降序排列用户
     * - 统计每个用户处理的案例类型分布
     * 
     * 请求参数：
     * - start_date (string, optional): 开始日期，格式：Y-m-d
     * - end_date (string, optional): 结束日期，格式：Y-m-d
     * - case_type (string, optional): 案例类型，可选值：patent(专利)、trademark(商标)、copyright(版权)、tech_service(技术服务)
     * 
     * 响应参数：
     * - success (boolean): 请求是否成功
     * - data (array): 用户提成汇总数据列表，按提成总额降序排列
     *   - user_id (integer): 用户ID
     *   - user_name (string): 用户真实姓名
     *   - department (integer): 部门ID
     *   - total_cases (integer): 处理的案例总数
     *   - total_commission (decimal): 提成总金额
     *   - average_commission (decimal): 平均提成金额
     *   - case_types (object): 案例类型分布统计，键为案例类型名称，值为数量
     * 
     * @param Request $request HTTP请求对象
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function getUserCommissionSummary(Request $request)
    {
        try {
            // 获取请求参数
            $startDate = $request->input('start_date');     // 开始日期
            $endDate = $request->input('end_date');         // 结束日期
            $caseType = $request->input('case_type');       // 案例类型

            // 构建用户查询：获取所有有完成处理事项的用户
            $query = User::whereHas('assignedProcesses', function($processQuery) use ($startDate, $endDate, $caseType) {
                // 只查询已完成的流程
                $processQuery->where('process_status', CaseProcess::STATUS_COMPLETED);
                
                // 日期范围筛选
                if ($startDate && $endDate) {
                    $processQuery->whereBetween('completion_date', [$startDate, $endDate]);
                }
                
                // 案例类型筛选
                if ($caseType) {
                    $processQuery->whereHas('case', function($caseQuery) use ($caseType) {
                        // 案例类型映射：前端传入的字符串映射到数据库常量
                        $typeMap = [
                            'patent' => Cases::TYPE_PATENT,           // 专利
                            'trademark' => Cases::TYPE_TRADEMARK,     // 商标
                            'copyright' => Cases::TYPE_COPYRIGHT,     // 版权
                            'tech_service' => Cases::TYPE_TECH_SERVICE // 技术服务
                        ];
                        if (isset($typeMap[$caseType])) {
                            $caseQuery->where('case_type', $typeMap[$caseType]);
                        }
                    });
                }
            })->with(['assignedProcesses' => function($processQuery) use ($startDate, $endDate, $caseType) {
                // 预加载用户的已完成流程及关联案例
                $processQuery->where('process_status', CaseProcess::STATUS_COMPLETED)
                            ->with('case');
                
                // 应用相同的日期筛选条件
                if ($startDate && $endDate) {
                    $processQuery->whereBetween('completion_date', [$startDate, $endDate]);
                }
                
                // 应用相同的案例类型筛选条件
                if ($caseType) {
                    $processQuery->whereHas('case', function($caseQuery) use ($caseType) {
                        // 案例类型映射：前端传入的字符串映射到数据库常量
                        $typeMap = [
                            'patent' => Cases::TYPE_PATENT,           // 专利
                            'trademark' => Cases::TYPE_TRADEMARK,     // 商标
                            'copyright' => Cases::TYPE_COPYRIGHT,     // 版权
                            'tech_service' => Cases::TYPE_TECH_SERVICE // 技术服务
                        ];
                        if (isset($typeMap[$caseType])) {
                            $caseQuery->where('case_type', $typeMap[$caseType]);
                        }
                    });
                }
            }]);

            // 执行查询获取用户数据
            $users = $query->get();
            $summaryData = [];

            // 遍历每个用户计算提成汇总
            foreach ($users as $user) {
                // 初始化用户统计变量
                $totalCommission = 0;    // 用户总提成
                $totalCases = 0;         // 用户处理案例总数
                $caseTypes = [];         // 案例类型分布统计

                // 遍历用户的所有已完成流程
                foreach ($user->assignedProcesses as $process) {
                    // 计算单个流程的提成
                    $commission = $this->calculateCommission($process);
                    $totalCommission += $commission['total_commission'];
                    $totalCases++;
                    
                    // 统计案例类型分布
                    $caseType = $process->case->type_text;
                    if (!isset($caseTypes[$caseType])) {
                        $caseTypes[$caseType] = 0;
                    }
                    $caseTypes[$caseType]++;
                }

                // 只有处理过案例的用户才加入汇总
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

            // 按提成总额降序排序
            usort($summaryData, function($a, $b) {
                return $b['total_commission'] <=> $a['total_commission'];
            });

            // 返回成功响应
            return response()->json([
                'success' => true,
                'data' => $summaryData
            ]);

        } catch (\Exception $e) {
            // 异常处理：返回错误响应
            return response()->json([
                'success' => false,
                'message' => '查询提成汇总失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 计算单个处理事项的提成
     * 
     * 功能说明：
     * - 根据案例类型计算基础提成
     * - 根据提前完成天数计算奖励提成
     * - 应用难度系数进行最终调整
     * - 返回详细的提成计算结果
     * 
     * 计算公式：
     * 总提成 = (基础提成 + 奖励提成) × 难度系数
     * 基础提成 = 基础费用 × 提成比例
     * 奖励提成 = 基础费用 × 奖励比例（当提前完成天数 >= 阈值时）
     * 
     * 参数说明：
     * - $process (CaseProcess): 案例处理流程对象，包含完成日期、截止日期、难度系数等信息
     * 
     * 返回参数：
     * - base_fee (decimal): 基础费用
     * - commission_rate (decimal): 提成比例
     * - commission_amount (decimal): 基础提成金额
     * - bonus (decimal): 奖励提成金额
     * - difficulty_multiplier (decimal): 难度系数
     * - total_commission (decimal): 总提成金额
     * 
     * @param CaseProcess $process 案例处理流程对象
     * @return array 提成计算结果数组
     */
    private function calculateCommission($process)
    {
        // 基础提成配置：不同案例类型的提成参数设置
        $commissionConfig = [
            // 专利案例配置
            Cases::TYPE_PATENT => [
                'base_rate' => 0.05,        // 基础提成比例：5%
                'base_fee' => 500,          // 基础费用：500元
                'bonus_threshold' => 30,    // 奖励天数阈值：提前30天
                'bonus_rate' => 0.02        // 奖励提成比例：2%
            ],
            // 商标案例配置
            Cases::TYPE_TRADEMARK => [
                'base_rate' => 0.04,        // 基础提成比例：4%
                'base_fee' => 300,          // 基础费用：300元
                'bonus_threshold' => 20,    // 奖励天数阈值：提前20天
                'bonus_rate' => 0.015       // 奖励提成比例：1.5%
            ],
            // 版权案例配置
            Cases::TYPE_COPYRIGHT => [
                'base_rate' => 0.03,        // 基础提成比例：3%
                'base_fee' => 200,          // 基础费用：200元
                'bonus_threshold' => 15,    // 奖励天数阈值：提前15天
                'bonus_rate' => 0.01        // 奖励提成比例：1%
            ],
            // 技术服务案例配置
            Cases::TYPE_TECH_SERVICE => [
                'base_rate' => 0.06,        // 基础提成比例：6%
                'base_fee' => 800,          // 基础费用：800元
                'bonus_threshold' => 45,    // 奖励天数阈值：提前45天
                'bonus_rate' => 0.025       // 奖励提成比例：2.5%
            ]
        ];

        // 获取案例类型并匹配对应配置，默认使用专利配置
        $caseType = $process->case->case_type;
        $config = $commissionConfig[$caseType] ?? $commissionConfig[Cases::TYPE_PATENT];

        // 提取配置参数
        $baseFee = $config['base_fee'];                 // 基础费用
        $commissionRate = $config['base_rate'];         // 提成比例
        
        // 计算基础提成：基础费用 × 提成比例
        $commissionAmount = $baseFee * $commissionRate;
        
        // 计算奖励提成：根据提前完成天数判断
        $bonus = 0;
        if ($process->completion_date && $process->due_date) {
            // 解析完成日期和截止日期
            $completionDate = Carbon::parse($process->completion_date);
            $dueDate = Carbon::parse($process->due_date);
            
            // 计算提前完成的天数（正数表示提前，负数表示延迟）
            $daysEarly = $dueDate->diffInDays($completionDate, false);
            
            // 如果提前完成且天数达到奖励阈值，则给予奖励提成
            if ($daysEarly > 0 && $daysEarly >= $config['bonus_threshold']) {
                $bonus = $baseFee * $config['bonus_rate'];
            }
        }
        
        // 应用难度系数调整：获取流程难度系数，默认为1.0
        $difficultyMultiplier = $process->process_coefficient ?? 1.0;
        
        // 计算最终提成：(基础提成 + 奖励提成) × 难度系数
        $totalCommission = ($commissionAmount + $bonus) * $difficultyMultiplier;

        // 返回详细的提成计算结果
        return [
            'base_fee' => $baseFee,                                     // 基础费用
            'commission_rate' => $commissionRate,                       // 提成比例
            'commission_amount' => round($commissionAmount, 2),         // 基础提成金额（保留2位小数）
            'bonus' => round($bonus, 2),                               // 奖励提成金额（保留2位小数）
            'difficulty_multiplier' => $difficultyMultiplier,           // 难度系数
            'total_commission' => round($totalCommission, 2)            // 总提成金额（保留2位小数）
        ];
    }

    /**
     * 获取提成配置
     * 
     * 功能说明：
     * - 返回系统中所有案例类型的提成配置信息
     * - 提供前端展示和计算参考的配置数据
     * - 配置包含基础提成比例、基础费用、奖励阈值等参数
     * - 用于前端界面显示提成规则和计算说明
     * 
     * 请求参数：
     * - 无需传入参数
     * 
     * 响应参数：
     * - success (boolean): 请求是否成功
     * - data (object): 提成配置数据对象，按案例类型分组
     *   - patent (object): 专利案例配置
     *     - name (string): 案例类型名称
     *     - base_rate (integer): 基础提成比例（百分比形式）
     *     - base_fee (integer): 基础费用（元）
     *     - bonus_threshold (integer): 奖励天数阈值（天）
     *     - bonus_rate (decimal): 奖励提成比例（百分比形式）
     *   - trademark (object): 商标案例配置（字段同上）
     *   - copyright (object): 版权案例配置（字段同上）
     *   - tech_service (object): 技术服务案例配置（字段同上）
     * 
     * @param Request $request HTTP请求对象（未使用）
     * @return \Illuminate\Http\JsonResponse JSON响应
     */
    public function getCommissionConfig(Request $request)
    {
        try {
            // 提成配置数据：定义各案例类型的提成参数（以百分比形式返回给前端）
            $config = [
                // 专利案例提成配置
                'patent' => [
                    'name' => '专利',                    // 案例类型中文名称
                    'base_rate' => 5,                   // 基础提成比例：5%
                    'base_fee' => 500,                  // 基础费用：500元
                    'bonus_threshold' => 30,            // 奖励天数阈值：提前30天
                    'bonus_rate' => 2                   // 奖励提成比例：2%
                ],
                // 商标案例提成配置
                'trademark' => [
                    'name' => '商标',                    // 案例类型中文名称
                    'base_rate' => 4,                   // 基础提成比例：4%
                    'base_fee' => 300,                  // 基础费用：300元
                    'bonus_threshold' => 20,            // 奖励天数阈值：提前20天
                    'bonus_rate' => 1.5                 // 奖励提成比例：1.5%
                ],
                // 版权案例提成配置
                'copyright' => [
                    'name' => '版权',                    // 案例类型中文名称
                    'base_rate' => 3,                   // 基础提成比例：3%
                    'base_fee' => 200,                  // 基础费用：200元
                    'bonus_threshold' => 15,            // 奖励天数阈值：提前15天
                    'bonus_rate' => 1                   // 奖励提成比例：1%
                ],
                // 技术服务案例提成配置
                'tech_service' => [
                    'name' => '科技服务',                // 案例类型中文名称
                    'base_rate' => 6,                   // 基础提成比例：6%
                    'base_fee' => 800,                  // 基础费用：800元
                    'bonus_threshold' => 45,            // 奖励天数阈值：提前45天
                    'bonus_rate' => 2.5                 // 奖励提成比例：2.5%
                ]
            ];

            // 返回成功响应
            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            // 异常处理：返回错误响应
            return response()->json([
                'success' => false,
                'message' => '获取配置失败：' . $e->getMessage()
            ], 500);
        }
    }
}
