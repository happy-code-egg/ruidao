<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractService extends Model
{
    use SoftDeletes;

    protected $table = 'contract_services';

    protected $fillable = [
        'contract_id',
        'service_name',
        'service_description',
        'amount',
        'official_fee',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'contract_id' => 'integer',
        'amount' => 'decimal:2',
        'official_fee' => 'decimal:2',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取所属合同
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取总金额
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->official_fee;
    }
}
