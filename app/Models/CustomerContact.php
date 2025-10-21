<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use SoftDeletes;

    protected $table = 'customer_contacts';

    protected $fillable = [
        'customer_id',
        'contact_name',
        'contact_type',
        'contact_type_text',
        'gender',
        'position',
        'phone',
        'telephone',
        'email',
        'work_email',
        'wechat',
        'qq',
        'address',
        'work_address',
        'id_card',
        'is_primary',
        'is_on_job',
        'department',
        'title',
        'business_staff_id',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'contact_type' => 'integer',
        'is_primary' => 'integer',
        'is_on_job' => 'boolean',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 联系人类型常量
     */
    const TYPE_HANDLER = 1;
    const TYPE_TECHNICAL = 2;
    const TYPE_FINANCIAL = 3;
    const TYPE_IPR = 4;
    const TYPE_INVENTOR = 5;

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
     * 获取业务人员
     */
    public function businessStaff()
    {
        return $this->belongsTo(User::class, 'business_staff_id');
    }

    /**
     * 联系人类型文本
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_HANDLER => '经办人',
            self::TYPE_TECHNICAL => '技术人员',
            self::TYPE_FINANCIAL => '财务人员',
            self::TYPE_IPR => 'IPR',
            self::TYPE_INVENTOR => '发明人',
        ];

        return $types[$this->contact_type] ?? '未知';
    }

    /**
     * 状态文本
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? '启用' : '禁用';
    }

    /**
     * 是否主要联系人文本
     */
    public function getPrimaryTextAttribute()
    {
        return $this->is_primary == 1 ? '是' : '否';
    }

    /**
     * 获取主要联系方式
     */
    public function getPrimaryContactAttribute()
    {
        if ($this->phone) {
            return $this->phone;
        }
        
        if ($this->telephone) {
            return $this->telephone;
        }
        
        if ($this->email) {
            return $this->email;
        }
        
        return '无';
    }
}
