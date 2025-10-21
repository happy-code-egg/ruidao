<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Logs;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // 执行请求
        $response = $next($request);
        
        // 计算执行时间
        $executionTime = round((microtime(true) - $startTime) * 1000);
        
        // 记录API请求日志
        $this->logApiRequest($request, $response, $executionTime);
        
        return $response;
    }

    /**
     * 记录API请求日志
     */
    private function logApiRequest($request, $response, $executionTime)
    {
        try {
            // 跳过某些不需要记录的请求
            if ($this->shouldSkipLogging($request)) {
                return;
            }

            $user = auth()->user();
            $responseData = $response->getContent();
            $responseArray = json_decode($responseData, true);
            
            // 判断操作类型
            $type = $this->getOperationType($request);
            
            // 判断操作状态
            $status = $this->getOperationStatus($response, $responseArray);
            
            // 生成操作标题
            $title = $this->getOperationTitle($request);
            
            // 生成操作内容
            $content = $this->getOperationContent($request, $responseArray);
            
            // 记录日志
            Logs::create([
                'user_id' => $user ? $user->id : null,
                'type' => $type,
                'content' => $content,
                'title' => $title,
                'method' => $this->getControllerMethod($request),
                'request_method' => $request->method(),
                'ip_address' => $request->ip(),
                'location' => $this->getLocationByIp($request->ip()),
                'url' => $request->fullUrl(),
                'request_param' => json_encode($this->filterSensitiveData($request->all())),
                'json_result' => $responseData,
                'error_msg' => $status === Logs::STATUS_FAILED ? ($responseArray['msg'] ?? '') : null,
                'status' => $status,
                'execution_time' => $executionTime,
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // 记录日志失败时使用Laravel默认日志
            \Log::error('API日志记录失败: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * 判断是否跳过日志记录
     */
    private function shouldSkipLogging($request)
    {
        $skipPaths = [
            '/api/logs',           // 日志查询本身不记录
            '/api/health',         // 健康检查
            '/api/heartbeat',      // 心跳检测
        ];

        $path = $request->path();
        foreach ($skipPaths as $skipPath) {
            if (strpos($path, $skipPath) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取操作类型
     */
    private function getOperationType($request)
    {
        $method = $request->method();
        
        switch ($method) {
            case 'GET':
                return Logs::TYPE_QUERY;
            case 'POST':
                return Logs::TYPE_INSERT;
            case 'PUT':
            case 'PATCH':
                return Logs::TYPE_UPDATE;
            case 'DELETE':
                return Logs::TYPE_DELETE;
            default:
                return Logs::TYPE_QUERY;
        }
    }

    /**
     * 获取操作状态
     */
    private function getOperationStatus($response, $responseArray)
    {
        $httpStatus = $response->getStatusCode();
        
        if ($httpStatus >= 200 && $httpStatus < 300) {
            // HTTP状态正常，检查业务状态
            if (isset($responseArray['code'])) {
                return $responseArray['code'] === 0 ? Logs::STATUS_SUCCESS : Logs::STATUS_FAILED;
            }
            return Logs::STATUS_SUCCESS;
        }
        
        return Logs::STATUS_FAILED;
    }

    /**
     * 获取操作标题
     */
    private function getOperationTitle($request)
    {
        $path = $request->path();
        
        $titleMap = [
            'permissions' => '权限管理',
            'departments' => '部门管理',
            'users' => '用户管理',
            'roles' => '角色管理',
            'workflows' => '工作流管理',
            'notification-rules' => '通知规则管理',
            'process-rules' => '处理规则管理',
            'login' => '用户认证',
            'logout' => '用户认证',
        ];

        foreach ($titleMap as $key => $title) {
            if (strpos($path, $key) !== false) {
                return $title;
            }
        }

        return '系统操作';
    }

    /**
     * 获取操作内容
     */
    private function getOperationContent($request, $responseArray)
    {
        $method = $request->method();
        $path = $request->path();
        
        $action = [
            'GET' => '查看',
            'POST' => '创建',
            'PUT' => '更新',
            'PATCH' => '更新',
            'DELETE' => '删除',
        ][$method] ?? '操作';

        // 从路径中提取资源名称
        if (preg_match('/\/api\/([^\/]+)/', $path, $matches)) {
            $resource = $matches[1];
            $resourceMap = [
                'permissions' => '权限',
                'departments' => '部门',
                'users' => '用户',
                'roles' => '角色',
                'workflows' => '工作流',
                'notification-rules' => '通知规则',
                'process-rules' => '处理规则',
            ];
            
            $resourceName = $resourceMap[$resource] ?? $resource;
            return "{$action}{$resourceName}";
        }

        return $action . '系统资源';
    }

    /**
     * 获取控制器方法
     */
    private function getControllerMethod($request)
    {
        $route = $request->route();
        if ($route) {
            $action = $route->getAction();
            if (isset($action['controller'])) {
                return $action['controller'];
            }
        }
        
        return 'Unknown';
    }

    /**
     * 过滤敏感数据
     */
    private function filterSensitiveData($data)
    {
        $sensitiveFields = ['password', 'old_password', 'new_password', 'password_confirmation'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }
        
        return $data;
    }

    /**
     * 根据IP获取地理位置
     */
    private function getLocationByIp($ip)
    {
        // 内网IP判断
        if ($ip === '127.0.0.1' || $ip === '::1' || 
            preg_match('/^192\.168\./', $ip) || 
            preg_match('/^10\./', $ip) || 
            preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $ip)) {
            return '内网IP';
        }
        
        return '未知位置';
    }
}
