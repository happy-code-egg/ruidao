<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 佣金配置模型
 * 用于管理系统中的佣金配置规则
 */
class CommissionConfig extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'commission_configs';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'config_name',   // 配置名称
        'config_type',   // 配置类型
        'level',         // 等级
        'base_rate',     // 基础费率
        'bonus_rate',    // 奖金费率
        'min_amount',    // 最小金额
        'max_amount',    // 最大金额
        'status',        // 状态
        'remark'         // 备注
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'base_rate' => 'decimal:2',           // 基础费率转换为保留2位小数的浮点数
        'bonus_rate' => 'decimal:2',          // 奖金费率转换为保留2位小数的浮点数
        'min_amount' => 'decimal:2',          // 最小金额转换为保留2位小数的浮点数
        'max_amount' => 'decimal:2',          // 最大金额转换为保留2位小数的浮点数
        'created_at' => 'datetime:Y-m-d H:i:s', // 创建时间格式化
        'updated_at' => 'datetime:Y-m-d H:i:s'  // 更新时间格式化
    ];

    /**
     * 要追加到模型数组表单的访问器
     * @var array
     */
    protected $appends = ['createTime', 'updateTime'];

    /**
     * 获取创建时间的访问器
     * 将 `created_at` 格式化为 'Y-m-d H:i:s' 格式
     * @return string|null
     */
    public function getCreateTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取更新时间的访问器
     * 将 `updated_at` 格式化为 'Y-m-d H:i:s' 格式
     * @return string|null
     */
    public function getUpdateTimeAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * 获取数组形式的属性
     * 重写父类方法，将字段名从 snake_case 转换为 camelCase
     * @return array
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
     * @param string $str 输入的 snake_case 字符串
     * @return string 返回 camelCase 格式的字符串
     */
    private function snakeToCamel($str)
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }
}
