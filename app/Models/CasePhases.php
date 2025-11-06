<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasePhases extends Model
{
    // 指定对应的数据库表名
    protected $table = 'case_phases';

    // 允许批量赋值的字段列表
    protected $fillable = [
        'name',           // 阶段名称
        'code',           // 阶段编码
        'description',    // 描述信息
        'status',         // 状态(1:启用, 0:禁用)
        'sort_order',     // 排序顺序
        'country',        // 国家
        'case_type',      // 案件类型
        'phase_name',     // 阶段名称
        'phase_name_en',  // 阶段英文名称
        'created_by',     // 创建者ID
        'updated_by'      // 更新者ID
    ];

    // 字段类型转换定义
    protected $casts = [
        'status' => 'integer',      // 状态 - 整数类型
        'sort_order' => 'integer',  // 排序顺序 - 整数类型
        'created_by' => 'integer',  // 创建者ID - 整数类型
        'updated_by' => 'integer'   // 更新者ID - 整数类型
    ];

    // 关联创建人
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 关联更新人
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 状态范围查询 - 只获取启用状态的数据
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // 排序范围查询 - 按排序字段和ID升序排列
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
