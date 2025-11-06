<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 支出单模型
 * 用于管理支出单的基本信息和关联数据
 */
class Expense extends Model
{
    // 使用软删除功能，允许记录被"删除"而不实际从数据库中移除
    use SoftDeletes;

    // 指定数据库表名
    protected $table = 'expenses';

    // 定义可批量赋值的字段
    protected $fillable = [
        'expense_no',        // 支出单编号
        'expense_name',      // 支出单名称
        'customer_id',       // 客户ID
        'customer_name',     // 客户名称
        'company_id',        // 公司ID
        'company_name',      // 公司名称
        'total_amount',      // 总金额
        'expense_date',      // 支出日期
        'status',            // 状态
        'creator_id',        // 创建人ID
        'creator_name',      // 创建人名称
        'modifier_id',       // 修改人ID
        'modifier_name',     // 修改人名称
        'remark',            // 备注
    ];

    // 定义字段类型转换
    protected $casts = [
        'total_amount' => 'decimal:2',   // 总金额转为保留2位小数的decimal类型
        'expense_date' => 'date',        // 支出日期转为日期类型
        'created_at' => 'datetime',      // 创建时间转为日期时间类型
        'updated_at' => 'datetime',      // 更新时间转为日期时间类型
    ];

    /**
     * 关联支出单项目
     * 通过 `expense_id` 字段关联 `ExpenseItem` 模型
     */
    public function items()
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    /**
     * 关联支出单历史
     * 通过 `expense_id` 字段关联 `ExpenseHistory` 模型
     */
    public function history()
    {
        return $this->hasMany(ExpenseHistory::class, 'expense_id');
    }

    /**
     * 关联客户信息
     * 通过 `customer_id` 字段关联 `Customer` 模型
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联公司信息
     * 通过 `company_id` 字段关联 `OurCompanies` 模型
     */
    public function company()
    {
        return $this->belongsTo(OurCompanies::class, 'company_id');
    }
}
