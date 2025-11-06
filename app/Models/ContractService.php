<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合同服务项目模型
 * 用于管理合同中包含的服务项目信息
 */
class ContractService extends Model
{
    // 使用软删除功能
    use SoftDeletes;

    /**
     * 指定与模型关联的数据表
     * @var string
     */
    protected $table = 'contract_services';

    /**
     * 允许批量赋值的字段列表
     * @var array
     */
    protected $fillable = [
        'contract_id',          // 合同ID
        'service_name',         // 服务名称
        'service_description',  // 服务描述
        'amount',               // 金额
        'official_fee',         // 官方费用
        'remark',               // 备注
        'sort_order',           // 排序字段
    ];

    /**
     * 字段类型转换定义
     * @var array
     */
    protected $casts = [
        'contract_id' => 'integer',     // 合同ID转换为整数
        'amount' => 'decimal:2',        // 金额转换为保留2位小数的浮点数
        'official_fee' => 'decimal:2',  // 官方费用转换为保留2位小数的浮点数
        'sort_order' => 'integer',      // 排序字段转换为整数
        'created_at' => 'datetime',     // 创建时间转换为日期时间
        'updated_at' => 'datetime',     // 更新时间转换为日期时间
        'deleted_at' => 'datetime',     // 删除时间转换为日期时间
    ];

    /**
     * 获取所属合同关联关系
     * 建立与 `Contract` 模型的一对多反向关联
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * 获取总金额的访问器
     * 计算金额与官方费用的总和
     * @return float 总金额
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->official_fee;
    }
}
