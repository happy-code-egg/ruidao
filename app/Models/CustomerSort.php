<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 客户排序模型
 * 用于管理用户对客户列表的自定义排序设置
 */
class CustomerSort extends Model
{

    // 指定数据库表名
    protected $table = 'customer_sorts';

    // 定义可批量赋值的字段
    protected $fillable = [
        'user_id',      // 用户ID
        'customer_id',  // 客户ID
        'list_type',    // 列表类型
        'sort_order'    // 排序顺序
    ];

    // 定义字段类型转换
    protected $casts = [
        'user_id' => 'integer',      // 用户ID转为整数类型
        'customer_id' => 'integer',  // 客户ID转为整数类型
        'sort_order' => 'integer'    // 排序顺序转为整数类型
    ];

    /**
     * 关联用户信息
     * 通过 `user_id` 字段关联 `User` 模型
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联客户信息
     * 通过 `customer_id` 字段关联 `Customer` 模型
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 获取用户的排序设置
     * 根据用户ID和列表类型查询排序记录，并按排序顺序排列
     * @param int $userId 用户ID
     * @param string $listType 列表类型
     * @return \Illuminate\Database\Eloquent\Collection 排序记录集合
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
     * 先删除用户原有的排序记录，然后批量插入新的排序数据
     * @param int $userId 用户ID
     * @param string $listType 列表类型
     * @param array $sortData 排序数据数组
     * @return bool 操作是否成功
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
                'created_at' => now(),  // 设置创建时间
                'updated_at' => now()   // 设置更新时间
            ];
        }

        if (!empty($insertData)) {
            static::insert($insertData);
        }

        return true;
    }

    /**
     * 重置用户排序
     * 删除指定用户和列表类型的排序记录
     * @param int $userId 用户ID
     * @param string $listType 列表类型
     * @return int 删除的记录数
     */
    public static function resetUserSort($userId, $listType)
    {
        return static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->delete();
    }

    /**
     * 处理相同序号的自动后移
     * 当插入新的排序记录时，将相同或更大序号的记录向后移动
     * @param int $userId 用户ID
     * @param string $listType 列表类型
     * @param int $newOrder 新的排序序号
     * @param int|null $excludeCustomerId 需要排除的客户ID（通常是正在更新的客户）
     * @return int 受影响的记录数量
     */
    public static function handleDuplicateOrder($userId, $listType, $newOrder, $excludeCustomerId = null)
    {
        // 查询需要后移的记录（序号大于等于新序号的记录）
        $query = static::where('user_id', $userId)
            ->where('list_type', $listType)
            ->where('sort_order', '>=', $newOrder);

        // 如果指定了排除的客户ID，则排除该客户
        if ($excludeCustomerId) {
            $query->where('customer_id', '!=', $excludeCustomerId);
        }

        $affectedRecords = $query->get();

        // 将受影响记录的序号都加1
        foreach ($affectedRecords as $record) {
            $record->sort_order = $record->sort_order + 1;
            $record->save();
        }

        return $affectedRecords->count();
    }

    /**
     * 设置单个客户的排序
     * 处理重复序号后，更新或创建指定客户的排序记录
     * @param int $userId 用户ID
     * @param int $customerId 客户ID
     * @param string $listType 列表类型
     * @param int $sortOrder 排序序号
     * @return CustomerSort 排序记录模型实例
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
