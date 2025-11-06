<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $table = 'expense_items';

    protected $fillable = [
        'expense_id',
        'request_no',
        'request_date',
        'our_no',
        'case_name',
        'client_name',
        'applicant',
        'process_item',
        'expense_name',
        'payable_amount',
        'payment_date',
        'actual_pay_date',
        'expense_remark',
        'cooperative_agency',
        'expense_type',
    ];

    protected $casts = [
        'payable_amount' => 'decimal:2',
        'request_date' => 'date',
        'payment_date' => 'date',
        'actual_pay_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联支出单
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}//这里

