<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 控制器基类
 * 所有业务控制器的父类，提供通用功能
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * 获取当前认证用户
     * @return mixed 返回当前登录用户对象
     */
    public function AuthUser(){
        return request()->user();
    }

    /**
     * 数据库查询结果分页处理
     * 根据请求参数对数据库查询结果进行分页，并返回格式化的分页数据
     * @param \Illuminate\Database\Eloquent\Builder $data 数据库查询构建器实例
     * @return \Illuminate\Http\JsonResponse 返回包含分页数据的JSON响应
     */
    public function page($data){
        $pageSize = request()->get('limit') ?: 10;
        $pageinfo = $data->paginate($pageSize);
        return json_page($pageinfo->items(),$pageinfo->total());
    }

    /**
     * 记录操作日志
     * @param $type 操作类型：0-查询 1-新增 2-修改 3-删除 4-登录 5-退出 6-导出 7-导入 8-错误
     * @param $content 操作内容
     * @param array $options 额外选项
     */
    public function log($type, $content, $options = []){
        $request = request();
        $user = $this->AuthUser();

        $log = new Logs();
        $log->user_id = $user ? $user->id : 1;
        $log->type = $type;
        $log->content = $content;
        $log->title = $options['title'] ?? $content;
        $log->method = $options['method'] ?? $this->getCallerMethod();
        $log->request_method = $request->method();
        $log->ip_address = $request->ip();
        $log->location = $options['location'] ?? $this->getLocationByIp($request->ip());
        $log->url = $request->fullUrl();
        $log->request_param = json_encode($request->all());
        $log->json_result = $options['result'] ?? null;
        $log->error_msg = $options['error'] ?? null;
        $log->status = $options['status'] ?? Logs::STATUS_SUCCESS;
        $log->execution_time = $options['execution_time'] ?? null;
        $log->user_agent = $request->userAgent();

        $log->save();
        return $log;
    }

    /**
     * 获取调用方法名
     * 通过调试回溯获取调用当前方法的类和方法名
     * @return string 返回调用方法的格式化名称，如 "ClassName::methodName"
     */
    private function getCallerMethod()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if (isset($trace[2])) {
            $class = class_basename($trace[2]['class'] ?? '');
            $function = $trace[2]['function'] ?? '';
            return "{$class}::{$function}";
        }
        return 'Unknown';
    }

    /**
     * 根据IP获取地理位置
     * 识别内网IP并处理外部IP的位置查询
     * @param string $ip IP地址
     * @return string 返回地理位置描述
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

        // 这里可以集成第三方IP定位服务
        return '未知位置';
    }
}
