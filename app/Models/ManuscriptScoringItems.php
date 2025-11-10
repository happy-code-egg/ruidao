<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文稿评分项模型
 * 用于管理文稿评分的各项评分标准和配置
 */
class ManuscriptScoringItems extends Model
{
    protected $table = 'manuscript_scoring_items';

    protected $fillable = [
        'sort',           // 排序号
        'name',           // 评分项名称
        'code',           // 评分项代码
        'major_category', // 主分类
        'minor_category', // 次分类
        'description',    // 描述
        'score',          // 分值
        'max_score',      // 最高分
        'weight',         // 权重
        'status',         // 状态（0:禁用, 1:启用）
        'sort_order',     // 排序顺序
        'updated_by',     // 更新人ID
        'created_by'      // 创建人ID
    ];

    protected $casts = [
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * 格式化创建时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取创建人
     * 通过 `created_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 格式化更新时间
     * 将时间戳转换为 Y-m-d H:i:s 格式
     * @param mixed $value 原始时间值
     * @return string 格式化的时间字符串
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * 获取更新人
     * 通过 `updated_by` 字段关联 `User` 模型
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 获取状态文本
     * 将 status 字段值转换为对应的中文状态文本
     * @return string 状态文本（启用或禁用）
     */
    public function getStatusTextAttribute()
    {
        return $this->status === 1 ? '启用' : '禁用';
    }

    /**
     * 作用域：启用状态
     * 查询 status = 1 的记录
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 作用域：按排序排列
     * 先按 sort_order 字段排序，再按 id 字段排序
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @return \Illuminate\Database\Eloquent\Builder 查询构建器
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 转换为数组格式
     * 自定义模型的数组序列化格式，包含关联数据和状态文本
     * @return array 格式化后的数组
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'name' => $this->name,
            'code' => $this->code,
            'major_category' => $this->major_category,
            'minor_category' => $this->minor_category,
            'description' => $this->description,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'weight' => $this->weight,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->creator->real_name ?? '',
            'updated_by' => $this->updater->real_name ?? '',
        ];
    }
}
