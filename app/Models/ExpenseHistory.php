<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseHistory extends Model
{
    protected $table = 'expense_history';

    public $timestamps = false;

    protected $fillable = [
        'expense_id',
        'operation',
        'operator_id',
        'operator_name',
        'remark',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * 关联支出单
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}

