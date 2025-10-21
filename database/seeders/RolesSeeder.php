<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 角色表种子数据
     */
    public function run()
    {
        $roles = [
            [
                'role_code' => 'SUPER_ADMIN',
                'role_name' => '超级管理员',
                'description' => '系统超级管理员，拥有所有权限',

                'created_by' => null,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'ADMIN',
                'role_name' => '系统管理员',
                'description' => '系统管理员，负责系统配置和用户管理',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'MANAGER',
                'role_name' => '业务经理',
                'description' => '业务经理，负责业务管理和团队协调',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'PATENT_AGENT',
                'role_name' => '专利代理师',
                'description' => '专利代理师，负责专利申请和审查业务',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'TRADEMARK_AGENT',
                'role_name' => '商标代理师',
                'description' => '商标代理师，负责商标注册和维权业务',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'COPYRIGHT_AGENT',
                'role_name' => '版权代理师',
                'description' => '版权代理师，负责版权登记和保护业务',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'CUSTOMER_SERVICE',
                'role_name' => '客服专员',
                'description' => '客服专员，负责客户服务和咨询',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'FINANCE',
                'role_name' => '财务专员',
                'description' => '财务专员，负责财务管理和费用结算',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'LEGAL',
                'role_name' => '法务专员',
                'description' => '法务专员，负责法律事务和维权',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'SALES',
                'role_name' => '销售专员',
                'description' => '销售专员，负责业务拓展和客户开发',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'OPERATOR',
                'role_name' => '操作员',
                'description' => '普通操作员，负责日常业务操作',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'role_code' => 'VIEWER',
                'role_name' => '查看者',
                'description' => '只读权限，仅能查看相关信息',

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('roles')->count() == 0) {
            DB::table('roles')->insert($roles);
        }
    }
}
