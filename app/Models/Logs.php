<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    public $table='logs';

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'title',
        'method',
        'request_method',
        'ip_address',
        'location',
        'url',
        'request_param',
        'json_result',
        'error_msg',
        'status',
        'execution_time',
        'user_agent',
        'created_at',
        'updated_at'
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
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    /**
     * 获取操作类型文本
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
     */
    public function getStatusTextAttribute()
    {
        return $this->status == self::STATUS_SUCCESS ? '成功' : '失败';
    }

    /**
     * 获取操作类型代码（前端格式）
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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getJsonResultAttribute($value)
    {
        return json_decode($value, true);
    }

}
