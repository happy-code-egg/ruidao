<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 角色权限关联表种子数据
     */
    public function run()
    {
        $rolePermissions = [
            // 超级管理员 (role_id: 1) - 拥有所有权限
            ['role_id' => 1, 'permission_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 16, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 17, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 19, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 1, 'permission_id' => 20, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 系统管理员 (role_id: 2) - 系统管理权限
            ['role_id' => 2, 'permission_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 16, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 17, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 19, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 2, 'permission_id' => 20, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 业务经理 (role_id: 3) - 客户和案件管理权限
            ['role_id' => 3, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 3, 'permission_id' => 20, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 专利代理师 (role_id: 4) - 案件管理权限
            ['role_id' => 4, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 4, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 4, 'permission_id' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 4, 'permission_id' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 4, 'permission_id' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 商标代理师 (role_id: 5) - 案件管理权限
            ['role_id' => 5, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 5, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 5, 'permission_id' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 5, 'permission_id' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 5, 'permission_id' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 客服专员 (role_id: 7) - 客户管理权限
            ['role_id' => 7, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 7, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 7, 'permission_id' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 7, 'permission_id' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 财务专员 (role_id: 8) - 查看权限
            ['role_id' => 8, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 8, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 8, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 8, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 8, 'permission_id' => 20, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 销售专员 (role_id: 10) - 客户管理权限
            ['role_id' => 10, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 10, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 10, 'permission_id' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 10, 'permission_id' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 10, 'permission_id' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 操作员 (role_id: 11) - 基本操作权限
            ['role_id' => 11, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 11, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 11, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 11, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // 查看者 (role_id: 12) - 只读权限
            ['role_id' => 12, 'permission_id' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 12, 'permission_id' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 12, 'permission_id' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['role_id' => 12, 'permission_id' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('role_permissions')->count() == 0) {
            DB::table('role_permissions')->insert($rolePermissions);
        }
    }
}
