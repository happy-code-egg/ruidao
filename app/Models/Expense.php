<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'expense_no',
        'expense_name',
        'customer_id',
        'customer_name',
        'company_id',
        'company_name',
        'total_amount',
        'expense_date',
        'status',
        'creator_id',
        'creator_name',
        'modifier_id',
        'modifier_name',
        'remark',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expense_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联支出单项目
     */
    public function items()
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    /**
     * 关联支出单历史
     */
    public function history()
    {
        return $this->hasMany(ExpenseHistory::class, 'expense_id');
    }

    /**
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 关联公司
     */
    public function company()
    {
        return $this->belongsTo(OurCompanies::class, 'company_id');
    }
}

