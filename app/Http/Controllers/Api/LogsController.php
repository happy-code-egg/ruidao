<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    /**
     * 获取日志列表
     */
    public function index(Request $request)
    {
        try {
            // 先尝试从数据库获取真实日志
            $query = Logs::with('user')->orderBy('created_at', 'desc');

            // 搜索条件
            if ($request->has('keyword') && $request->keyword) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('content', 'like', "%{$keyword}%")
                      ->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhereHas('user', function ($userQuery) use ($keyword) {
                          $userQuery->where('username', 'like', "%{$keyword}%");
                      });
                });
            }

            // 按用户名搜索
            if ($request->has('username') && $request->username) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('username', 'like', "%{$request->username}%");
                });
            }

            // 按状态筛选
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // 类型筛选
            if ($request->has('type') && $request->type !== null) {
                $query->where('type', $request->type);
            }

            // 用户筛选
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // 日期范围筛选
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // 分页
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);
            $total = $query->count();

            $logs = $query->offset(($page - 1) * $limit)
                         ->limit($limit)
                         ->get();

            // 转换数据格式以匹配前端期望
            $formattedLogs = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'title' => $log->title ?: $log->content,
                    'type' => $log->type,
                    'type_text' => $log->type_text,
                    'type_code' => $log->type_code,
                    'method' => $log->method ?: 'System Operation',
                    'request_method' => $log->request_method ?: 'POST',
                    'requestMethod' => $log->request_method ?: 'POST',
                    'username' => $log->user ? $log->user->username : 'System',
                    'user_id' => $log->user_id,
                    'ip' => $log->ip_address ?: '127.0.0.1',
                    'ip_address' => $log->ip_address ?: '127.0.0.1',
                    'location' => $log->location ?: '内网IP',
                    'status' => $log->status ?? 1,
                    'status_text' => $log->status_text,
                    'operTime' => $log->created_at->format('Y-m-d H:i:s'),
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'url' => $log->url ?: '/api/system',
                    'request_param' => $log->request_param ?: '{}',
                    'requestParam' => $log->request_param ?: '{}',
                    'json_result' => $log->json_result ?: '{"code":200,"msg":"操作成功","data":{}}',
                    'jsonResult' => $log->json_result ?: '{"code":200,"msg":"操作成功","data":{}}',
                    'error_msg' => $log->error_msg ?: '',
                    'errorMsg' => $log->error_msg ?: '',
                    'execution_time' => $log->execution_time,
                    'content' => $log->content,
                    'user_agent' => $log->user_agent,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'username' => $log->user->username,
                        'name' => $log->user->name ?? $log->user->username
                    ] : null
                ];
            });

            return json_page($formattedLogs, $total, '获取成功');
        } catch (\Exception $e) {
            $this->log(8, 'LogsController.index.' . $e->getMessage());
            return json_fail('获取日志列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取模拟日志数据
     */
    private function getMockLogData(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);

        // 生成模拟日志数据
        $mockLogs = [];
        $types = [0, 1, 2, 3, 4, 5, 6, 7, 8];
        $contents = [
            '用户登录系统',
            '创建新用户',
            '更新用户信息',
            '删除用户',
            '查看用户列表',
            '分配用户角色',
            '导出用户数据',
            '系统配置更新',
            '权限验证失败'
        ];
        $usernames = ['admin', 'user1', 'user2', 'manager', 'operator'];

        for ($i = 1; $i <= 100; $i++) {
            $type = $types[array_rand($types)];
            $content = $contents[array_rand($contents)];
            $username = $usernames[array_rand($usernames)];

            $mockLogs[] = [
                'id' => $i,
                'content' => $content . ' - ' . $i,
                'type' => $type,
                'user_id' => rand(1, 5),
                'ip_address' => '192.168.1.' . rand(100, 200),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => rand(1, 5),
                    'username' => $username,
                    'real_name' => $username . '用户'
                ]
            ];
        }

        // 应用搜索过滤
        if ($request->has('keyword') && $request->keyword) {
            $keyword = $request->keyword;
            $mockLogs = array_filter($mockLogs, function($log) use ($keyword) {
                return stripos($log['content'], $keyword) !== false ||
                       stripos($log['user']['username'], $keyword) !== false;
            });
        }

        if ($request->has('type') && $request->type !== null) {
            $mockLogs = array_filter($mockLogs, function($log) use ($request) {
                return $log['type'] == $request->type;
            });
        }

        // 重新索引
        $mockLogs = array_values($mockLogs);
        $total = count($mockLogs);

        // 分页处理
        $start = ($page - 1) * $limit;
        $mockLogs = array_slice($mockLogs, $start, $limit);

        return json_page($mockLogs, $total, '获取成功（模拟数据）');
    }

    /**
     * 获取日志详情
     */
    public function show($id)
    {
        try {
            $log = Logs::with('user')->find($id);

            if (!$log) {
                return json_fail('日志不存在');
            }

            return json_success('获取成功', $log);
        } catch (\Exception $e) {
            $this->log(8, 'LogsController.show.' . $e->getMessage());
            return json_fail('获取日志详情失败');
        }
    }

    /**
     * 删除日志
     */
    public function destroy($id)
    {
        try {
            $log = Logs::find($id);

            if (!$log) {
                return json_fail('日志不存在');
            }

            $log->delete();

            $this->log(3, '删除日志记录：' . $log->content);

            return json_success('删除成功');
        } catch (\Exception $e) {
            $this->log(8, 'LogsController.destroy.' . $e->getMessage());
            return json_fail('删除日志失败');
        }
    }

    /**
     * 批量删除日志
     */
    public function batchDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return json_fail('请选择要删除的日志');
            }

            $count = Logs::whereIn('id', $ids)->delete();

            $this->log(3, '批量删除日志记录：' . $count . '条');

            return json_success("成功删除 {$count} 条日志记录");
        } catch (\Exception $e) {
            $this->log(8, 'LogsController.batchDelete.' . $e->getMessage());
            return json_fail('批量删除日志失败');
        }
    }

    /**
     * 清空日志
     */
    public function clear()
    {
        try {
            $count = Logs::count();
            Logs::truncate();

            $this->log(3, '清空所有日志记录：' . $count . '条');

            return json_success("成功清空 {$count} 条日志记录");
        } catch (\Exception $e) {
            $this->log(8, 'LogsController.clear.' . $e->getMessage());
            return json_fail('清空日志失败');
        }
    }

    /**
     * 导出日志
     */
    public function export(Request $request)
    {
        try {
            $query = Logs::with('user')->orderBy('created_at', 'desc');

            // 应用搜索条件
            if ($request->has('keyword') && $request->keyword) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('content', 'like', "%{$keyword}%")
                      ->orWhereHas('user', function ($userQuery) use ($keyword) {
                          $userQuery->where('username', 'like', "%{$keyword}%");
                      });
                });
            }

            if ($request->has('type') && $request->type !== null) {
                $query->where('type', $request->type);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $logs = $query->get();

            // 生成CSV内容
            $csvContent = "ID,操作类型,操作内容,操作用户,操作时间\n";
            foreach ($logs as $log) {
                $typeNames = [
                    0 => '查询',
                    1 => '新增',
                    2 => '修改',
                    3 => '删除',
                    4 => '登录',
                    5 => '退出',
                    6 => '导出',
                    7 => '导入',
                    8 => '错误'
                ];

                $typeName = $typeNames[$log->type] ?? '其他';
                $username = $log->user ? $log->user->username : 'System';

                $csvContent .= sprintf(
                    "%d,%s,%s,%s,%s\n",
                    $log->id,
                    $typeName,
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $log->content),
                    $username,
                    $log->created_at
                );
            }

            $this->log(6, '导出日志记录：' . count($logs) . '条');

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="system_logs_' . date('Y-m-d') . '.csv"');

        } catch (\Exception $e) {
            $this->log(8, 'LogsController.export.' . $e->getMessage());
            return json_fail('导出日志失败');
        }
    }

    /**
     * 获取操作类型列表
     */
    public function getTypes()
    {
        try {
            $types = [
                ['value' => 0, 'label' => '查询'],
                ['value' => 1, 'label' => '新增'],
                ['value' => 2, 'label' => '修改'],
                ['value' => 3, 'label' => '删除'],
                ['value' => 4, 'label' => '登录'],
                ['value' => 5, 'label' => '退出'],
                ['value' => 6, 'label' => '导出'],
                ['value' => 7, 'label' => '导入'],
                ['value' => 8, 'label' => '错误']
            ];

            return json_success('获取成功', $types);
        } catch (\Exception $e) {
            return json_fail('获取操作类型失败');
        }
    }

    /**
     * 获取操作用户列表
     */
    public function getUsers()
    {
        try {
            $users = DB::table('logs')
                ->join('users', 'logs.user_id', '=', 'users.id')
                ->select('users.id', 'users.username', 'users.real_name')
                ->distinct()
                ->get();

            return json_success('获取成功', $users);
        } catch (\Exception $e) {
            return json_fail('获取操作用户失败');
        }
    }

    /**
     * 兼容旧方法
     */
    public function getList()
    {
        return $this->index(request());
    }
}
