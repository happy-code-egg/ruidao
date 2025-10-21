<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSort extends Model
{

    protected $table = 'customer_sorts';

    protected $fillable = [
        'user_id',
        'customer_id',
        'list_type',
        'sort_order'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'customer_id' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 获取用户的排序设置
     */
    public static function getUserSort($userId, $listType)
    {
        return static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 保存用户排序
     */
    public static function saveUserSort($userId, $listType, $sortData)
    {
        // 删除现有排序
        static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->delete();

        // 批量插入新排序
        $insertData = [];
        foreach ($sortData as $item) {
            $insertData[] = [
                'user_id' => $userId,
                'customer_id' => $item['customer_id'],
                'list_type' => $listType,
                'sort_order' => $item['sort_order'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($insertData)) {
            static::insert($insertData);
        }

        return true;
    }

    /**
     * 重置用户排序
     */
    public static function resetUserSort($userId, $listType)
    {
        return static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->delete();
    }

    /**
     * 处理相同序号的自动后移
     */
    public static function handleDuplicateOrder($userId, $listType, $newOrder, $excludeCustomerId = null)
    {
        $query = static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->where('sort_order', '>=', $newOrder);

        if ($excludeCustomerId) {
            $query->where('customer_id', '!=', $excludeCustomerId);
        }

        $affectedRecords = $query->get();

        foreach ($affectedRecords as $record) {
            $record->sort_order = $record->sort_order + 1;
            $record->save();
        }

        return $affectedRecords->count();
    }

    /**
     * 设置单个客户的排序
     */
    public static function setCustomerSort($userId, $customerId, $listType, $sortOrder)
    {
        // 处理相同序号的自动后移
        static::handleDuplicateOrder($userId, $listType, $sortOrder, $customerId);

        // 更新或创建排序记录
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'customer_id' => $customerId,
                'list_type' => $listType
            ],
            [
                'sort_order' => $sortOrder
            ]
        );
    }
}
