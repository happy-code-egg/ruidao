<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
   /**
 * 获取日志列表 index
 *
 * 功能描述：获取系统操作日志列表，支持多种筛选条件和分页
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - keyword (string, optional): 关键词搜索条件（匹配内容、标题或用户名）
 *   - username (string, optional): 用户名搜索条件
 *   - status (int, optional): 状态筛选条件
 *   - type (int, optional): 操作类型筛选条件
 *   - user_id (int, optional): 用户ID筛选条件
 *   - start_date (string, optional): 开始日期筛选条件（格式：Y-m-d）
 *   - end_date (string, optional): 结束日期筛选条件（格式：Y-m-d）
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 日志列表数据
 *     - id (int): 日志ID
 *     - title (string): 日志标题
 *     - type (int): 操作类型
 *     - type_text (string): 操作类型文本
 *     - type_code (string): 操作类型代码
 *     - method (string): 操作方法
 *     - request_method (string): 请求方法
 *     - requestMethod (string): 请求方法（别名）
 *     - username (string): 操作用户名称
 *     - user_id (int): 用户ID
 *     - ip (string): IP地址
 *     - ip_address (string): IP地址（别名）
 *     - location (string): 地理位置
 *     - status (int): 状态
 *     - status_text (string): 状态文本
 *     - operTime (string): 操作时间
 *     - created_at (string): 创建时间
 *     - url (string): 请求URL
 *     - request_param (string): 请求参数
 *     - requestParam (string): 请求参数（别名）
 *     - json_result (string): JSON结果
 *     - jsonResult (string): JSON结果（别名）
 *     - error_msg (string): 错误信息
 *     - errorMsg (string): 错误信息（别名）
 *     - execution_time (int): 执行时间
 *     - content (string): 日志内容
 *     - user_agent (string): 用户代理
 *     - user (object): 用户信息
 *       - id (int): 用户ID
 *       - username (string): 用户名
 *       - name (string): 用户姓名
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
public function index(Request $request)
{
    try {
        // 初始化查询构建器，预加载用户关联关系，按创建时间倒序排序
        $query = Logs::with('user')->orderBy('created_at', 'desc');

        // 关键词搜索条件（匹配内容、标题或用户名）
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

        // 分页参数处理
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);
        $total = $query->count();

        // 执行分页查询
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

        // 返回分页响应
        return json_page($formattedLogs, $total, '获取成功');
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(8, 'LogsController.index.' . $e->getMessage());
        return json_fail('获取日志列表失败: ' . $e->getMessage());
    }
}


   /**
 * 获取模拟日志数据 getMockLogData
 *
 * 功能描述：生成模拟的日志数据，用于在没有真实数据时提供演示数据
 *
 * 传入参数：
 * - request (Request): HTTP请求对象，包含分页和筛选参数
 *   - page (int, optional): 页码，默认为1
 *   - limit (int, optional): 每页数量，默认为15
 *   - keyword (string, optional): 关键词搜索条件
 *   - type (int, optional): 日志类型筛选条件
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据
 *   - list (array): 模拟日志列表数据
 *     - id (int): 日志ID
 *     - content (string): 日志内容
 *     - type (int): 操作类型
 *     - user_id (int): 用户ID
 *     - ip_address (string): IP地址
 *     - user_agent (string): 用户代理
 *     - created_at (string): 创建时间
 *     - updated_at (string): 更新时间
 *     - user (array): 用户信息
 *       - id (int): 用户ID
 *       - username (string): 用户名
 *       - real_name (string): 用户真实姓名
 *   - total (int): 总记录数
 *   - page (int): 当前页码
 *   - limit (int): 每页数量
 *   - pages (int): 总页数
 */
private function getMockLogData(Request $request)
{
    // 获取分页参数
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

    // 生成100条模拟日志记录
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

    // 应用关键词搜索过滤
    if ($request->has('keyword') && $request->keyword) {
        $keyword = $request->keyword;
        $mockLogs = array_filter($mockLogs, function($log) use ($keyword) {
            return stripos($log['content'], $keyword) !== false ||
                   stripos($log['user']['username'], $keyword) !== false;
        });
    }

    // 应用类型筛选过滤
    if ($request->has('type') && $request->type !== null) {
        $mockLogs = array_filter($mockLogs, function($log) use ($request) {
            return $log['type'] == $request->type;
        });
    }

    // 重新索引数组并计算总数
    $mockLogs = array_values($mockLogs);
    $total = count($mockLogs);

    // 分页处理
    $start = ($page - 1) * $limit;
    $mockLogs = array_slice($mockLogs, $start, $limit);

    // 返回分页响应
    return json_page($mockLogs, $total, '获取成功（模拟数据）');
}

/**
 * 获取日志详情 show
 *
 * 功能描述：根据ID获取单条日志的详细信息
 *
 * 传入参数：
 * - id (int): 日志记录的ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 日志详细信息
 *   - id (int): 日志ID
 *   - content (string): 日志内容
 *   - type (int): 操作类型
 *   - user_id (int): 用户ID
 *   - ip_address (string): IP地址
 *   - user_agent (string): 用户代理
 *   - created_at (string): 创建时间
 *   - updated_at (string): 更新时间
 *   - user (object): 关联的用户信息
 *     - id (int): 用户ID
 *     - username (string): 用户名
 *     - real_name (string): 用户真实姓名
 */
public function show($id)
{
    try {
        // 根据ID查找日志记录，并预加载用户关联信息
        $log = Logs::with('user')->find($id);

        // 如果日志不存在，返回失败响应
        if (!$log) {
            return json_fail('日志不存在');
        }

        // 返回成功响应，包含日志详细信息
        return json_success('获取成功', $log);
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(8, 'LogsController.show.' . $e->getMessage());
        return json_fail('获取日志详情失败');
    }
}

/**
 * 删除日志 destroy
 *
 * 功能描述：根据ID删除单条日志记录
 *
 * 传入参数：
 * - id (int): 要删除的日志记录ID
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 */
public function destroy($id)
{
    try {
        // 根据ID查找日志记录
        $log = Logs::find($id);

        // 如果日志不存在，返回失败响应
        if (!$log) {
            return json_fail('日志不存在');
        }

        // 删除日志记录
        $log->delete();

        // 记录操作日志
        $this->log(3, '删除日志记录：' . $log->content);

        // 返回成功响应
        return json_success('删除成功');
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(8, 'LogsController.destroy.' . $e->getMessage());
        return json_fail('删除日志失败');
    }
}

/**
 * 批量删除日志 batchDelete
 *
 * 功能描述：批量删除多条日志记录
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - ids (array): 要删除的日志记录ID数组
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息，包含删除记录数
 */
public function batchDelete(Request $request)
{
    try {
        // 获取要删除的日志ID数组
        $ids = $request->input('ids', []);

        // 如果没有选择日志，返回失败响应
        if (empty($ids)) {
            return json_fail('请选择要删除的日志');
        }

        // 批量删除日志记录
        $count = Logs::whereIn('id', $ids)->delete();

        // 记录操作日志
        $this->log(3, '批量删除日志记录：' . $count . '条');

        // 返回成功响应，包含删除记录数
        return json_success("成功删除 {$count} 条日志记录");
    } catch (\Exception $e) {
        // 记录错误日志并返回失败响应
        $this->log(8, 'LogsController.batchDelete.' . $e->getMessage());
        return json_fail('批量删除日志失败');
    }
}


     /**
 * 清空日志 clear
 *
 * 功能描述：清空系统中所有的操作日志记录
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息，包含清空的记录数
 */
    public function clear()
    {
        try {
            // 统计当前日志总数
            $count = Logs::count();
            // 清空所有日空记录
            Logs::truncate();

            // 记录操作日志，类型3表示删除操作
            $this->log(3, '清空所有日志记录：' . $count . '条');

            // 返回成功响应，包含清空的记录数
            return json_success("成功清空 {$count} 条日志记录");
        } catch (\Exception $e) {
            // 记录错误日志，类型8表示错误操作
            $this->log(8, 'LogsController.clear.' . $e->getMessage());
            // 返回失败响应
            return json_fail('清空日志失败');
        }
    }

    /**
 * 导出日志 export
 *
 * 功能描述：根据筛选条件导出系统操作日志为CSV文件
 *
 * 传入参数：
 * - request (Request): HTTP请求对象
 *   - keyword (string, optional): 关键词搜索条件（匹配内容或用户名）
 *   - type (int, optional): 操作类型筛选条件
 *   - start_date (string, optional): 开始日期筛选条件（格式：Y-m-d）
 *   - end_date (string, optional): 结束日期筛选条件（格式：Y-m-d）
 *
 * 输出参数：
 * - CSV文件: 包含日志记录的CSV格式文件
 *   - Content-Type: text/csv; charset=UTF-8
 *   - Content-Disposition: attachment; filename="system_logs_YYYY-MM-DD.csv"
 */
    public function export(Request $request)
    {
        try {
            // 初始化查询构建器，预加载用户关联关系，按创建时间倒序排序
            $query = Logs::with('user')->orderBy('created_at', 'desc');

            // 应用关键词搜索条件（匹配内容或用户名）
            if ($request->has('keyword') && $request->keyword) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('content', 'like', "%{$keyword}%")
                      ->orWhereHas('user', function ($userQuery) use ($keyword) {
                          $userQuery->where('username', 'like', "%{$keyword}%");
                      });
                });
            }

            // 应用操作类型筛选条件
            if ($request->has('type') && $request->type !== null) {
                $query->where('type', $request->type);
            }

            // 应用日期范围筛选条件
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // 执行查询获取日志记录
            $logs = $query->get();

            // 生成CSV头部
            $csvContent = "ID,操作类型,操作内容,操作用户,操作时间\n";

            // 定义操作类型映射关系
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

            // 遍历日志记录，生成CSV内容行
            foreach ($logs as $log) {
                // 获取操作类型文本
                $typeName = $typeNames[$log->type] ?? '其他';
                // 获取用户名，如果不存在则显示System
                $username = $log->user ? $log->user->username : 'System';

                // 格式化CSV行数据，处理特殊字符
                $csvContent .= sprintf(
                    "%d,%s,%s,%s,%s\n",
                    $log->id,
                    $typeName,
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $log->content),
                    $username,
                    $log->created_at
                );
            }

            // 记录操作日志，类型6表示导出操作
            $this->log(6, '导出日志记录：' . count($logs) . '条');

            // 返回CSV响应
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="system_logs_' . date('Y-m-d') . '.csv"');

        } catch (\Exception $e) {
            // 记录错误日志，类型8表示错误操作
            $this->log(8, 'LogsController.export.' . $e->getMessage());
            // 返回失败响应
            return json_fail('导出日志失败');
        }
    }

    /**
 * 获取操作类型列表 getTypes
 *
 * 功能描述：获取系统支持的操作类型列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 操作类型列表
 *   - value (int): 类型值
 *   - label (string): 类型标签
 */
    public function getTypes()
    {
        try {
            // 定义操作类型列表
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

            // 返回成功响应，包含操作类型列表
            return json_success('获取成功', $types);
        } catch (\Exception $e) {
            // 返回失败响应
            return json_fail('获取操作类型失败');
        }
    }

    /**
 * 获取操作用户列表 getUsers
 *
 * 功能描述：获取系统中所有执行过操作的用户列表
 *
 * 传入参数：无
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (array): 用户列表
 *   - id (int): 用户ID
 *   - username (string): 用户名
 *   - real_name (string): 用户真实姓名
 */
    public function getUsers()
    {
        try {
            // 通过关联查询获取操作用户列表
            $users = DB::table('logs')
                ->join('users', 'logs.user_id', '=', 'users.id')
                ->select('users.id', 'users.username', 'users.real_name')
                ->distinct()
                ->get();

            // 返回成功响应，包含用户列表
            return json_success('获取成功', $users);
        } catch (\Exception $e) {
            // 返回失败响应
            return json_fail('获取操作用户失败');
        }
    }

    /**
 * 兼容旧方法 getList
 *
 * 功能描述：兼容旧的日志列表接口方法
 *
 * 传入参数：无（使用当前请求对象）
 *
 * 输出参数：
 * - code (int): 状态码，0表示成功
 * - msg (string): 操作结果消息
 * - data (object): 分页数据（参考index方法）
 */
    public function getList()
    {
        // 调用index方法处理请求
        return $this->index(request());
    }

}
