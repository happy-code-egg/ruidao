<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmaSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. 创建部门数据
        $departments = [
            [
                'id' => 1,
                'department_code' => 'ADMIN',
                'department_name' => '管理部',
                'parent_id' => 0,
                'level_path' => '1',
                'manager_id' => null,
                'description' => '系统管理部门',
                'sort_order' => 1,
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'department_code' => 'BUSINESS',
                'department_name' => '业务部',
                'parent_id' => 0,
                'level_path' => '2',
                'manager_id' => null,
                'description' => '业务管理部门',
                'sort_order' => 2,
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'department_code' => 'PATENT',
                'department_name' => '专利部',
                'parent_id' => 2,
                'level_path' => '2,3',
                'manager_id' => null,
                'description' => '专利业务部门',
                'sort_order' => 1,
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'department_code' => 'TRADEMARK',
                'department_name' => '商标部',
                'parent_id' => 2,
                'level_path' => '2,4',
                'manager_id' => null,
                'description' => '商标业务部门',
                'sort_order' => 2,
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'department_code' => 'FINANCE',
                'department_name' => '财务部',
                'parent_id' => 0,
                'level_path' => '5',
                'manager_id' => null,
                'description' => '财务管理部门',
                'sort_order' => 3,
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('departments')->insert($departments);

        // 2. 创建权限数据
        $permissions = [
            // 系统管理
            ['id' => 1, 'permission_code' => 'system', 'permission_name' => '系统管理', 'parent_id' => 0, 'permission_type' => 1, 'resource_url' => '/config', 'icon' => 'el-icon-setting', 'sort_order' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'permission_code' => 'system.user', 'permission_name' => '用户管理', 'parent_id' => 1, 'permission_type' => 1, 'resource_url' => '/config/system/user', 'icon' => 'el-icon-user', 'sort_order' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'permission_code' => 'system.role', 'permission_name' => '角色管理', 'parent_id' => 1, 'permission_type' => 1, 'resource_url' => '/config/system/role', 'icon' => 'el-icon-s-custom', 'sort_order' => 2, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'permission_code' => 'system.permission', 'permission_name' => '权限管理', 'parent_id' => 1, 'permission_type' => 1, 'resource_url' => '/config/system/permission', 'icon' => 'el-icon-lock', 'sort_order' => 3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'permission_code' => 'system.department', 'permission_name' => '部门管理', 'parent_id' => 1, 'permission_type' => 1, 'resource_url' => '/config/system/department', 'icon' => 'el-icon-office-building', 'sort_order' => 4, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            
            // 客户管理
            ['id' => 6, 'permission_code' => 'customer', 'permission_name' => '客户管理', 'parent_id' => 0, 'permission_type' => 1, 'resource_url' => '/customer', 'icon' => 'el-icon-user-solid', 'sort_order' => 2, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'permission_code' => 'customer.list', 'permission_name' => '客户列表', 'parent_id' => 6, 'permission_type' => 1, 'resource_url' => '/customer/list', 'icon' => '', 'sort_order' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'permission_code' => 'customer.add', 'permission_name' => '新增客户', 'parent_id' => 6, 'permission_type' => 2, 'resource_url' => '', 'icon' => '', 'sort_order' => 2, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'permission_code' => 'customer.edit', 'permission_name' => '编辑客户', 'parent_id' => 6, 'permission_type' => 2, 'resource_url' => '', 'icon' => '', 'sort_order' => 3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'permission_code' => 'customer.delete', 'permission_name' => '删除客户', 'parent_id' => 6, 'permission_type' => 2, 'resource_url' => '', 'icon' => '', 'sort_order' => 4, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            
            // 合同管理
            ['id' => 11, 'permission_code' => 'contract', 'permission_name' => '合同管理', 'parent_id' => 0, 'permission_type' => 1, 'resource_url' => '/contract', 'icon' => 'el-icon-document', 'sort_order' => 3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'permission_code' => 'contract.list', 'permission_name' => '合同列表', 'parent_id' => 11, 'permission_type' => 1, 'resource_url' => '/contract/list', 'icon' => '', 'sort_order' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            
            // 项目管理
            ['id' => 13, 'permission_code' => 'case', 'permission_name' => '项目管理', 'parent_id' => 0, 'permission_type' => 1, 'resource_url' => '/case', 'icon' => 'el-icon-folder', 'sort_order' => 4, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'permission_code' => 'case.list', 'permission_name' => '项目列表', 'parent_id' => 13, 'permission_type' => 1, 'resource_url' => '/case/search', 'icon' => '', 'sort_order' => 1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('permissions')->insert($permissions);

        // 3. 创建角色数据
        $roles = [
            [
                'id' => 1,
                'role_code' => 'SUPER_ADMIN',
                'role_name' => '超级管理员',
                'description' => '系统超级管理员，拥有所有权限',
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'role_code' => 'ADMIN',
                'role_name' => '管理员',
                'description' => '系统管理员',
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'role_code' => 'BUSINESS_MANAGER',
                'role_name' => '业务经理',
                'description' => '业务部门经理',
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'role_code' => 'BUSINESS_STAFF',
                'role_name' => '业务员',
                'description' => '普通业务员',
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('roles')->insert($roles);

        // 4. 创建用户数据
        $users = [
            [
                'id' => 1,
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'real_name' => '系统管理员',
                'email' => 'admin@ema.com',
                'phone' => '13800138000',
                'department_id' => 1,
                'position' => '系统管理员',
                'employee_no' => 'EMA001',
                'status' => 1,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'username' => 'manager',
                'password' => Hash::make('123456'),
                'real_name' => '业务经理',
                'email' => 'manager@ema.com',
                'phone' => '13800138001',
                'department_id' => 2,
                'position' => '业务经理',
                'employee_no' => 'EMA002',
                'status' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'username' => 'staff',
                'password' => Hash::make('123456'),
                'real_name' => '业务员',
                'email' => 'staff@ema.com',
                'phone' => '13800138002',
                'department_id' => 3,
                'position' => '业务员',
                'employee_no' => 'EMA003',
                'status' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('users')->insert($users);

        // 5. 分配用户角色
        $userRoles = [
            ['user_id' => 1, 'role_id' => 1, 'created_at' => $now], // admin -> 超级管理员
            ['user_id' => 2, 'role_id' => 3, 'created_at' => $now], // manager -> 业务经理
            ['user_id' => 3, 'role_id' => 4, 'created_at' => $now], // staff -> 业务员
        ];

        DB::table('user_roles')->insert($userRoles);

        // 6. 分配角色权限
        $rolePermissions = [
            // 超级管理员拥有所有权限
            ['role_id' => 1, 'permission_id' => 1, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 2, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 3, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 4, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 5, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 6, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 7, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 8, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 9, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 10, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 11, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 12, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 13, 'created_at' => $now],
            ['role_id' => 1, 'permission_id' => 14, 'created_at' => $now],
            
            // 业务经理权限
            ['role_id' => 3, 'permission_id' => 6, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 7, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 8, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 9, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 11, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 12, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 13, 'created_at' => $now],
            ['role_id' => 3, 'permission_id' => 14, 'created_at' => $now],
            
            // 业务员权限
            ['role_id' => 4, 'permission_id' => 6, 'created_at' => $now],
            ['role_id' => 4, 'permission_id' => 7, 'created_at' => $now],
            ['role_id' => 4, 'permission_id' => 13, 'created_at' => $now],
            ['role_id' => 4, 'permission_id' => 14, 'created_at' => $now],
        ];

        DB::table('role_permissions')->insert($rolePermissions);

        // 更新部门负责人
        DB::table('departments')->where('id', 1)->update(['manager_id' => 1]);
        DB::table('departments')->where('id', 2)->update(['manager_id' => 2]);
        DB::table('departments')->where('id', 3)->update(['manager_id' => 2]);
        DB::table('departments')->where('id', 4)->update(['manager_id' => 2]);
        DB::table('departments')->where('id', 5)->update(['manager_id' => 1]);

        $this->command->info('EMA系统基础数据创建完成！');
        $this->command->info('用户账号：');
        $this->command->info('管理员 - 用户名: admin, 密码: 123456');
        $this->command->info('业务经理 - 用户名: manager, 密码: 123456');
        $this->command->info('业务员 - 用户名: staff, 密码: 123456');
    }
}
