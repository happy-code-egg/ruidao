<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * 系统日志模型
 * 用于记录系统操作日志，包括用户操作、请求信息和执行结果等
 */
class Logs extends Model
{
    public $table='logs';

    protected $fillable = [
        'user_id',          // 用户ID
        'type',             // 操作类型
        'content',          // 操作内容
        'title',            // 操作标题
        'method',           // 调用方法
        'request_method',   // HTTP请求方法
        'ip_address',       // IP地址
        'location',         // 位置信息
        'url',              // 请求URL
        'request_param',    // 请求参数
        'json_result',      // 返回结果（JSON格式）
        'error_msg',        // 错误信息
        'status',           // 执行状态
        'execution_time',   // 执行时间（毫秒）
        'user_agent',       // 用户代理
        'created_at',       // 创建时间
        'updated_at'        // 更新时间
    ];

    protected $casts = [
        'user_id' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
        'execution_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 操作类型常量
     */
    const TYPE_QUERY = 0;   // 查询操作
    const TYPE_INSERT = 1;  // 新增操作
    const TYPE_UPDATE = 2;  // 修改操作
    const TYPE_DELETE = 3;  // 删除操作
    const TYPE_LOGIN = 4;   // 登录操作
    const TYPE_LOGOUT = 5;  // 退出操作
    const TYPE_EXPORT = 6;  // 导出操作
    const TYPE_IMPORT = 7;  // 导入操作
    const TYPE_ERROR = 8;   // 错误操作

    /**
     * 状态常量
     */
    const STATUS_FAILED = 0;  // 失败
    const STATUS_SUCCESS = 1; // 成功

    //验证规则
    public $rules = [
    ];
    //未通过验证返回的错误消息
    public $messages = [
    ];
    /**
     * 关联用户
     * 通过 `user_id` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    /**
     * 获取操作类型文本
     * 将 type 字段值转换为对应的中文操作类型文本
     * @return string 操作类型文本（查询、新增、修改等）
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_QUERY => '查询',
            self::TYPE_INSERT => '新增',
            self::TYPE_UPDATE => '修改',
            self::TYPE_DELETE => '删除',
            self::TYPE_LOGIN => '登录',
            self::TYPE_LOGOUT => '退出',
            self::TYPE_EXPORT => '导出',
            self::TYPE_IMPORT => '导入',
            self::TYPE_ERROR => '错误',
        ];

        return $types[$this->type] ?? '其他';
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（成功或失败）
     */
    public function getStatusTextAttribute()
    {
        return $this->status == self::STATUS_SUCCESS ? '成功' : '失败';
    }

    /**
     * 获取操作类型代码
     * 将 type 字段值转换为对应的英文大写操作类型代码
     * @return string 操作类型代码（QUERY、INSERT、UPDATE等）
     */
    public function getTypeCodeAttribute()
    {
        $codes = [
            self::TYPE_QUERY => 'QUERY',
            self::TYPE_INSERT => 'INSERT',
            self::TYPE_UPDATE => 'UPDATE',
            self::TYPE_DELETE => 'DELETE',
            self::TYPE_LOGIN => 'LOGIN',
            self::TYPE_LOGOUT => 'LOGOUT',
            self::TYPE_EXPORT => 'EXPORT',
            self::TYPE_IMPORT => 'IMPORT',
            self::TYPE_ERROR => 'ERROR',
        ];

        return $codes[$this->type] ?? 'OTHER';
    }

    /**
     * 记录操作日志
     * 创建一条新的系统日志记录，自动填充当前请求信息
     * @param int $type 操作类型
     * @param string $content 操作内容
     * @param array $options 可选参数（title、method、location、result、error、status、execution_time等）
     * @return static 创建的日志记录实例
     */
    public static function record($type, $content, $options = [])
    {
        $request = request();
        $user = auth()->user();

        return self::create([
            'user_id' => $user ? $user->id : null,
            'type' => $type,
            'content' => $content,
            'title' => $options['title'] ?? $content,
            'method' => $options['method'] ?? class_basename(debug_backtrace()[1]['class'] ?? '') . '::' . (debug_backtrace()[1]['function'] ?? ''),
            'request_method' => $request->method(),
            'ip_address' => $request->ip(),
            'location' => $options['location'] ?? '内网IP',
            'url' => $request->fullUrl(),
            'request_param' => json_encode($request->all()),
            'json_result' => $options['result'] ?? null,
            'error_msg' => $options['error'] ?? null,
            'status' => $options['status'] ?? self::STATUS_SUCCESS,
            'execution_time' => $options['execution_time'] ?? null,
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * 序列化日期时间
     * 自定义日期时间序列化格式，统一输出为 Y-m-d H:i:s 格式
     * @param DateTimeInterface $date 日期时间接口实例
     * @return string 格式化的日期时间字符串
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 获取JSON结果属性
     * 将 json_result 字段的JSON字符串解析为关联数组
     * @param string $value 原始JSON字符串
     * @return array|null 解析后的数组或null
     */
    public function getJsonResultAttribute($value)
    {
        return json_decode($value, true);
    }

}
