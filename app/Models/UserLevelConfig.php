<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLevelConfig extends Model
{
    use SoftDeletes;

    protected $table = 'user_level_configs';

    protected $fillable = [
        'level_name',
        'level_code',
        'level_order',
        'user_type',
        'min_experience',
        'max_experience',
        'base_salary',
        'required_skills',
        'description',
        'status',
        'remark'
    ];

    protected $casts = [
        'required_skills' => 'array',
        'base_salary' => 'decimal:2',
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

