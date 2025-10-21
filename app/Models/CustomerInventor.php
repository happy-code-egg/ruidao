<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInventor extends Model
{
    use SoftDeletes;

    protected $table = 'customer_inventors';

    protected $fillable = [
        'customer_id',
        'inventor_name_cn',
        'inventor_name_en',
        'inventor_code',
        'inventor_type',
        'gender',
        'id_type',
        'id_number',
        'country',
        'province',
        'city',
        'district',
        'street',
        'postal_code',
        'address',
        'address_en',
        'phone',
        'landline',
        'wechat',
        'email',
        'work_unit',
        'department',
        'position',
        'business_staff',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 获取客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * 获取创建人
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新人
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取完整地址
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->province, 
            $this->city, 
            $this->district, 
            $this->street,
            $this->address
        ]);
        return implode('', $parts);
    }

    /**
     * 生成发明人编号
     */
    public static function generateCode()
    {
        $prefix = 'INV';
        $lastInventor = static::withTrashed()
            ->where('inventor_code', 'like', $prefix . '%')
            ->orderBy('inventor_code', 'desc')
            ->first();

        if ($lastInventor) {
            $lastNumber = intval(substr($lastInventor->inventor_code, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
