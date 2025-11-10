<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 支出单历史记录模型
 * 用于记录支出单的各种操作历史
 */
class ExpenseHistory extends Model
{
    // 指定数据库表名
    protected $table = 'expense_history';

    // 禁用时间戳自动管理，因为手动控制 created_at 字段
    public $timestamps = false;

    // 定义可批量赋值的字段
    protected $fillable = [
        'expense_id',      // 支出单ID
        'operation',       // 操作类型
        'operator_id',     // 操作人ID
        'operator_name',   // 操作人名称
        'remark',          // 备注信息
        'created_at',      // 创建时间
    ];

    // 定义字段类型转换
    protected $casts = [
        'created_at' => 'datetime',  // 创建时间转为日期时间类型
    ];

    /**
     * 关联支出单信息
     * 通过 `expense_id` 字段关联 `Expense` 模型
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
