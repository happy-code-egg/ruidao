<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessServiceTypes extends Model
{
    // 指定对应的数据库表名
    protected $table = 'business_service_types';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 服务类型名称
        'code',           // 服务类型编码
        'category',       // 服务分类
        'description',    // 描述信息
        'status',         // 状态(1:启用, 0:禁用)
        'sort_order',     // 排序顺序
        'created_by',     // 创建者ID
        'updated_by',     // 更新者ID
        'created_at',     // 创建时间
        'updated_at'      // 更新时间
    ];

    // 字段类型转换定义
    protected $casts = [
        'status' => 'integer',      // 状态 - 整数类型
        'sort_order' => 'integer',  // 排序顺序 - 整数类型
        'created_by' => 'integer',  // 创建者ID - 整数类型
        'updated_by' => 'integer',  // 更新者ID - 整数类型
        'created_at' => 'datetime', // 创建时间 - 日期时间类型
        'updated_at' => 'datetime'  // 更新时间 - 日期时间类型
    ];

    /**
     * 格式化 created_at 时间
     * 将创建时间格式化为 Y-m-d H:i:s 格式
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 格式化 updated_at 时间
     * 将更新时间格式化为 Y-m-d H:i:s 格式
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取状态文本
     * 根据 status 字段值返回对应的中文状态描述
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 查询作用域 - 只获取状态为启用(status=1)的记录
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序
     * 查询作用域 - 按 sort_order 字段和 id 字段进行升序排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 创建者关联
     * 建立与 User 模型的反向关联，通过 created_by 字段关联用户的 id 字段
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * 更新者关联
     * 建立与 User 模型的反向关联，通过 updated_by 字段关联用户的 id 字段
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * 处理API响应格式
     * 自定义模型数组序列化格式，指定返回给API的字段及其格式
     */
    public function toArray()
    {
        $array = parent::toArray();
        return [
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'category' => $this->category,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',  // 获取创建者真实姓名，如果不存在则返回空字符串
            'updated_by' => $this->updater->real_name ?? ''   // 获取更新者真实姓名，如果不存在则返回空字符串
        ];
    }
}
