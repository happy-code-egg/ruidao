<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 佣金设置模型
 * 用于管理佣金计算规则和配置
 */
class CommissionSettings extends Model
{
    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'commission_settings';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'name',                // 设置名称
        'code',                // 设置编码
        'handler_level',       // 处理者等级
        'case_type',           // 案件类型
        'business_type',       // 业务类型
        'application_type',    // 申请类型
        'case_coefficient',    // 案件系数
        'matter_coefficient',  // 事项系数
        'processing_matter',   // 处理事项
        'case_stage',          // 案件阶段
        'commission_type',     // 佣金类型
        'piece_ratio',         // 件数比例
        'piece_points',        // 件数积分
        'country',             // 国家
        'rate',                // 费率
        'status',              // 状态(1为启用，其他为禁用)
        'sort_order',          // 排序字段
        'description',         // 描述
        'updater'              // 更新人
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'status' => 'integer',              // 状态字段转换为整数
        'sort_order' => 'integer',          // 排序字段转换为整数
        'case_coefficient' => 'string',     // 案件系数转换为字符串
        'matter_coefficient' => 'string'    // 事项系数转换为字符串
    ];

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
     * 关联创建人信息
     * 建立与 `User` 模型的反向一对一关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * 获取创建时间的访问器
     * 格式化创建时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取更新时间的访问器
     * 格式化更新时间为 'Y-m-d H:i:s' 格式
     * @param string $value 原始时间值
     * @return string 格式化后的时间
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDate($value)->format('Y-m-d H:i:s');
    }
}
