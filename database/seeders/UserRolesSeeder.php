<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 用户角色关联表种子数据
     */
    public function run()
    {
        $userRoles = [
            // 用户ID 1 (admin) - 超级管理员
            [
                'user_id' => 1,
                'role_id' => 1, // 超级管理员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 2 (manager) - 业务经理
            [
                'user_id' => 2,
                'role_id' => 3, // 业务经理
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 3 (agent001) - 专利代理师
            [
                'user_id' => 3,
                'role_id' => 4, // 专利代理师
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 4 (agent002) - 商标代理师
            [
                'user_id' => 4,
                'role_id' => 5, // 商标代理师
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 5 (customer_service) - 客服专员
            [
                'user_id' => 5,
                'role_id' => 7, // 客服专员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 6 (finance) - 财务专员
            [
                'user_id' => 6,
                'role_id' => 8, // 财务专员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 用户ID 7 (test_user) - 查看者
            [
                'user_id' => 7,
                'role_id' => 12, // 查看者
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 给业务经理额外的销售权限
            [
                'user_id' => 2,
                'role_id' => 10, // 销售专员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 给专利代理师额外的操作员权限
            [
                'user_id' => 3,
                'role_id' => 11, // 操作员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // 给商标代理师额外的操作员权限
            [
                'user_id' => 4,
                'role_id' => 11, // 操作员
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('user_roles')->count() == 0) {
            DB::table('user_roles')->insert($userRoles);
        }
    }
}
