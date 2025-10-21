<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRelatedPerson extends Model
{
    use SoftDeletes;

    protected $table = 'customer_related_persons';

    protected $fillable = [
        'customer_id',
        'related_business_person_id',
        'person_name',
        'person_type',
        'phone',
        'email',
        'position',
        'department',
        'relationship',
        'responsibility',
        'is_active',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'related_business_person_id' => 'integer',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 人员类型常量
     */
    const TYPE_TECH_LEADER = '技术负责人';
    const TYPE_BUSINESS_LEADER = '商务负责人';
    const TYPE_FINANCE_LEADER = '财务负责人';
    const TYPE_PROJECT_MANAGER = '项目负责人';
    const TYPE_BUSINESS_ASSISTANT = '业务助理';
    const TYPE_BUSINESS_COLLABORATOR = '业务协作人';
    const TYPE_OTHER = '其他';

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
     * 获取关联的业务人员
     */
    public function relatedBusinessPerson()
    {
        return $this->belongsTo(User::class, 'related_business_person_id');
    }

    /**
     * 获取在职状态文本
     */
    public function getActiveStatusTextAttribute()
    {
        return $this->is_active ? '在职' : '离职';
    }

    /**
     * 获取人员类型选项
     */
    public static function getPersonTypes()
    {
        return [
            self::TYPE_TECH_LEADER => '技术负责人',
            self::TYPE_BUSINESS_LEADER => '商务负责人',
            self::TYPE_FINANCE_LEADER => '财务负责人',
            self::TYPE_PROJECT_MANAGER => '项目负责人',
            self::TYPE_BUSINESS_ASSISTANT => '业务助理',
            self::TYPE_BUSINESS_COLLABORATOR => '业务协作人',
            self::TYPE_OTHER => '其他',
        ];
    }
}
