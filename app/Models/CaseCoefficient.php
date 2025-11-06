<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseCoefficient extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_coefficients';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 系数名称
        'sort',           // 排序字段
        'is_valid',       // 是否有效(1:有效, 0:无效)
        'created_by',     // 创建者ID
        'updated_by',     // 更新者ID
        'created_at',     // 创建时间
        'updated_at',     // 更新时间
    ];

    // 字段类型转换定义
    protected $casts = [
        'sort' => 'integer',        // 排序 - 整数类型
        'is_valid' => 'integer',    // 是否有效 - 整数类型
        'created_by' => 'integer',  // 创建者ID - 整数类型
        'updated_by' => 'integer',  // 更新者ID - 整数类型
        'created_at' => 'datetime', // 创建时间 - 日期时间类型
        'updated_at' => 'datetime', // 更新时间 - 日期时间类型
    ];

    /**
     * 获取创建者
     * 建立与 User 模型的一对多反向关联，关联记录创建者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新者
     * 建立与 User 模型的一对多反向关联，关联记录更新者
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 作用域：有效的记录
     * 查询作用域 - 只获取有效状态(is_valid=1)的记录
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', 1);
    }

    /**
     * 作用域：按排序查询
     * 查询作用域 - 按 sort 字段升序排列
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc');
    }

    /**
     * 获取状态文本
     * 根据 is_valid 字段值返回对应的中文状态描述
     */
    public function getIsValidTextAttribute()
    {
        return $this->is_valid ? '是' : '否';
    }
}
