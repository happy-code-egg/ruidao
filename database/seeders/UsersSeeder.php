<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 用户表种子数据
     */
    public function run()
    {
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('123456'),
                'real_name' => '系统管理员',
                'email' => 'admin@ema.com',
                'phone' => '13800138000',
                'avatar_url' => null,
                'department_id' => 1, // 管理部门
                'position' => '系统管理员',
                'employee_no' => 'EMA001',
                'status' => 1,
                'last_login_time' => Carbon::now(),
                'last_login_ip' => '127.0.0.1',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'manager',
                'password' => Hash::make('123456'),
                'real_name' => '业务经理',
                'email' => 'manager@ema.com',
                'phone' => '13800138001',
                'avatar_url' => null,
                'department_id' => 2, // 业务部门
                'position' => '业务经理',
                'employee_no' => 'EMA002',
                'status' => 1,
                'last_login_time' => Carbon::now()->subDays(1),
                'last_login_ip' => '192.168.1.100',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'agent001',
                'password' => Hash::make('123456'),
                'real_name' => '专利代理师',
                'email' => 'agent001@ema.com',
                'phone' => '13800138002',
                'avatar_url' => null,
                'department_id' => 3, // 专利部门
                'position' => '专利代理师',
                'employee_no' => 'EMA003',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(2),
                'last_login_ip' => '192.168.1.101',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'agent002',
                'password' => Hash::make('123456'),
                'real_name' => '商标代理师',
                'email' => 'agent002@ema.com',
                'phone' => '13800138003',
                'avatar_url' => null,
                'department_id' => 4, // 商标部门
                'position' => '商标代理师',
                'employee_no' => 'EMA004',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(1),
                'last_login_ip' => '192.168.1.102',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'customer_service',
                'password' => Hash::make('123456'),
                'real_name' => '客服专员',
                'email' => 'service@ema.com',
                'phone' => '13800138004',
                'avatar_url' => null,
                'department_id' => 5, // 客服部门
                'position' => '客服专员',
                'employee_no' => 'EMA005',
                'status' => 1,
                'last_login_time' => Carbon::now()->subMinutes(30),
                'last_login_ip' => '192.168.1.103',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'finance',
                'password' => Hash::make('123456'),
                'real_name' => '财务专员',
                'email' => 'finance@ema.com',
                'phone' => '13800138005',
                'avatar_url' => null,
                'department_id' => 6, // 财务部门
                'position' => '财务专员',
                'employee_no' => 'EMA006',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(3),
                'last_login_ip' => '192.168.1.104',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'legal_manager',
                'password' => Hash::make('123456'),
                'real_name' => '法务经理',
                'email' => 'legal@ema.com',
                'phone' => '13800138007',
                'avatar_url' => null,
                'department_id' => 8, // 法务部门
                'position' => '法务经理',
                'employee_no' => 'EMA007',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(4),
                'last_login_ip' => '192.168.1.105',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'legal_specialist',
                'password' => Hash::make('123456'),
                'real_name' => '法务专员',
                'email' => 'legal2@ema.com',
                'phone' => '13800138008',
                'avatar_url' => null,
                'department_id' => 8, // 法务部门
                'position' => '法务专员',
                'employee_no' => 'EMA008',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(2),
                'last_login_ip' => '192.168.1.106',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'finance_manager',
                'password' => Hash::make('123456'),
                'real_name' => '财务经理',
                'email' => 'finance_mgr@ema.com',
                'phone' => '13800138009',
                'avatar_url' => null,
                'department_id' => 6, // 财务部门
                'position' => '财务经理',
                'employee_no' => 'EMA009',
                'status' => 1,
                'last_login_time' => Carbon::now()->subHours(1),
                'last_login_ip' => '192.168.1.107',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'archive_specialist',
                'password' => Hash::make('123456'),
                'real_name' => '档案专员',
                'email' => 'archive@ema.com',
                'phone' => '13800138010',
                'avatar_url' => null,
                'department_id' => 1, // 管理部门
                'position' => '档案专员',
                'employee_no' => 'EMA010',
                'status' => 1,
                'last_login_time' => Carbon::now()->subMinutes(45),
                'last_login_ip' => '192.168.1.108',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'username' => 'business_specialist',
                'password' => Hash::make('123456'),
                'real_name' => '业务专员',
                'email' => 'business@ema.com',
                'phone' => '13800138011',
                'avatar_url' => null,
                'department_id' => 2, // 业务部门
                'position' => '业务专员',
                'employee_no' => 'EMA011',
                'status' => 1,
                'last_login_time' => Carbon::now()->subMinutes(15),
                'last_login_ip' => '192.168.1.109',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // 检查是否已有数据，避免重复插入
        if (DB::table('users')->count() == 0) {
            DB::table('users')->insert($users);
        }
    }
}
