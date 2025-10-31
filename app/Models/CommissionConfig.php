<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionConfig extends Model
{
    use SoftDeletes;

    protected $table = 'commission_configs';

    protected $fillable = [
        'config_name',
        'config_type',
        'level',
        'base_rate',
        'bonus_rate',
        'min_amount',
        'max_amount',
        'status',
        'remark'
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'bonus_rate' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $appends = ['createTime', 'updateTime'];

    public function getCreateTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    }

    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取数组形式的属性
     */
    public function toArray()
    {
        $array = parent::toArray();

        // 将 snake_case 转换为 camelCase
        $camelArray = [];
        foreach ($array as $key => $value) {
            $camelKey = $this->snakeToCamel($key);
            $camelArray[$camelKey] = $value;
        }

        return $camelArray;
    }

    /**
     * 将 snake_case 转换为 camelCase
     */
    private function snakeToCamel($str)
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}

