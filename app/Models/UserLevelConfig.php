<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 用户等级配置模型
 * 用于管理系统中用户等级相关的配置信息，包括等级名称、经验要求和薪资等
 */
class UserLevelConfig extends Model
{
    use SoftDeletes;

    protected $table = 'user_level_configs';

    protected $fillable = [
        'level_name',       // 等级名称
        'level_code',       // 等级编码
        'level_order',      // 等级顺序
        'user_type',        // 用户类型
        'min_experience',   // 最低经验要求
        'max_experience',   // 最高经验要求
        'base_salary',      // 基础薪资
        'required_skills',  // 所需技能（数组形式）
        'description',      // 描述信息
        'status',           // 状态
        'remark'            // 备注
    ];

    protected $casts = [
        'required_skills' => 'array',
        'base_salary' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $appends = ['createTime', 'updateTime'];

    /**
     * 获取创建时间
     * 为API提供的格式化时间字段
     * @return string|null 格式化的时间字符串或null
     */
    public function getCreateTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取更新时间
     * 为API提供的格式化时间字段
     * @return string|null 格式化的时间字符串或null
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 自定义数组转换
     * 将模型转换为数组时，把字段名从snake_case转换为camelCase格式
     * @return array 转换后的数组数据
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
     * 将 snake_case 格式转换为 camelCase 格式
     * @param string $str 待转换的字符串
     * @return string 转换后的camelCase格式字符串
     */
    private function snakeToCamel($str)
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}

