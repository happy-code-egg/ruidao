<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 权限表种子数据
     */
    public function run()
    {
        $permissions = [
            // 1. 系统管理
            [
                'permission_code' => 'system',
                'permission_name' => '系统管理',
                'parent_id' => 0,
                'permission_type' => 1, // 菜单
                'resource_url' => '/system',
                // 'icon' => 'system',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'system.user',
                'permission_name' => '用户管理',
                'parent_id' => 1,
                'permission_type' => 1,
                'resource_url' => '/system/user',
                // 'icon' => 'user',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'system.role',
                'permission_name' => '角色管理',
                'parent_id' => 1,
                'permission_type' => 1,
                'resource_url' => '/system/role',
                // 'icon' => 'role',
                'sort_order' => 2,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'system.permission',
                'permission_name' => '权限管理',
                'parent_id' => 1,
                'permission_type' => 1,
                'resource_url' => '/system/permission',
                // 'icon' => 'permission',
                'sort_order' => 3,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'system.department',
                'permission_name' => '部门管理',
                'parent_id' => 1,
                'permission_type' => 1,
                'resource_url' => '/system/department',
                // 'icon' => 'department',
                'sort_order' => 4,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // 2. 客户管理
            [
                'permission_code' => 'customer',
                'permission_name' => '客户管理',
                'parent_id' => 0,
                'permission_type' => 1,
                'resource_url' => '/customer',
                // 'icon' => 'customer',
                'sort_order' => 2,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'customer.list',
                'permission_name' => '客户列表',
                'parent_id' => 6,
                'permission_type' => 1,
                'resource_url' => '/customer/list',
                // 'icon' => 'list',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'customer.add',
                'permission_name' => '新增客户',
                'parent_id' => 6,
                'permission_type' => 2, // 按钮
                'resource_url' => '/customer/add',
                // 'icon' => 'add',
                'sort_order' => 2,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'customer.edit',
                'permission_name' => '编辑客户',
                'parent_id' => 6,
                'permission_type' => 2,
                'resource_url' => '/customer/edit',
                // 'icon' => 'edit',
                'sort_order' => 3,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'customer.delete',
                'permission_name' => '删除客户',
                'parent_id' => 6,
                'permission_type' => 2,
                'resource_url' => '/customer/delete',
                // 'icon' => 'delete',
                'sort_order' => 4,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // 3. 案件管理
            [
                'permission_code' => 'case',
                'permission_name' => '案件管理',
                'parent_id' => 0,
                'permission_type' => 1,
                'resource_url' => '/case',
                // 'icon' => 'case',
                'sort_order' => 3,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'case.list',
                'permission_name' => '案件列表',
                'parent_id' => 11,
                'permission_type' => 1,
                'resource_url' => '/case/list',
                // 'icon' => 'list',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'case.add',
                'permission_name' => '新增案件',
                'parent_id' => 11,
                'permission_type' => 2,
                'resource_url' => '/case/add',
                // 'icon' => 'add',
                'sort_order' => 2,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // 4. 代理机构管理
            [
                'permission_code' => 'agency',
                'permission_name' => '代理机构管理',
                'parent_id' => 0,
                'permission_type' => 1,
                'resource_url' => '/agency',
                // 'icon' => 'agency',
                'sort_order' => 4,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'agency.list',
                'permission_name' => '代理机构列表',
                'parent_id' => 14,
                'permission_type' => 1,
                'resource_url' => '/agency/list',
                // 'icon' => 'list',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // 5. 数据配置
            [
                'permission_code' => 'config',
                'permission_name' => '数据配置',
                'parent_id' => 0,
                'permission_type' => 1,
                'resource_url' => '/config',
                // 'icon' => 'config',
                'sort_order' => 5,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'config.apply_type',
                'permission_name' => '申请类型配置',
                'parent_id' => 16,
                'permission_type' => 1,
                'resource_url' => '/config/apply-type',
                // 'icon' => 'config',
                'sort_order' => 1,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'config.process_status',
                'permission_name' => '处理状态配置',
                'parent_id' => 16,
                'permission_type' => 1,
                'resource_url' => '/config/process-status',
                // 'icon' => 'config',
                'sort_order' => 2,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'permission_code' => 'config.fee',
                'permission_name' => '费用配置',
                'parent_id' => 16,
                'permission_type' => 1,
                'resource_url' => '/config/fee',
                // 'icon' => 'config',
                'sort_order' => 3,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // 6. 报表统计
            [
                'permission_code' => 'report',
                'permission_name' => '报表统计',
                'parent_id' => 0,
                'permission_type' => 1,
                'resource_url' => '/report',
                // 'icon' => 'report',
                'sort_order' => 6,

                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('permissions')->count() == 0) {
            DB::table('permissions')->insert($permissions);
        }
    }
}
