<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 部门表种子数据
     */
    public function run()
    {
        $departments = [
            [
                'department_code' => 'ADMIN',
                'department_name' => '管理部门',
                'parent_id' => 0,
                'level_path' => '/1',
                'manager_id' => 1, // 系统管理员
                'description' => '负责公司整体管理和系统维护',
                'sort_order' => 1,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'BUSINESS',
                'department_name' => '业务部门',
                'parent_id' => 0,
                'level_path' => '/2',
                'manager_id' => 2, // 业务经理
                'description' => '负责业务拓展和客户关系维护',
                'sort_order' => 2,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'PATENT',
                'department_name' => '专利部门',
                'parent_id' => 2, // 隶属于业务部门
                'level_path' => '/2/3',
                'manager_id' => 3, // 专利代理师
                'description' => '负责专利申请、审查和维护相关业务',
                'sort_order' => 1,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'TRADEMARK',
                'department_name' => '商标部门',
                'parent_id' => 2, // 隶属于业务部门
                'level_path' => '/2/4',
                'manager_id' => 4, // 商标代理师
                'description' => '负责商标注册、维权和管理相关业务',
                'sort_order' => 2,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'SERVICE',
                'department_name' => '客服部门',
                'parent_id' => 0,
                'level_path' => '/5',
                'manager_id' => 5, // 客服专员
                'description' => '负责客户服务和售后支持',
                'sort_order' => 3,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'FINANCE',
                'department_name' => '财务部门',
                'parent_id' => 0,
                'level_path' => '/6',
                'manager_id' => 6, // 财务专员
                'description' => '负责财务管理、费用结算和发票开具',
                'sort_order' => 4,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'COPYRIGHT',
                'department_name' => '版权部门',
                'parent_id' => 2, // 隶属于业务部门
                'level_path' => '/2/7',
                'manager_id' => null,
                'description' => '负责版权登记和保护相关业务',
                'sort_order' => 3,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'LEGAL',
                'department_name' => '法务部门',
                'parent_id' => 0,
                'level_path' => '/8',
                'manager_id' => null,
                'description' => '负责法律事务和知识产权维权',
                'sort_order' => 5,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'TECH',
                'department_name' => '技术部门',
                'parent_id' => 0,
                'level_path' => '/9',
                'manager_id' => null,
                'description' => '负责系统开发和技术支持',
                'sort_order' => 6,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_code' => 'HR',
                'department_name' => '人事部门',
                'parent_id' => 1, // 隶属于管理部门
                'level_path' => '/1/10',
                'manager_id' => null,
                'description' => '负责人力资源管理和员工事务',
                'sort_order' => 1,

                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('departments')->count() == 0) {
            DB::table('departments')->insert($departments);
        }
    }
}
