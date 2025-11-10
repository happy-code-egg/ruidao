<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 佣金类型模型
 * 用于管理不同类型的佣金配置信息
 */
class CommissionTypes extends Model
{
    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'commission_types';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'name',          // 佣金类型名称
        'code',          // 佣金类型编码
        'rate',          // 费率
        'description',   // 描述信息
        'status',        // 状态(1为启用，其他为禁用)
        'sort_order',    // 排序字段
        'created_by',    // 创建人ID
        'updated_by'     // 更新人ID
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'status' => 'integer',      // 状态字段转换为整数
        'sort_order' => 'integer',  // 排序字段转换为整数
        'created_by' => 'integer',  // 创建人ID转换为整数
        'updated_by' => 'integer'   // 更新人ID转换为整数
    ];

    /**
     * 获取创建时间的访问器
     * 格式化创建时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始创建时间值
     * @return string 格式化后的创建时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联创建人信息
     * 建立与 `User` 模型的反向一对一关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取更新时间的访问器
     * 格式化更新时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始更新时间值
     * @return string 格式化后的更新时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 关联更新人信息
     * 建立与 `User` 模型的反向一对一关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取状态文本的访问器
     * 将数字状态转换为中文文本显示
     * @return string 状态文本('启用'或'禁用')
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 启用状态范围查询
     * 查询状态为启用(值为1)的记录
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 排序范围查询
     * 按照 `sort_order` 字段和 `id` 进行升序排列
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 自定义模型数组输出格式
     * 定义模型转换为数组时的字段结构和数据
     * @return array 包含模型数据的数组
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'rate' => $this->rate,
            'description' => $this->description,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',  // 获取创建人真实姓名，不存在则返回空字符串
            'updated_by' => $this->updater->real_name ?? '',  // 获取更新人真实姓名，不存在则返回空字符串
        ];
    }
}
